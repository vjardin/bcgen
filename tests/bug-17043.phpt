--TEST--
Bug #17043 - Crash on call of methods inherited from DateTime
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.2', '<')) die("skip PHP 5.2 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'bug-17043.phpt', $dir.'bug-17043.phb');
?>
--FILE--
<?php
error_reporting(E_ALL & !E_STRICT);
@date_default_timezone_set(@date_default_timezone_get());

include("bug-17043.phb");

$o = new MyClass();
$x2 = $o->getOffset();

echo (0 + $x1 - $x2), "\n";

unlink(dirname(__FILE__).'/bug-17043.phb');
exit;
--CODE--
class MyClass extends DateTime {
  function getOffset() {
    echo "MyClass::getOffset()\n";
    return parent::getOffset();
  }
}
$d = new DateTime();
$x1 = $d->getOffset();
--EXPECT--
MyClass::getOffset()
0
