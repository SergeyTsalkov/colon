<?php
class ColonFormatParser {
  private $Router;
  
  function __construct(ColonFormatRouter $Router) {
    $this->Router = $Router;
  }

  function parseArgv(array $argv) {
    array_shift($argv);
    $route = array_shift($argv);
    if (! $route) throw new Exception("You didn't specify a function to run!");

    $args = [];
    foreach ($argv as $arg) {
      list($key, $value) = explode(':', $arg, 2);
      $args[$key] = $value;
    }

    $Route = $this->Router->find($route);
    return new ColonFormatJob($Route, $args);
  }
}