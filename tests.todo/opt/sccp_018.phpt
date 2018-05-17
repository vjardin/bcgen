--TEST--
SCCP 018: Object assignemnt
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
;bcgen.opt_debug_level=0x20000
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function foo() {
	$a = new stdClass;
	$b = $a;
	$a->x = 5;
	$b->x = 42;
	echo $a->x;
	echo "\n";
}
foo();
?>
--EXPECT--
42
