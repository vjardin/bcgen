--TEST--
Test calling private methods from a subclass
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a50-class.phps', $dir.'a50-class.phb');
  make_bytecode($dir.'a55-class_privfunc.phpt', $dir.'a55-class_privfunc.phb');
?>
--FILE--
<?php
include("a55-class_privfunc.phb");
unlink(dirname(__FILE__).'/a55-class_privfunc.phb');
unlink(dirname(__FILE__)."/a50-class.phb");
exit;
--CODE--
include(dirname(__FILE__)."/a50-class.phb");

$obj = new SubClass();
$obj->show();              /* a b c */
$obj->fail1(10);
$obj->show();
--EXPECTREGEX--
a=\[a\] b=\[b\] c=\[c\]

Fatal error: Call to private method BaseClass::set\(\) from context 'SubClass' .* on line 17
--CLEAN--
<?php
unlink(dirname(__FILE__).'/a55-class_privfunc.phb');
unlink(dirname(__FILE__)."/a50-class.phb");
?>
