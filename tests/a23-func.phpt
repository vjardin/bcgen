--TEST--
Test functions
--SKIPIF--
<?php
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a23-func.phpt', $dir.'a23-func.phb');
?>
--FILE--
<?php
include("a23-func.phb");
unlink(dirname(__FILE__).'/a23-func.phb');
exit;
--CODE--
function Add($a, $b) {
  function Div($x, $y = 100) { return $x / $y; }

  return $a + Div($b); 
}

function uc(&$string) {
  ucfirst($string);
  return array(7, 8, 1000);
}

function max2() {
  $m = NULL;
  for ($i = 0; $i < func_num_args(); $i++) {
    $v = func_get_arg($i);
    if (is_array($v)) $v = call_user_func_array(__FUNCTION__, $v);
    if ($v > $m) $m = $v;
  }
  return $m;
}

var_dump( Add(2, 78) );
$s = "hello world\n";
$arr = uc($s);
echo $s;
$f = "max2";
echo $f(2, 7, $s, False, $arr, "56", ord('z')-1), "\n";
var_dump( max2() );
?>
--EXPECT--
float(2.78)
hello world
1000
NULL
