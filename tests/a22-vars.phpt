--TEST--
Test variables
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a22-vars.phpt', $dir.'a22-vars.phb');
?>
--FILE--
<?php
include("a22-vars.phb");
unlink(dirname(__FILE__).'/a22-vars.phb');
exit;
//--CODE--
  $a = 2;
  $b = $a * $a;
  $c = ++$a;
  $d = $c * $a / 5;
  $s = "A string\n2nd line";
  $arr = array($a, $b, $d);
  $x = 'c';
  $$x *= 10;
  var_dump($c);
  var_dump($d);
  echo "$s\n";
  var_dump($arr);
  $arr2[ $s{0} ] = &$a;
  $a = ($c % 10) ? "yes" : "no";
  echo $arr2['A'], "\n";
  $z = "_SELF";
  echo $_SERVER["PHP{$z}"], "\n";
?>
--EXPECTREGEX--
int\(30\)
float\(1\.8\)
A string
2nd line
array\(3\) \{
  \[0\]=>
  int\(3\)
  \[1\]=>
  int\(4\)
  \[2\]=>
  float\(1\.8\)
\}
no
.*a22-vars\.php$
