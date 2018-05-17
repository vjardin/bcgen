--TEST--
Test setting private properties from a subclass
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a50-class.phps', $dir.'a50-class.phb');
  make_bytecode($dir.'a56-class_privvar.phpt', $dir.'a56-class_privvar.phb');
?>
--FILE--
<?php
include("a56-class_privvar.phb");
unlink(dirname(__FILE__).'/a56-class_privvar.phb');
unlink(dirname(__FILE__)."/a50-class.phb");
exit;
--CODE--
include(dirname(__FILE__)."/a50-class.phb");

$obj = new SubClass();
$obj->show();              /* a b c */
$obj->fail2(10);
echo "c=", $obj->c, "\n";
$obj->show();
--EXPECT--
a=[a] b=[b] c=[c]
c=10
a=[a] b=[b] c=[c]
--CLEAN--
<?php
unlink(dirname(__FILE__).'/a56-class_privvar.phb');
unlink(dirname(__FILE__)."/a50-class.phb");
?>
