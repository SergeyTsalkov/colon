<?php
class ColonFormatJob {
  private $fn;
  private $args;

  function __construct($fn, array $args) {
    if (! is_callable($fn)) {
      throw new Exception("Not a callable function: $fn");
    }

    $this->fn = $fn;
    $this->args = $args;
  }

  function run() {
    return $this->run_with_args($this->fn, $this->args);
  }

  private function run_with_args(callable $fn, array $args) {
    $Refl = $this->reflect($fn);

    $fn_args = [];
    foreach ($Refl->getParameters() as $Param) {
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

  private function reflect(callable $fn) {
    if ($fn instanceof Closure) {
      return new ReflectionFunction($fn);
    }
    if (is_array($fn) && count($fn) == 2) {
      $Refl = new ReflectionClass($fn[0]);
      return $Refl->getMethod($fn[1]);
    }
    if (is_string($fn)) {
      if (function_exists($fn)) {
        return new ReflectionFunction($fn);
      }

      $class = $method = '';
      if (substr_count($fn, '::')) {
        list($class, $method) = explode('::', $fn, 2);
      }
      else if (substr_count($fn, ':')) {
        list($class, $method) = explode(':', $fn, 2);
      }
      if ($class && $method) {
        $Refl = new ReflectionClass($class);
        return $Refl->getMethod($method);
      }
    }

    throw new Exception("This doesn't seem to be a callable function or method");
  }
}