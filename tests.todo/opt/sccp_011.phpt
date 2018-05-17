--TEST--
SCCP 011: Conditional Constant Propagation of non-escaping object properties
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
bcgen.opt_debug_level=0x20000
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function foo(int $x) {
	$o = new stdClass;
	if ($x) {
		$o->foo = 0;
		$o->bar = 1;
	} else {
		$o->foo = 0;
		$o->bar = 2;
	}
	echo $o->foo;
}
?>
--EXPECTF--
$_main: ; (lines=1, args=0, vars=0, tmps=0)
    ; (after optimizer)
    ; %ssccp_011.php:1-14
L0 (14):    RETURN int(1)

foo: ; (lines=3, args=1, vars=1, tmps=0)
    ; (after optimizer)
    ; %ssccp_011.php:2-12
L0 (2):     CV0($x) = RECV 1
L1 (11):    ECHO int(0)
L2 (12):    RETURN null
