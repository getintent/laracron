# Laracron

This tool adds a capability to manage your Cron-based CLI tasks via simple JSON configuration.
Once configured it allows you to control you scheduled tasks without plain old Cron interface.
Inspired by Laravel [Task Scheduling]

## Installation
Manager designed to be used as phar executable, so you need to build it first.
Clone project first:

```
git clone --depth 1 https://github.com/trig/lara-cron.git
cd lara-cron
```
Then compile Phar executable with the following command:
```
php -dphar-readonly=0 bin/laracron.php build:phar
```
After this step you will find `laracron.phar` file in the current directory.
Then you need to add only one Cron task:
```
* * * * * cd /path-to-your-project && php laracron.phar schedule:run >> /dev/null 2>&1
``` 
That's all it needs.

## Usage
Now you can configure manager for your project needs, just copy `laracron.phar` file to your project root folder. Here are default configuration file (laracron.json):
```
{
  "scheduledJobs": {
    "everyMinute": [
      "php --version"
    ],
    "* * * * *": [
    ]
  },

  "cache.default": "file",

  "log_to": "laracron_cache/laracron.log",
  "cache.prefix": "laracron",

  "cache.stores.file": {
    "driver": "file",
    "path": "laracron_cache"
  },

  "cache.stores.redis": {
    "driver": "redis",
    "host": "localhost",
    "password": null,
    "port": 6379,
    "database": 0
  }
}

```
Here is the brief description for each configuration option:
#### `scheduledJobs`
Is the main configuration option, needed for scheduling you CLI commands,
it is a JSON object with keys standing for Cron job definition of specific method names.
You can see full list on [Task Scheduling] page. Here are some examples:

|Method|Description|
|:---|:---|
|->cron('* * * * *');|Run the task on a custom Cron schedule|
|->everyMinute();|Run the task every minute|
|->everyFiveMinutes();|Run the task every five minutes|
|->everyTenMinutes();|Run the task every ten minutes|
|->everyFifteenMinutes();|Run the task every fifteen minutes|
|->everyThirtyMinutes();|Run the task every thirty minutes|
|->hourly();|Run the task every hour|
|->daily();|Run the task every day at midnight|
|->weekly();|Run the task every week|
|->monthly();|Run the task every month|
|->quarterly();|Run the task every quarter|
|->yearly();|Run the task every year|

So, based on the above description, let's define some simple task and schedule it to run each 15 minutes,
just omit object access operator and parenthesis for the definition:
```
...
"scheduledJobs": {
    "everyFifteenMinutes": [
      "echo 'Hey, I\'ll execute each 15 minutes'"
    ],
    // or just like you define a regular cron job:
    "*/15 * * * *": [
      "echo 'Hey, I\'ll execute each 15 minutes'"
    ]
},
...

```
You may have noticed, that parametrized definitions is not supported (e.g `->weeklyOn(1, '8:00');`) however it fully covered by Cron syntax.

#### `cache.default`
Stands for caching engine name can be one of `file` or `redis`

#### `log_to` (optional)
Relative path to log file where it will log execution output, be aware that log file is not rotated

#### `cache.prefix`
Used as prefix to cache entries it creates

#### `cache.stores.file`
Configuration for file cache engine

#### `cache.stores.redis`
Configuration for Redis cache engine


[Task scheduling]: https://laravel.com/docs/5.6/scheduling
