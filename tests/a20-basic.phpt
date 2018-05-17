--TEST--
Test basic syntax 
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a20-basic.phpt', $dir.'a20-basic.phb');
?>
--FILE--
<?php
include("a20-basic.phb");
unlink(dirname(__FILE__).'/a20-basic.phb');
exit;
//--CODE--
  echo "test line 1\n";
  echo "test line".' ', 4 / 2, "\n";
?>
test line 3
--EXPECT--
test line 1
test line 2
test line 3
