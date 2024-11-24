<?php
class ColonFormatJobSet implements Iterator, Countable {
  private $Jobs = [];
  private $position = 0;

  function __construct(ColonFormatJob $Job) {
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
      $result = $Job->run();

      if (is_string($result) && strlen($result) > 0) {
        $results[] = $result;
      }
    }
    return implode("\n", $results);
  }

  function runConfig() {
    if (! $this->Jobs) return;
    return $this->Jobs[0]->runConfig();
  }

  function validate() {
    foreach ($this->Jobs as $Job) {
      $Job->validate();
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