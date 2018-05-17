--TEST--
Bug #66474 (Optimizer bug in constant string to boolean conversion)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function foo() {
	$speed = 'slow' || 'fast';
}
foo();
echo "ok\n";
--EXPECT--
ok
