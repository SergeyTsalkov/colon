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

  function expArray(array $hash=[]) {
    var_dump($hash);
  }
}


$Router = new ColonFormatRouter();
$Router->add('testroute:one', 'MyClass:hello');
$Router->add('testroute:two', 'MyClass:expArray');

$Parser = new ColonFormatParser($Router);
$Job = $Parser->parseArgv($argv);
$Job->run();
