--TEST--
Bug #71443 (Segfault using built-in webserver with intl using symfony)
--INI--
bcgen.enable=1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
<?php if (substr(PHP_OS, 0, 3) == 'WIN') die('skip..  not for Windows'); ?>
--FILE--
<?php
ini_set("include_path", "/tmp");
?>
okey
--EXPECT--
okey
