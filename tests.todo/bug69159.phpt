--TEST--
Bug #69159 (BCgen causes problem when passing a variable variable to a function)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
$i = 1;
$x1 = "okey";
myFunction(${"x$i"});

function myFunction($x) {
	var_dump($x);
}

?>
--EXPECT--
string(4) "okey"
