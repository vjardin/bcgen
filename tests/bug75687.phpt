--TEST--
Bug #75687 (var 8 (TMP) has array key type but not value type)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php

function x($y)
{
	if (is_array($y)) {
		$z = is_array($y) ? array() : array($y);
	}
}
?>
okey
--EXPECT--
okey
