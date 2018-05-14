--TEST--
ISSUE #128 (opcache_invalidate segmentation fault)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
var_dump(opcache_invalidate('1'));
var_dump("okey");
?>
--EXPECT--
bool(false)
string(4) "okey"
