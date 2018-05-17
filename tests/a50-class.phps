--FILE--
--CODE--
class BaseClass {
  public $a = 'a';
  protected $b = 'b';
  private $c = 'c';

  public function show() {
    echo "a=[", $this->a, "] b=[", $this->b, "] c=[", $this->c, "]\n";
  }
  protected function set_b($b) { $this->set('b', $b); }
  protected function set_c($c) { $this->set('c', $c); }
  private function set($k, $v) { $this->{$k} = $v; }
}
class SubClass extends BaseClass {
  protected function hidden($a) { $this->a = $a; }
  public function fail1($v) {
    $this->set('c', $v);
  }
  public function fail2($v) {
    $this->c = $v;
  }
  public function work($b, $c) {
    $this->set_b($b); $this->set_c($c);
  }
}

--EXPECT--
