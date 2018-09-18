<?php

require __DIR__ . '/../vendor/autoload.php';

$io = new \Illuminate\Console\OutputStyle(
    new \Symfony\Component\Console\Input\ArgvInput(),
    new \Symfony\Component\Console\Output\ConsoleOutput()
);

$configFile = realpath(getenv('LARACRON_CONFIG_YAML'));

if (!$configFile || !file_exists($configFile)) {
    $io->error("Please provide config file path as environment variable LARACRON_CONFIG_YAML");
    exit(1);
}

if (!is_readable($configFile)) {
    $io->error("Provided file '{$configFile}' is not readable.");
    exit(1);
}

$config = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($configFile));
if (!is_array($config)) {
    $io->error("Provided configuration file '{$configFile}' must be parsed to an array.");
    exit(1);
}

$cronApp = new \Trig\LaraCron\CronApplication($config);

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        global $cronApp;
        return $cronApp->basePath() . ($path ? '/' . $path : $path);
    }
}

$cronApp->booting(function () use ($config) {
    if ('redis' === ($config['cache.default'] ?? null)) {
        if (!class_exists('Redis')) {
            throw new \RuntimeException('Please install redis extension, to use with provided configuration');
        }
    }
});

$cronApp->booted(function (\Trig\LaraCron\CronApplication $app) {
    $app->get(\Illuminate\Console\Scheduling\Schedule::class)->exec('app/console list')->everyMinute()
        ->name('test')
        ->onOneServer();
});

try {
    $cronApp->boot();
    $cronApp->get(\Illuminate\Console\Scheduling\ScheduleRunCommand::class)->handle();
} catch (\Throwable $e) {
    $io->error($e->getMessage());
    $io->table(['Stack trace'], array_map(function ($row) {
        return [$row];
    }, explode("\n", $e->getTraceAsString())));
}
