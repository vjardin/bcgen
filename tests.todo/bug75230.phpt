--TEST--
Bug #75230 (Invalid opcode 49/1/8 using opcache)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function f() {
	  $retval = false;
	    if ($retval) { }
}
f();
exit("OK");
?>
--EXPECT--
OK
