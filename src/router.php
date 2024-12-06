<?php
class ColonFormatRouter implements Iterator, Countable {
  private $routes=[];
  private $expansions=[];
  private $position=0; // iterator

  function add(string $path, $fn) {
    $this->routes[$path] = new ColonFormatRoute($path, $fn);
  }

  function find(string $path) {
    if (strlen($path) == 0) {
      return new ColonFormatRoute('', [$this, 'help']);
    }

    $Route = $this->routes[$path] ?? null;
    if (! $Route) {
      throw new Exception("Unknown route: $path");
    }
    return $Route;
  }

  function addExpansion(string $key, string $value, callable $fn) {
    $this->expansions[$key][$value] = $fn;
  }

  function findExpansion(string $key, string $value) {
    return $this->expansions[$key][$value] ?? null;
  }

  function help() {
    $results = ["--- AVAILABLE COMMANDS ---"];
    foreach ($this->routes as $Route) {
      $results[] = $Route->help();
    }
    return implode("\n", $results);
  }

  function makeJob(string $path, array $args): ColonFormatJobSet {
    $Route = $this->find($path);
    $Job = new ColonFormatJob($Route, $args);
    return $this->expand($Job);
  }

  private function expand(ColonFormatJob $Job) {
    $expansions = [];

    foreach ($Job->args() as $key => $value) {
      if (! is_string($value)) continue;

      if ($fn = $this->findExpansion($key, $value)) {
        $expansions[] = [$key, $value, $fn];
      }
    }

    $JobSet = new ColonFormatJobSet($Job);
    foreach ($expansions as list($key, $value, $fn)) {
      $new_values = $fn();
      $this->expandKV($JobSet, $key, $value, $new_values);
    }
    return $JobSet;
  }

  private function expandKV(ColonFormatJobSet $JobSet, string $key, string $value, array $new_values) {
    $ReplaceJobs = array_filter(iterator_to_array($JobSet), fn($Job) => $Job->hasArg($key));

    foreach ($ReplaceJobs as $Job) {
      $JobSet->remove($Job);
      foreach ($new_values as $new_value) {
        $NewJob = clone $Job;
        $NewJob->setArg($key, $new_value);
        $JobSet->add($NewJob);
      }
    }
  }

  static function get() {
    static $Router;
    if (! $Router) $Router = new self();
    return $Router;
  }

  // ***** Count
  #[\ReturnTypeWillChange]
  function count() {
    return count($this->routes);
  }

  // ***** Iterator
  #[\ReturnTypeWillChange]
  function current() {
    $routes = array_values($this->routes);
    return $this->valid() ? $routes[$this->position] : null;
  }
  #[\ReturnTypeWillChange]
  function key() {
    return $this->position;
  }
  #[\ReturnTypeWillChange]
  function next() {
    $this->position++;
  }
  #[\ReturnTypeWillChange]
  function rewind() {
    $this->position = 0;
  }
  #[\ReturnTypeWillChange]
  function valid() {
    return $this->position < $this->count();
  }
}