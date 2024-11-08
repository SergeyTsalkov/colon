<?php
class ColonFormatParser {
  private $Router;

  function __construct(ColonFormatRouter $Router) {
    $this->Router = $Router;
  }

  function parseArgv(array $argv) {
    array_shift($argv);
    $route = array_shift($argv);

    $args = [];
    foreach ($argv as $arg) {
      list($key, $value) = explode(':', $arg, 2);
      $args[$key] = $value;
    }

    $callable = $this->Router->find($route);
    return new ColonFormatJob($route, $callable, $args);
  }
}