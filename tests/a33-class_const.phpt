--TEST--
Test class constants
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a33-class_const.phpt', $dir.'a33-class_const.phb');
?>
--FILE--
<?php
include("a33-class_const.phb");
unlink(dirname(__FILE__).'/a33-class_const.phb');
exit;
--CODE--
class A {
  const C = "test value";
  var $v;
  function A($v) { $this->v = self::C."=$v"; }
}

echo A::C, "\n";
$a = new A(-2.5);
$a->v .= "000";
echo $a->v."\n";
?>
--EXPECT--
test value
test value=-2.5000
