--TEST--
Bug #16978 - Constant function parameters fail
--SKIPIF--
<?php
  //if (version_compare(PHP_VERSION, '5.3.0', '<')) die("skip PHP 5.3 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'bug-16978.phpt', $dir.'bug-16978.phb');
?>
--FILE--
<?php
include("bug-16978.phb");
unlink(dirname(__FILE__).'/bug-16978.phb');
exit;
--CODE--
define("SOME_CONST",15);
function foo($var = SOME_CONST) { echo "var=$var\n"; }

foo();
--EXPECT--
var=15
