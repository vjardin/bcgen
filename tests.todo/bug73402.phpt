--TEST--
Bug #73402 (Opcache segfault when using class constant to call a method)
--INI--
bcgen.enable=1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
class Logger {
	public function info($msg) {
		echo $msg;
	}
}

class B
{
	const LOG_LEVEL = 'Info';
	public function test()
	{
		$logger = new \Logger();
		$logger->{self::LOG_LEVEL}('test');
	}
}

$b = new B;
$b->test();
--EXPECT--
test
