<?php
class ColonFormatJobSet implements Iterator, Countable {
  private $Router;
  private $Jobs = [];
  private $position = 0;

  function __construct(ColonFormatRouter $Router, ColonFormatJob $Job) {
    $this->Router = $Router;
    $this->add($Job);
  }

  function add(ColonFormatJob $Job) {
    $this->Jobs[] = $Job;
  }

  function remove(ColonFormatJob $Job) {
    $this->Jobs = array_values(array_filter($this->Jobs, fn($_Job) => $Job !== $_Job));
  }

  function run() {
    $results = [];
    foreach ($this->Jobs as $Job) {
      $results[] = $Job->run();
    }
    return implode("\n", $results);
  }

  function expand() {
    $expansions = [];

    foreach ($this->Jobs as $Job) {
      foreach ($Job->args() as $key => $value) {
        if ($fn = $this->Router->findExpansion($key, $value)) {
          $expansions[] = [$key, $value, $fn];
        }
      }
    }

    foreach ($expansions as list($key, $value, $fn)) {
      $new_values = $fn();
      $this->expandKV($key, $value, $new_values);
    }
  }

  private function expandKV(string $key, string $value, array $new_values) {
    foreach ($this->Jobs as $Job) {
      if (! $Job->hasArg($key)) continue;

      $this->remove($Job);
      foreach ($new_values as $new_value) {
        $NewJob = clone $Job;
        $NewJob->setArg($key, $new_value);
        $this->add($NewJob);
      }
    }
  }

  // ***** Count
  #[\ReturnTypeWillChange]
  function count() {
    return count($this->Jobs);
  }

  // ***** Iterator
  #[\ReturnTypeWillChange]
  function current() {
    return $this->valid() ? $this->Jobs[$this->position] : null;
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