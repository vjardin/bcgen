--TEST--
Bug #75370 (Webserver hangs on valid PHP text)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function test()
{
	$success = true;
	$success = $success AND true;
	return $success;
}

var_dump(test());
?>
--EXPECT--
bool(true)
