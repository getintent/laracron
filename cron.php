<?php

require 'vendor/autoload.php';


class MemoryCacheStore implements \Illuminate\Contracts\Cache\Factory
{

    private $store;

    /**
     * Get a cache store instance by name.
     *
     * @param  string|null $name
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public function store($name = null)
    {
        if ($this->store) {
            return $this->store;
        }

    }
}

$container = \Illuminate\Container\Container::getInstance();


$container->bind(\Illuminate\Contracts\Cache\Repository::class, function(\Illuminate\Container\Container $container, $parameters){
    $store = new \Illuminate\Cache\FileStore(new \Illuminate\Filesystem\Filesystem(), $container['rootDir'] . '/cache');
    new \Illuminate\Cache\TaggedCache($store, new \Illuminate\Cache\TagSet($store));
});

$dispatcher = new \Illuminate\Events\Dispatcher($container);

$app = new \Illuminate\Console\Application($container, $dispatcher, $container['version']);
$cronApp = new CronApp();

function base_path($path = '')
{
    global $cronApp;
    return $cronApp->basePath().($path ? '/'.$path : $path);
}

$container->bind(\Illuminate\Console\Scheduling\EventMutex::class, function () {
    return new \Illuminate\Console\Scheduling\CacheEventMutex(new MemoryCacheStore());
});

$container->bind(\Illuminate\Console\Scheduling\SchedulingMutex::class, function () {
    return new \Illuminate\Console\Scheduling\CacheSchedulingMutex(new MemoryCacheStore());
});

$schedule = new \Illuminate\Console\Scheduling\Schedule();

$scheduledRun = new \Illuminate\Console\Scheduling\ScheduleRunCommand($schedule);

$schedule->exec('app/console list')->everyMinute()
    ->name('test')
    ->onOneServer();

$schedule->call(function () {
    print date('H:i:s') . " I'm running every 5 mins!\n";
})->everyFiveMinutes()
    ->name('test1')
    ->onOneServer();

$app->add($scheduledRun);
$scheduledRun->setLaravel($cronApp);
$app->setDefaultCommand($scheduledRun->getName());

$app->run();
