<?php
class ColonFormatJob {
  private $route;
  private $fn;
  private $args;

  function __construct(string $route, $fn, array $args) {
    $this->route = $route;
    $this->fn = $fn;
    $this->args = $args;
  }

  function run() {
    return $this->runWithArgs();
  }

  private function runWithArgs() {
    $fn = $this->fn;
    $args = $this->args;

    $fn_args = [];
    foreach ($this->reflectParameters() as $Param) {
      $name = $Param->getName();

      if (array_key_exists($name, $args)) {
        $fn_args[] = $args[$name];
      } else if ($Param->isDefaultValueAvailable()) {
        $fn_args[] = $Param->getDefaultValue();
      } else {
        throw new Exception("Unable to run-- $name is not defined!");
      }
    }

    return $fn(...$fn_args);
  }
}