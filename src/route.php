<?php
class ColonFormatRoute {
  const TYPE_METHOD = 1;
  const TYPE_STATIC_METHOD = 2;
  const TYPE_CLOSURE = 3;
  const TYPE_FUNC_STRING = 4;

  // path assigned in route creation
  public $path;

  // massaged function, class:method strings have been converted
  // to [class, method] for simplicity
  public $fn;

  function __construct(string $path, $fn) {
    $this->path = $path;
    $this->fn = $this->parseFunc($fn);
  }

  function expectedArgs() {
    $Args1 = new ColonFormatArgs();
    foreach ($this->reflectParameters() as $Param) {
      $name = $Param->getName();
      $is_required = !$Param->isDefaultValueAvailable();

      $Arg = $Args1->add($name);
      $Arg->is_required = $is_required;
    }

    $Args2 = null;
    if ($this->funcType() == self::TYPE_METHOD) {
      $Args2 = $this->runAdjacentFunc(null, 'args', 'ColonFormatArgs');
      $Args3 = $this->runAdjacentFunc(null, '_args', 'ColonFormatArgs');
    }

    return ColonFormatArgs::merge($Args1, $Args2, $Args3);
  }

  function runAdjacentFunc(?object $Object, string $name, ?string $expected_type, array $args=[]) {
    if ($this->funcType() != self::TYPE_METHOD) {
      return;
    }

    if (! $Object) $Object = $this->Object();
    if (substr($name, 0, 1) == '_') $method = $this->method() . $name;
    else $method = $name;

    $fn = [$Object, $method];

    $result = null;
    if (is_callable($fn)) {
      $result = ColonFormatRoute::runWithArgs($fn, $args);
    }
    if (!is_null($result) && $expected_type) {
      $class_name = get_class($Object);

      if ($expected_type == 'array') {
        if (! is_array($result)) {
          throw new Exception("{$class_name}:{$method} should return an array");
        }
      }
      else if (!($result instanceof $expected_type)) {
        throw new Exception("{$class_name}:{$method} should return a {$expected_type} object");
      }
    }

    return $result;
  }

  static function runWithArgs(callable $fn, array $args) {
    $fn_args = [];
    $Params = ColonFormatRoute::_reflect($fn)->getParameters();
    if (count($Params) == 1 && strval($Params[0]->getType()) == 'array') {
      return $fn([$args]);
    }

    foreach ($Params as $Param) {
      $name = $Param->getName();

      if (array_key_exists($name, $args)) {
        $fn_args[] = ColonFormatRoute::massageType($Param->getType(), $args[$name]);
      } else if ($Param->isDefaultValueAvailable()) {
        $fn_args[] = $Param->getDefaultValue();
      } else {
        throw new Exception("Unable to run $fn: argument $name is not defined!");
      }
    }

    return $fn(...$fn_args);
  }

  function help() {
    $arg_ret = [];
    foreach ($this->expectedArgs() as $Arg) {
      $arg_ret[] = sprintf('%s%s', $Arg, !$Arg->is_required ? '(?)' : '');
    }

    if ($arg_ret) return sprintf('%s :: %s', $this->path, implode(', ', $arg_ret));
    return $this->path;
  }
  
  function ObjectMethod(): array {
    $Object = $this->Object();
    $method = $this->method();
    if ($Object && $method) {
      return [$Object, $method];
    }
    return [];
  }

  // create a new object if our $fn needs one
  // Job class should cache this result so class:validate() runs on the same object
  // as the main function we're going to run
  function Object() {
    if ($this->funcType() != self::TYPE_METHOD) {
      return;
    }

    $class_or_object = $this->fn[0];
    $Object = null;
    if (is_string($class_or_object)) {
      return new $class_or_object();
    } 
    if (is_object($class_or_object)) {
      return $class_or_object;
    }

    throw new Exception("Function {$this} does not look valid");
  }

  function Method() {
    if (! in_array($this->funcType(), [self::TYPE_METHOD, self::TYPE_STATIC_METHOD])) {
      return;
    }

    return $this->fn[1];
  }

  // massage the likely-callable we received
  // transform strings like class::method and class:method into [class, method]
  // we don't verify that it's actually callable until the verify/run steps
  private function parseFunc($fn) {
    if (is_array($fn) 
      && count($fn) == 2 
      && is_string($fn[1])
      && (is_string($fn[0]) || is_object($fn[0]))
    ) {
      return $fn;
    }

    if ($fn instanceof Closure && is_callable($fn)) {
      return $fn;
    }
    
    if (is_string($fn)) {
      if (function_exists($fn)) return $fn;

      $class = $method = '';
      if (substr_count($fn, '::')) {
        list($class, $method) = explode('::', $fn, 2);
      }
      else if (substr_count($fn, ':')) {
        list($class, $method) = explode(':', $fn, 2);
      }

      if (!$class || !$method) {
        throw new Exception("Function for $this does not look valid");
      }
      
      return [$class, $method];
    }


    throw new Exception("Function for $this does not look valid");
  }

  function funcType() {
    if ($this->fn instanceof Closure && is_callable($this->fn)) {
      return self::TYPE_CLOSURE;
    }
    if (is_string($this->fn) && is_callable($this->fn)) {
      return self::TYPE_FUNC_STRING;
    }
    if (is_array($this->fn) && count($this->fn) == 2) {
      if ($this->reflect()->isStatic()) {
        return self::TYPE_STATIC_METHOD;
      }
      return self::TYPE_METHOD;
    }

    throw new Exception("Function for $this does not look valid");
  }

  static function _reflect($fn) {
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
  }

  static function massageType(?ReflectionType $Type, $input) {
    if (! ($Type instanceof ReflectionNamedType)) return $input;
    if ($Type->getName() == 'bool') {
      if ($input === 'true') return true;
      if ($input === 'false') return false;
    }
    return $input;
  }

  private function reflect() {
    if ($result = self::_reflect($this->fn)) {
      return $result;
    }
    throw new Exception("Function for $this does not look valid");
  }

  private function reflectParameters() {
    return $this->reflect()->getParameters();
  }

  function __toString() {
    return $this->path;
  }
}