--TEST--
Bug #70207 Finally is broken with bcgen
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function bar() {
	return "bar";
}
function foo() {
    try { return bar(); }
    finally { @fclose(null); }
}

var_dump(foo());
?>
--EXPECT--
string(3) "bar"
