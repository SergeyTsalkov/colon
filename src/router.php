<?php
class ColonFormatRouter {
  private $routes=[];

  function add($route, $fn) {
    $this->routes[$route] = $fn;
  }

  function find($route) {
    $callable = $this->routes[$route] ?? null;
    if (! $callable) {
      throw new Exception("Unknown route: $route");
    }
    return $callable;
  }
}