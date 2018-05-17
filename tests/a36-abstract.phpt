--TEST--
Test abstract classes and methods
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a36-abstract.phpt', $dir.'a36-abstract.phb');
?>
--FILE--
<?php
include("a36-abstract.phb");
unlink(dirname(__FILE__).'/a36-abstract.phb');
exit;
--CODE--
error_reporting(E_ALL ^ E_NOTICE);

abstract class AClass {
  protected $c;
  abstract protected function set($c);
  abstract function prefix($s);
  public function show() { echo $this->prefix.$this->c."\n"; }
}

abstract class DoubleClass extends AClass {
  protected function set($c) { $this->c = 2*$c; }
}

class SampleClass extends DoubleClass {
  function prefix($s) { $this->prefix = $s; }
  function __construct($a, $b) {
    $this->set($a + $b); 
    $this->prefix("Sample:");
  }
}

$obj = new SampleClass(1, 2);
$obj->show();
--EXPECT--
Sample:6
