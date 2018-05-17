--TEST--
Test object iteration
--SKIPIF--
<?php
  if (version_compare(PHP_VERSION, '5.0.0', '<')) die("skip PHP 5 or later is required");
  $dir = dirname(__FILE__).'/';
  require($dir.'test-helper.php');
  make_bytecode($dir.'a38-iteration.phpt', $dir.'a38-iteration.phb');
?>
--FILE--
<?php
include("a38-iteration.phb");
unlink(dirname(__FILE__).'/a38-iteration.phb');
exit;
--CODE--
class test implements Iterator {
  private $cur = 'a';
  public $a, $b, $c;

  function test($arr) {
    $this->a = $arr[0]; $this->b = $arr[1]; $this->c = $arr[2];
  }

  public function rewind() { $this->cur = 'a'; }
  public function current() { return $this->cur ? $this->{$this->cur} : false; }
  public function key() { return $this->cur; }
  public function next() {
    if ($this->cur == 'c') $this->cur = false;
    else $this->cur = chr( ord($this->cur) + 1 );
    return $this->current();
  }
  public function valid() { return $this->cur !== false; }
}

class MyCollection implements IteratorAggregate {
    private $items = array();
    private $count = 0;

    // Required definition of interface IteratorAggregate
    public function getIterator() {
        return new test($this->items);
    }

    public function add($value) {
        $this->items[$this->count++] = $value;
    }
}

$obj = new test( array('A dog', 'Battle', 'Clean') );
foreach ($obj as $k=>$v) echo "$k: $v\n";

$coll = new MyCollection();
$coll->add('value 1');
$coll->add('value 2');
$coll->add('value 3');

foreach ($coll as $key => $val) {
    echo "key/value: [$key -> $val]\n";
}
--EXPECT--
a: A dog
b: Battle
c: Clean
key/value: [a -> value 1]
key/value: [b -> value 2]
key/value: [c -> value 3]
