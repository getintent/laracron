<?php

require __DIR__.'/../vendor/autoload.php';

use Trig\LaraCron\ExitCodes;

$io = new \Illuminate\Console\OutputStyle(
    new \Symfony\Component\Console\Input\ArgvInput(),
    new \Symfony\Component\Console\Output\ConsoleOutput()
);

$configFile = realpath(getcwd()).'/laracron.json';

if (!$configFile || !file_exists($configFile)) {
    $io->error("Please initialize configuration with <comment>init</comment> command.");
    exit(ExitCodes::ERROR_CONFIG_NOT_FOUND);
}

if (!is_readable($configFile)) {
    $io->error("Provided file <comment>{$configFile}</comment> is not readable.");
    exit(ExitCodes::ERROR_CONFIG_NOT_READABLE);
}

$config = json_decode(file_get_contents($configFile), true);
if (JSON_ERROR_NONE !== json_last_error()) {
    $io->error("JSON parse error in <comment>{$configFile}</comment>");
    exit(ExitCodes::ERROR_CONFIG_JSON_ERROR);
}

$cronApp = new \Trig\LaraCron\CronApplication($config);

$cronApp->booting(
    function (\Trig\LaraCron\CronApplication $app) use ($config, $io) {
        if ('redis' === ($config['cache.default'] ?? null)) {
            if (!class_exists('Redis')) {
                $io->error('Please install Redis extension, to use with provided configuration');
            }
        }
    }
);

$cronApp->booted(
    function (\Trig\LaraCron\CronApplication $app) use ($io, $config) {
        $scheduler = $app->get(\Illuminate\Console\Scheduling\Schedule::class);
        foreach ($config['scheduledJobs'] ?? [] as $definition => $commands) {
            foreach ($commands as $command) {
                $event = $scheduler->exec($command)->name(
                    implode(
                        '.',
                        [
                            'laracron',
                            crc32($definition.$command),
                            preg_replace('/-{2,}/', '-', preg_replace('/[^\w]/', '-', $definition.'/'.$command)),
                        ]
                    )
                )->onOneServer();

                $isCronDefinition = false !== strpos($definition, ' ');
                if ($isCronDefinition) {
                    $event->cron($definition);
                } elseif (method_exists($event, $definition)) {
                    $event->{$definition}();
                } else {
                    $io->writeln("<fg=red>ERROR:</> Seems that command schedule definition <comment>{$definition}</comment> is wrong for <comment>{$command}</comment> command");
                    exit(ExitCodes::ERROR_CMD_DEFINITION);
                }
            }
        }

        $console = $app->get(\Illuminate\Console\Application::class);
        $console->add(new \Trig\LaraCron\Command\InitCommand());
        $console->add(new \Trig\LaraCron\Command\BuildPharCommand());
    }
);

try {
    $cronApp->boot();
    $cronApp->get(\Illuminate\Console\Application::class)->run();
} catch (\Throwable $e) {
    $io->error($e->getMessage());
    $io->listing(explode("\n", $e->getTraceAsString()));
    exit(ExitCodes::ERROR_GENERAL);
}
