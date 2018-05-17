--FILE--

--CODE--
class Base {
  var $v;
  function Base($v = 1) { $this->v = $v; }
  function display() { echo $this->v, "\n"; }
}
?>
--EXPECT--
