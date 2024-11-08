<?php
class ColonFormatRoute {
  public $path;
  public $fn;

  function __construct(string $path, $fn) {
    $this->path = $path;
    $this->fn = $fn;
  }

  function args() {
    $Args = new ColonFormatArgs();
    foreach ($this->reflectParameters() as $Param) {
      $name = $Param->getName();
      $is_required = !$Param->isDefaultValueAvailable();

      $Arg = $Args->add($name);
      $Arg->is_required = $is_required;
    }
    return $Args;
  }

  function help() {
    $arg_ret = [];
    foreach ($this->args() as $Arg) {
      $arg_ret[] = sprintf('%s%s', $Arg, !$Arg->is_required ? '(?)' : '');
    }

    if ($arg_ret) return sprintf('%s :: %s', $this->path, implode(', ', $arg_ret));
    return $this->path;
  }

  private function reflect() {
    $fn = $this->fn;

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

  private function reflectParameters() {
    return $this->reflect()->getParameters();
  }
}