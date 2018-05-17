--TEST--
Test goto
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.3', '<')) die("skip PHP 5.3 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a25-goto.phpt', $dir.'a25-goto.phb');
?>
--FILE--
<?php
include("a25-goto.phb");
unlink(dirname(__FILE__).'/a25-goto.phb');
exit;
//--CODE--
for ($i = 0; $i < 10; $i++) {
  echo "$i\n";
  if ($i == 3) goto Label;
}

Label:
echo ".\n";
?>
--EXPECT--
0
1
2
3
.
