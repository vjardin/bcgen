--TEST--
Test exceptions
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a29-exceptions.phpt', $dir.'a29-exceptions.phb');
?>
--FILE--
<?php
include("a29-exceptions.phb");
unlink(dirname(__FILE__).'/a29-exceptions.phb');
exit;
--CODE--
function inverse($x) {
    if (!$x) {
        throw new Exception('Division by zero.');
    }
    else return 1/$x;
}

try {
    echo inverse(5) . "\n";
    echo inverse(0) . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

// Continue execution
echo 'Hello World';
?>
--EXPECT--
0.2
Caught exception: Division by zero.
Hello World
