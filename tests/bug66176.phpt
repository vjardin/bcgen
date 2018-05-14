--TEST--
Bug #66176 (Invalid constant substitution)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function foo($v) {
	global $a;
	return $a[$v];
}
$a = array(PHP_VERSION => 1);
var_dump(foo(PHP_VERSION));
--EXPECT--
int(1)
