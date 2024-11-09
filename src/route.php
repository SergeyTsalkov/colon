<?php
class ColonFormatRoute {
  // path assigned in route creation
  public $path;

  // function passed in route creation, may require massaging to actually run
  public $fn;

  // massaged $fn, should be runnable
  private $runnable;

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

  function run(array $args=[]) {
    if ($Object = $this->runnableObject()) {
      $Refl = new ReflectionClass($Object);

      foreach ($args as $key => $value) {
        if (! $Refl->hasProperty($key)) continue;
        $Object->$key = $value;
      }
    }

    $runnable = $this->runnable();
    $fn_args = $this->runnableFuncArgs($args);
    return $runnable(...$fn_args);
  }

  private function runnableObject() {
    $runnable = $this->runnable();

    if (is_array($runnable) && count($runnable) == 2 && is_object($runnable[0])) {
      return $runnable[0];
    }
  }

  private function runnableFuncArgs(array $args) {
    $fn_args = [];
    foreach ($this->reflectParameters() as $Param) {
      $name = $Param->getName();

      if (array_key_exists($name, $args)) {
        $fn_args[] = $args[$name];
      } else if ($Param->isDefaultValueAvailable()) {
        $fn_args[] = $Param->getDefaultValue();
      } else {
        throw new Exception("Unable to run: argument $name is not defined!");
      }
    }

    return $fn_args;
  }

  private function runnable() {
    if (! $this->runnable) $this->runnable = $this->_runnable();
    return $this->runnable;
  }

  private function _runnable() {
    if (is_callable($this->fn)) {
      if ($this->fn instanceof Closure) {
        return $this->fn;
      }
      if (is_array($this->fn) && count($this->fn) == 2) {
        return $this->fn;
      }
    }
    
    if (is_string($this->fn)) {
      if (function_exists($this->fn)) return $this->fn;

      $class = $method = '';
      if (substr_count($this->fn, '::')) {
        list($class, $method) = explode('::', $this->fn, 2);
      }
      else if (substr_count($this->fn, ':')) {
        list($class, $method) = explode(':', $this->fn, 2);
      }

      if (!$class || !$method) {
        throw new Exception("Function {$this->fn} does not look valid");
      }
      if (! class_exists($class)) {
        throw new Exception("Class $class does not exist");
      }

      $Refl = (new ReflectionClass($class))->getMethod($method);
      if ($Refl->isStatic()) {
        return [$class, $method];
      }

      return [new $class(), $method];
    }


    throw new Exception("Function {$this->fn} does not look valid");
  }

  private function reflect() {
    $fn = $this->runnable();

    if ($fn instanceof Closure) {
      return new ReflectionFunction($fn);
    }
    if (is_array($fn) && count($fn) == 2) {
      $Refl = new ReflectionClass($fn[0]);
      return $Refl->getMethod($fn[1]);
    }
    if (is_string($fn) && function_exists($fn)) {
      return new ReflectionFunction($fn);
    }

    throw new Exception("This doesn't seem to be a callable function or method");
  }

  private function reflectParameters() {
    return $this->reflect()->getParameters();
  }
}