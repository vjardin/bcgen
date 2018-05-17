--TEST--
Test extending internal classes
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.1', '<')) die("skip PHP 5.1 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a41-ext_internal.phpt', $dir.'a41-ext_internal.phb');
?>
--FILE--
<?php
error_reporting(E_ALL & !E_STRICT);
include("a41-ext_internal.phb");

$obj = new MyClass(dirname(__FILE__).'/a41-ext_internal.phpt');
echo count($obj), "\n", $obj->getSize(), "\n";

unlink(dirname(__FILE__).'/a41-ext_internal.phb');
exit;
--CODE--
class MyClass extends SplFileInfo implements Countable {
  public function getSize() {
    echo "MyClass::getSize()\n";
    return parent::getSize();
  }
  public function count() {
    echo "MyClass::count()\n";
    return 5;
  }
}

--EXPECT--
MyClass::count()
5
MyClass::getSize()
844
