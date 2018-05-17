--TEST--
Test class inheritance (single file)
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a31-class_extends.phpt', $dir.'a31-class_extends.phb');
?>
--FILE--
<?php
include("a31-class_extends.phb");
unlink(dirname(__FILE__).'/a31-class_extends.phb');
exit;
--CODE--
class Base {
  var $v;
  function Base($v = 1) { $this->v = $v; }
  function display() { echo $this->v, "\n"; }
}
class Child extends Base {
  function Child($v) {
    parent::Base($v);
    $this->v .= 'ext';
  }
  function setX($x) { $this->x = $x; }
}

$obj = new Child(5);
$obj->display();
$obj->setX(-1.2);
var_dump($obj->x);
?>
--EXPECT--
5ext
float(-1.2)
