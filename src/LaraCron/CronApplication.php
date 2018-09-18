<?php

namespace Trig\LaraCron;

use Illuminate\Cache\CacheManager;
use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\CacheSchedulingMutex;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Console\Scheduling\SchedulingMutex;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;

class CronApplication implements ApplicationContract
{
    const VERSION = '0.1.0';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array \Closure
     */
    private $bootingCallbacks = [];

    /**
     * @var array \Closure
     */
    private $bootedCallbacks = [];

    public function __construct(array $config)
    {
        $this->container = Container::getInstance();
        $this->container['config'] = $config;
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return self::VERSION;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function basePath()
    {
        $this->checkConfigPath('basePath');

        return $this->container['config']['basePath'];
    }

    /**
     * @param $path
     */
    private function checkConfigPath(string $path)
    {
        $compiledPath = sprintf('["%s"]', implode('"]["', explode('.', $path)));
        $result = eval(sprintf('return $this->container["config"]%s ?? null;', $compiledPath));
        if (null === $result) {
            throw new \RuntimeException("Configuration path '$path' is not exists");
        }
    }

    /**
     * Get or check the current application environment.
     *
     * @return string
     */
    public function environment()
    {
        $this->checkConfigPath('environment');

        return $this->container['config']['environment'];
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return true;
    }

    /**
     * Determine if the application is running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        return false;
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return false;
    }

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string $provider
     * @param  bool $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false)
    {
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string $provider
     * @param  string|null $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->bootingCallbacks as $callback) {
            $callback($this);
        }
        $this->bind(
            Application::class,
            function (Container $container) {
                return new Application($container, $container->make(Dispatcher::class), self::VERSION);
            },
            true
        );

        $this->bind(
            CacheManager::class,
            function (Container $container) {
                return new CacheManager($container);
            },
            true
        );

        $this->bind(
            EventMutex::class,
            function (Container $container) {
                return new CacheEventMutex($container->get(CacheManager::class));
            },
            true
        );

        $this->bind(
            SchedulingMutex::class,
            function (Container $container) {
                return new CacheSchedulingMutex($container->get(CacheManager::class));
            },
            true
        );

        $this->bind(Schedule::class, null, true);
        $this->bind(ScheduleRunCommand::class, null, true);

        $this->bind(Filesystem::class, null, true);
        $this->alias(Filesystem::class, 'files');


        $scheduledRun = $this->get(ScheduleRunCommand::class);
        $app = $this->get(Application::class);
        $app->add($scheduledRun);
        $scheduledRun->setLaravel($this);

        $this->registerScheduledCommands();
        foreach ($this->bootedCallbacks as $callback) {
            $callback($this);
        }
    }

    /**
     * Register a binding with the container.
     *
     * @param  string $abstract
     * @param  \Closure|string|null $concrete
     * @param  bool $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        $this->container->bind($abstract, $concrete, $shared);
    }

    /**
     * Alias a type to a different name.
     *
     * @param  string $abstract
     * @param  string $alias
     * @return void
     */
    public function alias($abstract, $alias)
    {
        $this->container->alias($abstract, $alias);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws \Psr\Container\NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws \Psr\Container\ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    private function registerScheduledCommands()
    {
        $this->checkConfigPath('scheduledJobs');

    }

    /**
     * Register a new boot listener.
     *
     * @param  mixed $callback
     * @return void
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param  mixed $callback
     * @return void
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;
    }

    /**
     * Get the path to the cached services.php file.
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return '';
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        return '';
    }

    /**
     * Determine if the given type has been bound.
     *
     * @param  string $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return $this->container->bound($abstract);
    }

    /**
     * Assign a set of tags to a given binding.
     *
     * @param  array|string $abstracts
     * @param  array|mixed ...$tags
     * @return void
     */
    public function tag($abstracts, $tags)
    {
        $this->container->tag($abstracts, $tags);
    }

    /**
     * Resolve all of the bindings for a given tag.
     *
     * @param  string $tag
     * @return array
     */
    public function tagged($tag)
    {
        return $this->container->tagged($tag);
    }

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param  string $abstract
     * @param  \Closure|string|null $concrete
     * @param  bool $shared
     * @return void
     */
    public function bindIf($abstract, $concrete = null, $shared = false)
    {
        $this->container->bindIf($abstract, $concrete, $shared);
    }

    /**
     * Register a shared binding in the container.
     *
     * @param  string $abstract
     * @param  \Closure|string|null $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->container->singleton($abstract, $concrete);
    }

    /**
     * "Extend" an type in the container.
     *
     * @param  string $abstract
     * @param  \Closure $closure
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function extend($abstract, \Closure $closure)
    {
        $this->container->extend($abstract, $closure);
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string $abstract
     * @param  mixed $instance
     * @return mixed
     */
    public function instance($abstract, $instance)
    {
        return $this->container->instance($abstract, $instance);
    }

    /**
     * Define a contextual binding.
     *
     * @param  string $concrete
     * @return \Illuminate\Contracts\Container\ContextualBindingBuilder
     */
    public function when($concrete)
    {
        return $this->container->when($concrete);
    }

    /**
     * Get a closure to resolve the given type from the container.
     *
     * @param  string $abstract
     * @return \Closure
     */
    public function factory($abstract)
    {
        return function () use ($abstract) {
            return $this->container->get($abstract);
        };
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string $abstract
     * @param  array $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        return $this->container->make($abstract, $parameters);
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param  callable|string $callback
     * @param  array $parameters
     * @param  string|null $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        return $this->container->call($callback, $parameters, $defaultMethod);
    }

    /**
     * Determine if the given type has been resolved.
     *
     * @param  string $abstract
     * @return bool
     */
    public function resolved($abstract)
    {
        return $this->container->resolved($abstract);
    }

    /**
     * Register a new resolving callback.
     *
     * @param  \Closure|string $abstract
     * @param  \Closure|null $callback
     * @return void
     */
    public function resolving($abstract, \Closure $callback = null)
    {
        $this->container->resolving($abstract, $callback);
    }

    /**
     * Register a new after resolving callback.
     *
     * @param  \Closure|string $abstract
     * @param  \Closure|null $callback
     * @return void
     */
    public function afterResolving($abstract, \Closure $callback = null)
    {
        $this->container->afterResolving($abstract, $callback);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

}
