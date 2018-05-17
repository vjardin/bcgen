--TEST--
Test class scope visibility
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a50-class.phps', $dir.'a50-class.phb');
  make_bytecode($dir.'a50-class_scope.phpt', $dir.'a50-class_scope.phb');
?>
--FILE--
<?php
include("a50-class_scope.phb");
unlink(dirname(__FILE__).'/a50-class_scope.phb');
unlink(dirname(__FILE__).'/a50-class.phb');
exit;
--CODE--
include(dirname(__FILE__)."/a50-class.phb");

$obj = new SubClass();
$obj->show();              /* a b c */
$obj->a = 25;
$obj->show();              /* 25 b c */
$obj->work(15, 10);
$obj->show();              /* 25 15 10 */
--EXPECT--
a=[a] b=[b] c=[c]
a=[25] b=[b] c=[c]
a=[25] b=[15] c=[10]
