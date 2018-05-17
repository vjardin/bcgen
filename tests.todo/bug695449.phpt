--TEST--
Bug #69549 (Memory leak with bcgen.optimization_level=0xFFFFFFFF)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
$a = array(true);
if($a[0] && false) {
  echo 'test';
}
echo "ok\n";
?>
--EXPECT--
ok
