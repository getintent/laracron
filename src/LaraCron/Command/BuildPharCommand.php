<?php

namespace Trig\LaraCron\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trig\LaraCron\ExitCodes;

class BuildPharCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'build:phar';

    /**
     * @var string
     */
    protected $description = 'Builds Phar executable';

    /**
     * @var array
     */
    private $buildDirectories = [
        '/bin',
        '/src',
        '/vendor',
    ];

    public function handle()
    {
        $basePath = $this->getLaravel()['config']['basePath'];

        $this->output->progressStart(3);
        $this->output->progressAdvance(1);

        $files = \Symfony\Component\Finder\Finder::create()
            ->in(
                array_map(
                    function ($path) use ($basePath) {
                        return $basePath.$path;
                    },
                    $this->buildDirectories
                )
            )
            ->ignoreVCS(true)
            ->ignoreDotFiles(true);

        $pharFile = $basePath.'/laracron.phar';
        $stubFile = $basePath.'/bin/laracron.php';

        $this->output->progressAdvance(1);
        $phar = new \Phar($pharFile);
        $phar->buildFromIterator($files->getIterator(), $basePath);

        $this->output->progressAdvance(1);
        $phar->setStub($phar->createDefaultStub($stubFile));
        $this->output->writeln("Build completed. Phar saved to <comment>{$pharFile}</comment>");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (ini_get('phar.readonly')) {
            $this->output->writeln("\n<fg=yellow>NOTE:</> Please run this command with <comment>-dphar.readonly=0</comment> parameter\n");
            exit(ExitCodes::ERROR_PHAR_READONLY);
        }
    }


}
