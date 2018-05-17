--TEST--
Test class inheritance (several files) and autoload
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a32-class_extends.phpt', $dir.'a32-class_extends.phb');
  make_bytecode($dir.'a32-class_Base.phps', $dir.'a32-class_Base.phb');
?>
--FILE--
<?php
include("a32-class_extends.phb");
unlink(dirname(__FILE__).'/a32-class_extends.phb');
unlink(dirname(__FILE__).'/a32-class_Base.phb');
exit;
--CODE--
if (version_compare(PHP_VERSION, '5.0.0', '<')) {
  @include_once(dirname(__FILE__)."/a32-class_Base.phb");
}

function __autoload($name) {
  @include_once(dirname(__FILE__)."/a32-class_{$name}.phb");
}

class Child extends Base {
  function Child($v) {
    parent::Base($v);
    $this->v .= "_".__CLASS__;
  }
}

$obj = new Child(123);
$obj->display();
?>
--EXPECTREGEX--
123_[Cc]hild
