<?php

namespace Trig\LaraCron\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'schedule:init';

    /**
     * @var string
     */
    protected $description = 'Initializes configuration for your scheduled jobs';

    public function handle()
    {
        $this->output->comment('todo...');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelperSet()->get('formatter');

        $this->output->writeln(['', $formatter->formatBlock('Welcome to the Laracron config generator', 'bg=blue;fg=black', true), '']);
    }

}
