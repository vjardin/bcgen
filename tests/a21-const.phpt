--TEST--
Test constants
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a21-const.phpt', $dir.'a21-const.phb');
?>
--FILE--
<?php
include("a21-const.phb");
unlink(dirname(__FILE__).'/a21-const.phb');
exit;
//--CODE--
define("AAA", '1');
define("BBB", '222', 1);
define("PI", 3.1415926);
define("True", True);
error_reporting(E_ALL & !E_NOTICE);
echo AAA, "\n", aaa, "\n", BBB, "\n", bbb."\n";
echo "Pi=".PI, "\n";
var_dump(True);
echo __LINE__, "\n";
?>
--EXPECT--
1
aaa
222
222
Pi=3.1415926
bool(true)
10
