<?php

namespace Trig\LaraCron\Command;

use Illuminate\Console\OutputStyle;
use Illuminate\Console\Scheduling\ScheduleRunCommand as ParentCommand;
use Symfony\Component\Finder\Finder;
use Trig\LaraCron\Exception\ExitCommandException;
use Trig\LaraCron\ExitCodes;

class ScheduleRunCommand extends ParentCommand
{

    public function handle()
    {
        $config = $this->getLaravel()->get('config');
        foreach ($config['scheduledJobs'] ?? [] as $definition => $commands) {
            foreach ($commands as $command) {
                $event = $this->schedule
                    ->exec($command)
                    ->name($this->getUniqueName($definition, $command))
                    ->onOneServer()
                    ->withoutOverlapping();

                if (isset($config['log_to']) && $config['log_to']) {
                    $event->appendOutputTo($config['log_to']);
                }

                $isCronDefinition = false !== strpos($definition, ' ');

                if ($isCronDefinition) {
                    $event->cron($definition);
                } elseif (method_exists($event, $definition)) {
                    $event->{$definition}();
                } else {
                    $this->output->writeln("<fg=red>ERROR:</> Seems that command schedule definition <comment>{$definition}</comment> is wrong for <comment>{$command}</comment> command");
                    throw new ExitCommandException('ERROR_CMD_DEFINITION', ExitCodes::ERROR_CMD_DEFINITION);
                }
                if ($this->output->isDebug()) {
                    $this->output->writeln("Scheduled <comment>{$command}</comment> command to run every <fg=blue>{$definition}</>");
                }
            }
        }

        $this->registerCacheGarbageCollector();

        parent::handle();
    }

    /**
     * @param string $definition
     * @param string $command
     * @return string
     */
    private function getUniqueName(string $definition, string $command): string
    {
        return implode(
            '.',
            [
                'laracron',
                crc32($definition.$command),
                preg_replace('/-{2,}/', '-', preg_replace('/[^\w]/', '-', $definition.'/'.$command)),
            ]
        );
    }

    private function registerCacheGarbageCollector()
    {
        $config = $this->getLaravel()->get('config');
        if ('file' !== $config['cache.default']) {
            return;
        }
        $cacheDir = $config['cache.stores.file']['path'] ?? null;
        if (null === $cacheDir) {
            throw new ExitCommandException('ERROR_CACHE_DIRECTORY_NOT_DEFINED', ExitCodes::ERROR_CACHE_DIRECTORY_NOT_DEFINED);
        }
        $cacheDir = $config['basePath'].DIRECTORY_SEPARATOR.ltrim($cacheDir, '/');
        $garbageCollector = function () use ($cacheDir) {
            $io = $this->getLaravel()->get(OutputStyle::class);
            $io->comment('Running cache garbage collecting task...');
            $cleanedFiles = 0;
            foreach (Finder::create()->in($cacheDir)->files()->getIterator() as $file) {
                $expireTime = (int)explode('b:', $file->getContents(), 2)[0] ?? 0;
                if ($expireTime && time() > $expireTime) {
                    $cleanedFiles++;
                    unlink($file->getRealPath());
                }
            }
            $io->comment("Cleaned up: <comment>{$cleanedFiles}</comment> expired files");
        };

        $this->schedule->call($garbageCollector)
            ->daily()
            ->name($this->getUniqueName('file-cache', 'garbage-collector'))
            ->onOneServer();
    }
}
