--TEST--
Test get_included_files()
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a15-included_files.phpt', $dir.'a15-included_files.phb');
?>
--FILE--
<?php
$i = 0;
include("a15-included_files.phb");
print_r( get_included_files() );
include_once("a15-included_files.phb");
unlink(dirname(__FILE__).'/a15-included_files.phb');
exit;
//--CODE--
echo $i++ ? "error\n" : " ok\n";
?>
--EXPECTREGEX--
ok
Array
\(
    \[0\] => \S+a15-included_files\.php
    \[1\] => \S+a15-included_files\.phb
\)
