--TEST--
Test inheriting from ArrayObject.
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.2.0', '<')) die("skip PHP 5.2 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a57-inherit-array-object.phpt', $dir.'a57-inherit-array-object.phb');
?>
--FILE--
<?php
include("a57-inherit-array-object.phb");
unlink(dirname(__FILE__).'/a57-inherit-array-object.phb');
exit;
--CODE--
class ezcDocumentOdtPcssConverterManager extends ArrayObject
{
    public function __construct()
    {
        parent::__construct( array(), ArrayObject::STD_PROP_LIST );
        echo "__construct()\n";
    }
}
$a = new ezcDocumentOdtPcssConverterManager;
$a->append("foo");
print_r( $a->getArrayCopy() );
var_dump( $a instanceof ArrayAccess );
?>
--EXPECT--
__construct()
Array
(
    [0] => foo
)
bool(true)
