#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

class MyClass {
  function hello($name, int $age = 15) { 
    echo "hello {$name}! your age is {$age}\n";
  }
}


$Router = new ColonFormatRouter();
$Router->add('testroute:one', 'MyClass:hello');

$Parser = new ColonFormatParser($Router);
foreach ($Router as $Route) {
  echo $Route->help() . "\n";
}
