--TEST--
Test subnamespace
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.3.0', '<')) die("skip PHP 5.3 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a60-namespace.phpt', $dir.'a60-namespace.phb');
  make_bytecode($dir.'a61-subnamespace.phpt', $dir.'a61-subnamespace.phb');
?>
--FILE--
<?php
include("a61-subnamespace.phb");
unlink(dirname(__FILE__).'/a60-namespace.phb');
unlink(dirname(__FILE__).'/a61-subnamespace.phb');
exit;
--CODE--
namespace Bar {

include("a60-namespace.phb");

const FOO = "sssa";
function foo($v) { echo "[2] foo($v) at ".__NAMESPACE__."\n"; }
class Foo {
  function __construct() { echo "[2] a Foo object at ".__NAMESPACE__."\n"; }
}

function strlen($s) {
  $i = 0;
  while ($s{$i}) { if ($s{++$i} == ".") return $i; }
  return $i;
}

error_reporting(E_ALL ^ E_NOTICE);
foo(FOO);
$obj2 = new Foo();

sub\foo(FOO);
foo(sub\FOO);

echo strlen(FOO), "\n";
echo strlen("This is a test.ext"), "\n";
echo \strlen("This is a test.ext"), "\n";

}
--EXPECT--
[1] foo(1) at Bar\sub
[1] a Foo object at Bar\sub
[2] foo(sssa) at Bar
[2] a Foo object at Bar
[1] foo(sssa) at Bar\sub
[2] foo(1) at Bar
4
14
18
