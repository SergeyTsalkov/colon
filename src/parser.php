<?php
class ColonFormatParser {
  private $Router;
  
  function __construct(ColonFormatRouter $Router) {
    $this->Router = $Router;
  }

  function parseArgv(array $argv) {
    array_shift($argv);
    $path = array_shift($argv);
    if (! $path) throw new Exception("You didn't specify a function to run!");

    $args = [];
    foreach ($argv as $arg) {
      list($key, $value) = explode(':', $arg, 2);
      $args[$key] = $value;
    }

    return $this->makeJob($path, $args);
  }

  function makeJob(string $path, array $args) {
    $Route = $this->Router->find($path);
    $Job = new ColonFormatJob($Route, $args);
    $JobSet = new ColonFormatJobSet($this->Router, $Job);
    $JobSet->expand();
    return $JobSet;
  }
}