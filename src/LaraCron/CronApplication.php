<?php

namespace LaraCron;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class CronApplication implements Application
{
  const VERSION = '0.1.0';

  /**
   * @var Container
   */
  private $container;

  public function __construct(Container $container)
  {
      $this->container = $container;

    $container['version'] = self::VERSION;
    $container['rootDir'] = __DIR__;
  }

  /**
   * Get the version number of the application.
   *
   * @return string
   */
  public function version()
  {
    return $this->container[];
  }

  /**
   * Get the base path of the Laravel installation.
   *
   * @return string
   */
  public function basePath()
  {
    return __DIR__;
  }

  /**
   * Get or check the current application environment.
   *
   * @return string
   */
  public function environment()
  {
    return 'development';
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
  }

  /**
   * Register a new boot listener.
   *
   * @param  mixed $callback
   * @return void
   */
  public function booting($callback)
  {
  }

  /**
   * Register a new "booted" listener.
   *
   * @param  mixed $callback
   * @return void
   */
  public function booted($callback)
  {
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
    return true;
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
  }

  /**
   * Resolve all of the bindings for a given tag.
   *
   * @param  string $tag
   * @return array
   */
  public function tagged($tag)
  {
    return [];
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
  public function extend($abstract, Closure $closure)
  {
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
    return new \stdClass();
  }

  /**
   * Define a contextual binding.
   *
   * @param  string $concrete
   * @return \Illuminate\Contracts\Container\ContextualBindingBuilder
   */
  public function when($concrete)
  {
    return new \Illuminate\Container\ContextualBindingBuilder();
  }

  /**
   * Get a closure to resolve the given type from the container.
   *
   * @param  string $abstract
   * @return \Closure
   */
  public function factory($abstract)
  {
    return new \Closure(function () {
    });
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
    if ('Illuminate\\Console\\OutputStyle' === $abstract) {
      return new \Illuminate\Console\OutputStyle(
        new \Symfony\Component\Console\Input\ArgvInput(),
        new \Symfony\Component\Console\Output\ConsoleOutput()
      );
    }
    return null;
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
    return call_user_func_array($callback, $parameters);
  }

  /**
   * Determine if the given type has been resolved.
   *
   * @param  string $abstract
   * @return bool
   */
  public function resolved($abstract)
  {
    return true;
  }

  /**
   * Register a new resolving callback.
   *
   * @param  \Closure|string $abstract
   * @param  \Closure|null $callback
   * @return void
   */
  public function resolving($abstract, Closure $callback = null)
  {
  }

  /**
   * Register a new after resolving callback.
   *
   * @param  \Closure|string $abstract
   * @param  \Closure|null $callback
   * @return void
   */
  public function afterResolving($abstract, Closure $callback = null)
  {
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
    return new \stdClass();
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
    return true;
  }
}
