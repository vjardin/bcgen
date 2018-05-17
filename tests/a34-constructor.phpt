--TEST--
Test class constructor and destructor
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a34-constructor.phpt', $dir.'a34-constructor.phb');
?>
--FILE--
<?php
include("a34-constructor.phb");
unlink(dirname(__FILE__).'/a34-constructor.phb');
exit;
--CODE--
class BaseClass {
  var $v;
  function __construct() {
    $this->v = "Base";
    echo "Base::__ctor\n";
  }
  function get() { return $this->v; }
}
class SubClass extends BaseClass {
  function __construct($v) {
    parent::__construct();
    $this->v .= "_".$v;
    echo "Sub::__ctor\n";
  }
  function __destruct() {
    echo "Sub::__dtor\n";
  }
}

function main() {
  $a = new SubClass('ext');
  echo "Got ", $a->get(), "\n";
}

main();
--EXPECT--
Base::__ctor
Sub::__ctor
Got Base_ext
Sub::__dtor
