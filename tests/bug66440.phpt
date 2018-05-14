--TEST--
Bug #66440 (Optimisation of conditional JMPs based on pre-evaluate constant function calls)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
if(constant('PHP_BINARY')) {
	echo "OK\n";
}
--EXPECT--
OK
