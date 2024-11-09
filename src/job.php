<?php
class ColonFormatJob {
  private $Route;
  private $args;

  function __construct(ColonFormatRoute $Route, array $args) {
    $this->Route = $Route;
    $this->args = $args;
  }

  function run() {
    return $this->Route->run($this->args);
  }

  function __toString() {
    $args = [];
    foreach ($this->args as $key => $value) {
      $args[] = escapeshellarg(sprintf('%s:%s', $key, $value));
    }

    return sprintf('%s %s', escapeshellarg($this->Route->path), implode(' ', $args));
  }
}