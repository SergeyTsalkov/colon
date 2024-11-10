<?php
class ColonFormatRouter implements Iterator, Countable {
  private $routes=[];
  private $expansions=[];
  private $position=0; // iterator

  function __construct() {
    $this->add('help', [$this, 'help']);
  }

  function add(string $path, $fn) {
    $this->routes[$path] = new ColonFormatRoute($path, $fn);
  }

  function find(string $path) {
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