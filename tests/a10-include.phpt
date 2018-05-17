--TEST--
Test include() with PHP code
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_phpcode($dir.'a10-include.phpt', $dir.'a10-include.phb');
?>
--FILE--
<?php
include("a10-include.phb");
unlink(dirname(__FILE__).'/a10-include.phb');
exit;
//--CODE--
echo "ok\n";
?>
--EXPECT--
ok
