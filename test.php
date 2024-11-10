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


$Router = ColonFormatRouter::get();
$Router->add('testroute:one', 'MyClass:hello');
$Router->add('testroute:two', 'MyClass:expArray');

$Router->addExpansion('name', 'all', fn() => ['Sergey', 'Kevin', 'Dusty']);
$Router->addExpansion('age', 'all', fn() => [15, 30, 45]);

$Parser = new ColonFormatParser($Router);
$JobSet = $Parser->parseArgv($argv);
$JobSet->run();