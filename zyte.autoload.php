<?php
# REQUIRE CONFIG FILE
require_once dirname(__FILE__) . '/zyte.config.php';

# CREATE AUTOLOADER
spl_autoload_register(function ($class) {
  $ds = DIRECTORY_SEPARATOR;
  $dir = __DIR__;

  $class = str_replace('\\', $ds, $class);

  $file = "{$dir}{$ds}{$class}.php";

  if (is_readable($file)) {
    require_once $file;
  }
});
?>
