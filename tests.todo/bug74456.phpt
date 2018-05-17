--TEST--
Bug #74456 (Segmentation error while running a script in CLI mode)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php


function small_numbers() {
	    return [0,1,2];
}

list ($zero, $one, $two) = small_numbers();

var_dump($zero, $one, $two);
?>
--EXPECT--
int(0)
int(1)
int(2)
