<?php
class ColonFormatArgs implements Iterator, Countable {
  private $args = [];
  private $position=0; // iterator

  function add($name) {
    $Arg = new ColonFormatArg($name);
    $this->args[] = $Arg;
    return $Arg;
  }

  // ***** Count
  #[\ReturnTypeWillChange]
  function count() {
    return count($this->args);
  }

  // ***** Iterator
  #[\ReturnTypeWillChange]
  function current() {
    return $this->valid() ? $this->args[$this->position] : null;
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