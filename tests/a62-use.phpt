--TEST--
Test use keyword
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.3.0', '<')) die("skip PHP 5.3 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a60-namespace.phpt', $dir.'a60-namespace.phb');
  make_bytecode($dir.'a62-use.phpt', $dir.'a62-use.phb');
?>
--FILE--
<?php
include("a62-use.phb");
unlink(dirname(__FILE__).'/a60-namespace.phb');
unlink(dirname(__FILE__).'/a62-use.phb');
exit;
--CODE--
namespace s {

include("a60-namespace.phb");

use Bar\sub\Foo as Another, Bar\sub;

function foo($v) { echo "[2] foo($v) at ".__NAMESPACE__."\n"; }
class Foo {
  function __construct() { echo "[2] a Foo object at ".__NAMESPACE__."\n"; }
}

//error_reporting(E_ALL ^ E_NOTICE);
foo(__NAMESPACE__);
$obj2 = new Foo();
$obj3 = new Another();
sub\foo(__NAMESPACE__);

}
--EXPECT--
[1] foo(1) at Bar\sub
[1] a Foo object at Bar\sub
[2] foo(s) at s
[2] a Foo object at s
[1] a Foo object at Bar\sub
[1] foo(s) at Bar\sub
