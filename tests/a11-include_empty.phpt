--TEST--
Test include() with empty file
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  touch($dir.'a11-include.phb');
?>
--FILE--
<?php
include("a11-include.phb");
unlink(dirname(__FILE__).'/a11-include.phb');
exit;
//--CODE--
?>
--EXPECT--
