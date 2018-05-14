--TEST--
Bug #66334 (Memory Leak in new pass1_5.c optimizations)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
enable_dl=0
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
if (extension_loaded("unknown_extension")) {
	var_dump(1);
} else {
	var_dump(2);
}
--EXPECT--
int(2)
