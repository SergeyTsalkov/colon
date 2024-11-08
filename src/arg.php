<?php
class ColonFormatArg {
  public $name;
  public $is_required=false;

  function __construct($name) {
    $this->name = $name;
  }

  function required() {
    $this->is_required = true;
    return $this;
  }

  function __toString() {
    return $this->name;
  }
}