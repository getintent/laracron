<?php

  require __DIR__.'/../vendor/autoload.php';

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Trig\LaraCron\Exception\ExitCommandException;
use Trig\LaraCron\ExitCodes;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;

$configFile = realpath('.') . '/laracron.json';

$io = new OutputStyle(
    new ArgvInput(),
    new ConsoleOutput()
);

try {
    $cronApp = new \Trig\LaraCron\CronApplication($configFile);

    function base_path($path = null)
    {
        $container = Container::getInstance();
        return $container->get('config')['basePath'].($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    $cronApp->booted(
        function (\Trig\LaraCron\CronApplication $app)use ($cronApp) {
            $console = $app->get(\Illuminate\Console\Application::class);
            $console->add(new \Trig\LaraCron\Command\InitCommand());
            $console->add(new \Trig\LaraCron\Command\BuildPharCommand());
            $scheduleRun = new \Trig\LaraCron\Command\ScheduleRunCommand($app->get(Schedule::class));
            $console->add($scheduleRun);
            $scheduleRun->setLaravel($cronApp);
        }
    );

    $cronApp->boot();
    $exitCode = $cronApp->get(\Illuminate\Console\Application::class)->run();
    exit($exitCode);

} catch (\Throwable $e) {
    $io->error(sprintf('[%s] %s', get_class($e), $e->getMessage()));
    $io->listing(explode("\n", $e->getTraceAsString()));

    if ($e instanceof ExitCommandException) {
        exit($e->getCode());
    }
    exit(ExitCodes::ERROR_GENERAL);
}
