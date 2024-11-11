<?php
class ColonFormatJob {
  private $ObjectMethod;
  private $Route;
  private $args;

  function __construct(ColonFormatRoute $Route, array $args) {
    $this->Route = $Route;
    $this->args = $args;
  }

  function args() {
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
    return $Args->validate($this->args);
  }

  function run() {
    $this->validate();
    $fn = $this->Route->fn;
    $fn_args = $this->Route->funcArgs($this->args);

    list($Object, $method) = $this->ObjectMethod();
    if ($Object && $method) {
      $fn = [$Object, $method];
      $Refl = new ReflectionClass($Object);

      foreach ($this->args as $key => $value) {
        if (! $Refl->hasProperty($key)) continue;
        $Object->$key = $value;
      }
    }
    
    return $fn(...$fn_args);
  }

  function ObjectMethod() {
    if (! $this->ObjectMethod) {
      $this->ObjectMethod = $this->Route->ObjectMethod();
    }
    return $this->ObjectMethod;
  }

  function __toString() {
    $args = [];
    foreach ($this->args as $key => $value) {
      $args[] = escapeshellarg(sprintf('%s:%s', $key, $value));
    }

    return sprintf('%s %s', escapeshellarg($this->Route->path), implode(' ', $args));
  }
}