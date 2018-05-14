--TEST--
Bug #74663 (Segfault with opcache.memory_protect and validate_timestamp)
--INI--
bcgen.enable=1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
$file = __DIR__ . "/bug74663.inc";
file_put_contents($file, "");
include $file;

var_dump(is_file($file));
?>
--CLEAN--
<?php
unlink(__DIR__ . "/bug74663.inc");
--EXPECT--
bool(true)
