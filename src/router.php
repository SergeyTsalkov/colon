<?php
class ColonFormatRouter implements Iterator, Countable {
  private $routes=[];
  private $position=0; // iterator

  function add($path, $fn) {
    $this->routes[$path] = new ColonFormatRoute($path, $fn);
  }

  function find($path) {
    $Route = $this->routes[$path] ?? null;
    if (! $Route) {
      throw new Exception("Unknown route: $path");
    }
    return $Route;
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