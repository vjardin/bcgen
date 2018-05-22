--TEST--
Test protected methods
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a50-class.phps', $dir.'a50-class.phb');
  make_bytecode($dir.'a53-class_protfunc.phpt', $dir.'a53-class_protfunc.phb');
?>
--FILE--
<?php
include("a53-class_protfunc.phb");
unlink(dirname(__FILE__).'/a53-class_protfunc.phb');
unlink(dirname(__FILE__)."/a50-class.phb");
exit;
--CODE--
include(dirname(__FILE__)."/a50-class.phb");

$obj = new SubClass();
$obj->show();              /* a b c */
try{
$obj->hidden(15);          /* error */
$obj->show();
}
catch(Error $e) {
    echo "\nFatal error: ", $e->getMessage(), " on line ", $e->getLine(), "\n";
}
--EXPECTREGEX--
a=\[a\] b=\[b\] c=\[c\]

Fatal error: Call to protected method SubClass::hidden\(\) from context .* on line 7
--CLEAN--
<?php
unlink(dirname(__FILE__).'/a53-class_protfunc.phb');
unlink(dirname(__FILE__)."/a50-class.phb");
?>
