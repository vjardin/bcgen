--TEST--
Test for reflection's getFileName().
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a58-reflection-file.phpt', $dir.'a58-reflection-file.phb');
?>
--FILE--
<?php
include("a58-reflection-file.phb");
unlink(dirname(__FILE__).'/a58-reflection-file.phb');
exit;
--CODE--
class ReflectionFileTest
{
}
$r = new ReflectionClass('ReflectionFileTest');
$r->getFileName();
echo "OK\n";
?>
--EXPECT--
OK
