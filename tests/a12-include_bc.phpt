--TEST--
Test include() with simple bytecode
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a12-include_bc.phpt', $dir.'a12-include_bc.phb');
?>
--FILE--
<?php
include("a12-include_bc.phb");
unlink(dirname(__FILE__).'/a12-include_bc.phb');
exit;
//--CODE--
echo "ok\n";
?>
--EXPECT--
ok
