--TEST--
Bug #65845 (Error when Zend BCgen Optimizer is fully enabled)
--INI--
bcgen.enable=1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
$Pile['vars'][(string)'toto'] = 'tutu';
var_dump($Pile['vars']['toto']);
?>
--EXPECT--
string(4) "tutu"
