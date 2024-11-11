<?php
class ColonFormatArgs implements Iterator, Countable {
  private $args = [];
  private $position=0; // iterator

  function add($name) {
    $Arg = new ColonFormatArg($name);
    $this->args[] = $Arg;
    return $Arg;
  }

  function validate(array $hash) {
    foreach ($this as $Arg) {
      if ($Arg->is_required && !array_key_exists($Arg->name, $hash)) {
        throw new Exception("Missing required argument: {$Arg->name}");
      }
    }
  }

  static function merge(?ColonFormatArgs ...$ArgSets) {
    $combined = [];
    foreach ($ArgSets as $ArgSet) {
      if (! $ArgSet) continue;

      foreach ($ArgSet as $Arg) {
        $combined[$Arg->name] = $Arg;
      }
    }

    $Combined = new ColonFormatArgs();
    $Combined->args = array_values($combined);
    return $Combined;
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