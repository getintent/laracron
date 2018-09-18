<?php

require __DIR__.'/../vendor/autoload.php';

$io = new \Illuminate\Console\OutputStyle(
    new \Symfony\Component\Console\Input\ArgvInput(),
    new \Symfony\Component\Console\Output\ConsoleOutput()
);

$configFile = realpath(getcwd()).'/laracron.json';

if (!$configFile || !file_exists($configFile)) {
    $io->error("Please initialize configuration with <comment>init</comment> command.");
    exit(1);
}

if (!is_readable($configFile)) {
    $io->error("Provided file <comment>{$configFile}</comment> is not readable.");
    exit(1);
}

$config = json_decode(file_get_contents($configFile), true);
if (!is_array($config)) {
    $io->error("Provided configuration file <comment>{$configFile}</comment> must be parsed to an array.");
    exit(1);
}

$cronApp = new \Trig\LaraCron\CronApplication($config);

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        global $cronApp;

        return $cronApp->basePath().($path ? '/'.$path : $path);
    }
}

$cronApp->booting(
    function (\Trig\LaraCron\CronApplication $app) use ($config) {
        if ('redis' === ($config['cache.default'] ?? null)) {
            if (!class_exists('Redis')) {
                throw new \RuntimeException('Please install redis extension, to use with provided configuration');
            }
        }
    }
);

$cronApp->booted(
    function (\Trig\LaraCron\CronApplication $app) use ($io) {
        $app->get(\Illuminate\Console\Scheduling\Schedule::class)->call(
            function () use ($io) {
                $io->comment('Hi there! '.date('H:i:s'));
            }
        )->everyMinute()
            ->name('test')
            ->onOneServer();

        $app->get(\Illuminate\Console\Application::class)->add(new \Trig\LaraCron\Command\InitCommand());
    }
);

try {
    $cronApp->boot();
    $cronApp->get(\Illuminate\Console\Application::class)->run();
} catch (\Throwable $e) {
    $io->error($e->getMessage());
    $io->listing(explode("\n", $e->getTraceAsString()));
}
