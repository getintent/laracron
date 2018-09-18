<?php

require __DIR__ . '/../vendor/autoload.php';

$baseDir = __DIR__ . '/..';

$files = \Symfony\Component\Finder\Finder::create()
  ->in([
    $baseDir . '/bin',
    $baseDir . '/src',
    $baseDir . '/vendor',
  ])->ignoreVCS(true)
  ->ignoreDotFiles(true);

$io = new \Illuminate\Console\OutputStyle(
  new \Symfony\Component\Console\Input\ArgvInput(),
  new \Symfony\Component\Console\Output\ConsoleOutput()
);

$phar = new Phar($baseDir . '/laracron.phar');
$io->progressStart($files->count());
foreach ($files as $file){
   $phar->addFile($file);
   $io->progressAdvance(10);
}
$io->progressFinish();

$phar->setStub($phar->createDefaultStub('bin/cron.php'));
print "\ndone.\n";


