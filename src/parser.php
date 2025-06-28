<?php
class ColonFormatParser {
  private $Router;
  
  function __construct(ColonFormatRouter $Router) {
    $this->Router = $Router;
  }

  static function parseArgv(array $argv): array {
    array_shift($argv);
    $path = strval(array_shift($argv));

    $args = [];
    foreach ($argv as $arg) {
      list($key, $value) = explode(':', $arg, 2);
      if (!substr_count($arg, ':') || !strlen($key)) {
        throw new Exception('Arguments should be in the format "key:value"');
      }

      $args[$key] = $value;
    }

    return [$path, $args];
  }

  static function makeArgv(string $path, array $args=[]) {
    $parts = [$path];
    foreach ($args as $key => $value) {
      if (is_bool($value)) $value = $value ? 'true' : 'false';
      $parts[] = sprintf('%s:%s', $key, $value);
    }

    return implode(' ', array_map('escapeshellarg', $parts));
  }

  function JobFromArgv(array $argv): ColonFormatJobSet {
    list($path, $args) = self::parseArgv($argv);
    return $this->Router->makeJob($path, $args);
  }

}