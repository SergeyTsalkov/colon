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
}