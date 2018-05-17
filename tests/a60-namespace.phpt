--TEST--
Test namespace basics
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.3.0', '<')) die("skip PHP 5.3 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a60-namespace.phpt', $dir.'a60-namespace.phb');
?>
--FILE--
<?php
include("a60-namespace.phb");
unlink(dirname(__FILE__).'/a60-namespace.phb');
exit;
--CODE--
namespace Bar\sub {

const FOO = 1;
function foo($v) { echo "[1] foo($v) at ".__NAMESPACE__."\n"; }
class Foo {
  function __construct() { echo "[1] a Foo object at ".__NAMESPACE__."\n"; }
}

foo(FOO);
$obj = new Foo();

}
--EXPECT--
[1] foo(1) at Bar\sub
[1] a Foo object at Bar\sub
