<?php

function _make_bytecode($sname, $tname) {
  $s = join('', file($sname));
  if (!preg_match("|--FILE--\r?\n.*//--CODE--\r?\n(.*)--EXPECT--|s", $s, $res)) echo "no\n";


  $f = fopen($tname.".in", "w");
  fwrite($f, "<?php\n".trim($res[1]));
  fclose($f);

  bcgen_compile_file($tname.".in", $tname);

  @unlink($tname.".in");
}

function make_phpcode($sname, $tname) {
  $s = join('', file($sname));
  preg_match("|--FILE--\r?\n.*?/?/?--CODE--\r?\n(.*)--EXPECT|s", $s, $res) or die("skip missing CODE section at $sname");

  $f = fopen($tname, "w");
  fwrite($f, "<?php\n".trim($res[1]));
  fclose($f);
}

function make_bytecode($sname, $tname) {
  check_bcgen();

  make_phpcode($sname, $tname.".in");

  bcgen_compile_file($tname.".in", $tname);

  @unlink($tname.".in");
}

function check_bcgen() {
  if (!extension_loaded("Zend BCgen")) die("skip Zend BCgen isn't loaded");
}

?>
