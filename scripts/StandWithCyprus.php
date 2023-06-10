#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

try {
  (new SingleCommandApplication())
    ->setCode(function(InputInterface $input, OutputInterface $output) {
      $output->writeln('Political message: <bg=white;fg=blue> #StandWith </><fg=black;bg=#f9a942> Cyprus </>');
    })
    ->run();
  }
  catch (ExceptionInterface $e) {
}
