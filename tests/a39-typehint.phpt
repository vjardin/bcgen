--TEST--
Test type hinting
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.1.0', '<')) die("skip PHP 5.1 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a39-typehint.phpt', $dir.'a39-typehint.phb');
?>
--FILE--
<?php
include("a39-typehint.phb");
unlink(dirname(__FILE__).'/a39-typehint.phb');
exit;
--CODE--
error_reporting(E_ALL ^ E_NOTICE);

function handle($n, $s, $file, $line) {
  echo "error #$n: $s on line $line\n";
}

class CAPM {
  protected $rF, $rM;
  function __construct($rF, $rM) { $this->rF = $rF; $this->rM = $rM; }
  function load(array $arr) { $this->rF = $arr[0]; $this->rM = $arr[1]; }
  function r($beta) { return $this->rF + $beta * ($this->rM - $this->rF); }
}

function equity(CAPM $market, $beta, $premium = 0) {
  if (is_object($market))
    echo "Cost of equity = ", $market->r($beta) + $premium, "%\n";
}

set_error_handler('handle');
$m = new CAPM(4.7, 9.2);
equity($m, 1.08, 5);
try {
equity(5, 1.08);
}
catch(Error $e) {
    echo "Fatal error: ", $e->getMessage(),  " on line ", $e->getLine(), "\n";
}
try{
$m->load("string");
}
catch(Error $e) {
    echo "Fatal error: ", $e->getMessage(),  " on line ", $e->getLine(), "\n";
}
equity($m, 1.5);
$m->load( array(3.6, 7.1) );
equity($m, 1.5);

--EXPECTREGEX--
Cost of equity = 14\.56%
Fatal error\: Argument 1 passed to equity\(\) must be an instance of CAPM, integer given, called in .* on line 24 on line 15
Fatal error\: Argument 1 passed to CAPM\:\:load\(\) must be of the type array, string given, called in .* on line 30 on line 11
Cost of equity = 11\.45%
Cost of equity = 8\.85%
