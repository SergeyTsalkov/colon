#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

class MyClass {
  public $race;

  function hello($name, int $age = 15) { 
    echo "hello {$name}! your age is {$age}\n";

    if ($this->race) {
      echo "your race is {$this->race}\n";
    }
  }
}


$Router = new ColonFormatRouter();
$Router->add('testroute:one', 'MyClass:hello');

$Parser = new ColonFormatParser($Router);
$Job = $Parser->parseArgv($argv);
$Job->run();
