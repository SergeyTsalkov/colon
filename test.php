#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';


$Router = new ColonFormatRouter();
$Router->add('testroute:one', function($name, int $age = 15) { 
  echo "hello {$name}! your age is {$age}\n";
});

$Parser = new ColonFormatParser($Router);
$Job = $Parser->parseArgv($argv);
$Job->run();