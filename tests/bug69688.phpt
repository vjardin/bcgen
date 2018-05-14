--TEST--
Bug #69688 (segfault with eval and opcache fast shutdown)
--INI--
bcgen.enable=1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
eval('function g() {} function g2() {} function g3() {}');

eval('class A{} class B{} class C{}');

?>
okey
--EXPECT--
okey
