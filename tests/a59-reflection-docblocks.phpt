--TEST--
Test for reflection's getDocComment().
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a59-reflection-docblocks.phpt', $dir.'a59-reflection-docblocks.phb');
?>
--FILE--
<?php
include("a59-reflection-docblocks.phb");
unlink(dirname(__FILE__).'/a59-reflection-docblocks.phb');
exit;
--CODE--
/**
 * CLASS DOCBLOCK
 */
class ReflectionTest
{
	/**
	 * CTOR DOCBLOCK
	 */
	function __construct()
	{
	}

	/**
	 * METHOD1 DOCBLOCK
	 */
	function method1()
	{
	}

	/**
	 * METHOD2 DOCBLOCK
	 */
	private function method2()
	{
	}

	/**
	 * METHOD3 DOCBLOCK
	 */
	protected function method3()
	{
	}
}

$r = new ReflectionClass('ReflectionTest');
echo $r->getDocComment(), "\n";
$m = $r->getMethod('__construct');
echo $m->getDocComment(), "\n";
$m = $r->getMethod('method1');
echo $m->getDocComment(), "\n";
$m = $r->getMethod('method2');
echo $m->getDocComment(), "\n";
$m = $r->getMethod('method3');
echo $m->getDocComment(), "\n";
echo "OK\n";
?>
--EXPECT--
/**
 * CLASS DOCBLOCK
 */
/**
	 * CTOR DOCBLOCK
	 */
/**
	 * METHOD1 DOCBLOCK
	 */
/**
	 * METHOD2 DOCBLOCK
	 */
/**
	 * METHOD3 DOCBLOCK
	 */
OK
