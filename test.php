#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

class MyClass {
  public $race='human';

  function config() {
    return ['channel' => 'common'];
  }

  function hello_config() {
    return ['channel' => 'config', 'threads' => 10];
  }

  function hello_args() {
    $Args = new ColonFormatArgs();
    $Args->add('race');
    return $Args;
  }

  function hello_validate($name) {
    if ($name == 'Kevin') {
      throw new Exception("name must not be Kevin");
    }
  }

  function validate() {
    if ($this->race == 'black') {
      throw new Exception("race must not be black");
    }
  }

  function hello($name, int $age = 15) { 
    echo "hello {$name}! your age is {$age}\n";

    if ($this->race) {
      echo "your race is {$this->race}\n";
    }
  }

  function expArray_args() {
    $Args = new ColonFormatArgs();
    $Args->add('title')->required();
    return $Args;
  }

  function expArray() {
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

$result = $JobSet->run();
if ($result) {
  echo $result . "\n";
}
