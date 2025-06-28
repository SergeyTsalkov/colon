#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

class MyClass {
  public $race='human';
  public $job_path;
  public bool $good=false;

  function config() {
    return ['channel' => 'common'];
  }

  function hello_config() {
    return ['channel' => 'config', 'threads' => 10];
  }

  function three_validate(array $args) {
    var_dump($args);
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
    echo "the path is {$this->job_path}\n";

    if ($this->race) {
      echo "your race is {$this->race}\n";
    }
  }

  function expArray_args() {
    $Args = new ColonFormatArgs();
    $Args->add('title')->required();
    return $Args;
  }

  function expArray(bool $good) {
    var_dump($good);
  }

  function three() {
  }
}


$Router = ColonFormatRouter::get();
$Router->add('testroute:one', 'MyClass:hello');
$Router->add('testroute:two', 'MyClass:expArray');
$Router->add('testroute:three', 'MyClass:three');

$Router->addExpansion('name', 'all', fn() => ['Sergey', 'Josh', 'Dusty']);
$Router->addExpansion('age', 'all', fn() => [15, 30, 45]);

$Parser = new ColonFormatParser($Router);
$JobSet = $Parser->JobFromArgv($argv);

foreach ($JobSet as $Job) {
  echo "Running: $Job\n";
}

$result = $JobSet->run();
if ($result) {
  echo $result . "\n";
}
