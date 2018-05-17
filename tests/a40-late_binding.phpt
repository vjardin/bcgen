--TEST--
Test late static binding
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.3', '<')) die("skip PHP 5.3 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a40-late_binding.phpt', $dir.'a40-late_binding.phb');
?>
--FILE--
<?php
include("a40-late_binding.phb");
unlink(dirname(__FILE__).'/a40-late_binding.phb');
exit;
--CODE--
class A {
    public static function who1() {
        echo __CLASS__, "\n";
    }
    public function who2() {
        echo __CLASS__, ", get_class=", get_class($this), "\n";
    }
    public static function test1_self() {
        self::who1();
    }
    public static function test1_static() {
        static::who1();
    }
    public function test2_self() {
        self::who2();
    }
    public function test2_static() {
        static::who2();
    }
}

class B extends A {
    public static function who1() {
        echo __CLASS__, "\n";
    }
    public function who2() {
        echo __CLASS__, ", get_class=", get_class($this), "\n";
    }
}

B::test1_self();
B::test1_static();
$obj = new B();
$obj->test2_self();
$obj->test2_static();
$obj->who2();
--EXPECT--
A
B
A, get_class=B
B, get_class=B
B, get_class=B
