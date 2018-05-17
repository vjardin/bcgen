--TEST--
Test class basics
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a30-class.phpt', $dir.'a30-class.phb');
?>
--FILE--
<?php
include("a30-class.phb");
unlink(dirname(__FILE__).'/a30-class.phb');
exit;
--CODE--
class A {
    var $x;
    function A($v) {
        $this->x = $v;
    }
    function foo() {
        if (isset($this)) {
            echo '$this is defined (', get_class($this), ")\n";
        } else {
            echo "\$this is not defined.\n";
        }
    }
}

class B {
    var $x;
    function B($v) {
        $this->x = $v;
    }
    function bar() {
        A::foo();
    }
}

if (PHP_VERSION >= 5) error_reporting(E_ALL & !E_STRICT);

$a = new A('x');
$b = new B(5.5);
echo $a->x, "\n";
var_dump($b->{$a->x});

$a->foo();
A::foo();
$b->bar();
B::bar();
?>
--EXPECTREGEX--
x
float\(5\.5\)
\$this is defined \([Aa]\)
\$this is not defined\.
\$this is defined \([Bb]\)
\$this is not defined\.
