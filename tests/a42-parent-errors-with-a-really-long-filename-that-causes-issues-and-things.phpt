--TEST--
Test for parents with really long filenames.
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode(
     $dir.'a42-parent-errors-with-a-really-long-filename-that-causes-issues-and-things.phpt',
     $dir.'a42-parent-errors-with-a-really-long-filename-that-causes-issues-and-things.phb');
?>
--FILE--
<?php
function __autoload($name) {
  switch( $name ) {
    case 'ezcDocumentWikiDokuwikiTokenizerTests':
      require("a42-parent-errors-with-a-really-long-filename-that-causes-issues-and-things.phb");
      unlink(dirname(__FILE__) . "/a42-parent-errors-with-a-really-long-filename-that-causes-issues-and-things.phb");
      break;
    case 'ezcTestCase':
      require("a42-ezctestcase.php");
      break;
  }
}
$a = new ezcDocumentWikiDokuwikiTokenizerTests();
echo get_class($a), "\n";
exit;
--CODE--
/**
 * Test suite for class.
 * 
 * @package Document
 * @subpackage Tests
 */
class ezcDocumentWikiDokuwikiTokenizerTests extends ezcTestCase
{
    protected static $testDocuments = null;

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
?>
--EXPECT--
In constructor
ezcDocumentWikiDokuwikiTokenizerTests
