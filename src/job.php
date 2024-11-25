<?php
class ColonFormatJob {
  private $Object;
  private $Route;
  private $args;

  function __construct(ColonFormatRoute $Route, array $args) {
    $this->Route = $Route;
    $this->args = $args;
  }

  function args($include_shadow=false) {
    $shadow = [
      'job_path' => $this->Route->path,
    ];

    if ($include_shadow) {
      return array_merge($shadow, $this->args);
    }
    return $this->args;
  }

  function hasArg(string $key) {
    return array_key_exists($key, $this->args);
  }

  function setArg(string $key, string $value) {
    $this->args[$key] = $value;
  }

  function validate() {
    $Args = $this->Route->expectedArgs();
    $Args->validate($this->args);

    $this->runAdjacentFunc('_validate');
    $this->runAdjacentFunc('validate');
  }

  function run() {
    $this->validate();
    $fn = $this->Route->fn;
    if ($ObjectMethod = $this->ObjectMethod()) {
      $fn = $ObjectMethod;
    }
    
    return ColonFormatRoute::runWithArgs($fn, $this->args(true));
  }

  function runConfig() {
    $general = $this->runAdjacentFunc('config', 'array') ?: [];
    $specific = $this->runAdjacentFunc('_config', 'array') ?: [];
    return array_merge($general, $specific);
  }

  function runAdjacentFunc(string $name, ?string $expected_type=null) {
    if ($Object = $this->Object()) {
      return $this->Route->runAdjacentFunc($Object, $name, $expected_type, $this->args(true));
    }
  }

  function Object() {
    if (! $this->Object) {
      $this->Object = $this->Route->Object();
      if ($this->Object) {
        $Refl = new ReflectionClass($this->Object);

        foreach ($this->args(true) as $key => $value) {
          if (! $Refl->hasProperty($key)) continue;
          $this->Object->$key = $value;
        }
      }
    }

    return $this->Object;
  }

  function method() {
    return $this->Route->method();
  }

  function ObjectMethod(): array {
    $Object = $this->Object();
    $method = $this->method();
    if ($Object && $method) {
      return [$Object, $method];
    }
    return [];
  }

  function __toString() {
    return ColonFormatParser::makeArgv($this->Route->path, $this->args());
  }
}