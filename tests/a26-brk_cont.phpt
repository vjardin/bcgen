--TEST--
Test break & continue
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a26-brk_cont.phpt', $dir.'a26-brk_cont.phb');
?>
--FILE--
<?php
include("a26-brk_cont.phb");
unlink(dirname(__FILE__).'/a26-brk_cont.phb');
exit;
//--CODE--
for ($i = 5; $i > 0; $i--) {
  if ($i == 3) break;
  echo "$i\n";
  $j = -$i;
  while ($j) {
    if (++$j % 2) continue;
    echo "$i $j\n";
  }
}
?>
--EXPECT--
5
5 -4
5 -2
5 0
4
4 -2
4 0
