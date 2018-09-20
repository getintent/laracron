<?php

namespace Trig\LaraCron\Command;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\ScheduleRunCommand as ParentCommand;
use Trig\LaraCron\Exception\ExitCommandException;

class ScheduleRunCommand extends ParentCommand
{

    public function handle()
    {
        foreach ($this->getLaravel()->get('config')['scheduledJobs'] ?? [] as $definition => $commands) {
            foreach ($commands as $command) {
                $event = $this->schedule->exec($command)
                    ->name($this->getUniqueName($definition, $command))
                    ->onOneServer();

                $isCronDefinition = false !== strpos($definition, ' ');

                if ($isCronDefinition) {
                    $event->cron($definition);
                } elseif (method_exists($event, $definition)) {
                    $event->{$definition}();
                } else {
                    $this->output->writeln("<fg=red>ERROR:</> Seems that command schedule definition <comment>{$definition}</comment> is wrong for <comment>{$command}</comment> command");
                    throw new ExitCommandException('ERROR_CMD_DEFINITION', ExitCodes::ERROR_CMD_DEFINITION);
                }
            }
        }

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
}
