--TEST--
Bug #74596 (SIGSEGV with opcache.revalidate_path enabled)
--INI--
bcgen.enable=1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php

file_put_contents(__DIR__ . "/bug74596_1.php", <<<CODE
<?php
class A {
	public function __construct() {
		\$a = true;
		if (\$a) {
			echo 1 + 2;
		} else {
			echo 2 + 3;
		}
	}
}
?>
CODE
);

file_put_contents(__DIR__ . "/bug74596_2.php", "ok\n");

class ufilter extends php_user_filter
{
	function filter($in, $out, &$consumed, $closing)
	{
		include_once __DIR__ . "/bug74596_1.php";
		while ($bucket = stream_bucket_make_writeable($in)) {
			stream_bucket_append($out, $bucket);
		}
		return PSFS_PASS_ON;
	}
}

stream_filter_register("ufilter", "ufilter");

include "php://filter/read=ufilter/resource=" . __DIR__ . "/bug74596_2.php";
?>
--CLEAN--
<?php
unlink(__DIR__ . "/bug74596_1.php");
unlink(__DIR__ . "/bug74596_2.php");
?>
--EXPECT--
ok
