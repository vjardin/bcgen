--TEST--
Test private properties
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a50-class.phps', $dir.'a50-class.phb');
  make_bytecode($dir.'a52-class_privvar.phpt', $dir.'a52-class_privvar.phb');
?>
--FILE--
<?php
include("a52-class_privvar.phb");
unlink(dirname(__FILE__).'/a52-class_privvar.phb');
unlink(dirname(__FILE__)."/a50-class.phb");
exit;
--CODE--
include(dirname(__FILE__)."/a50-class.phb");

$obj = new SubClass();
$obj->show();              /* a b c */
$obj->c = 10;              /* undef */
$obj->show();
--EXPECT--
a=[a] b=[b] c=[c]
a=[a] b=[b] c=[c]
--CLEAN--
<?php
unlink(dirname(__FILE__).'/a52-class_privvar.phb');
unlink(dirname(__FILE__)."/a50-class.phb");
?>
