--TEST--
SCCP 021: Memleak
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
;bcgen.opt_debug_level=0x20000
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
class A {
	public function memleak($num_tokens) {
		$queries = array();
		for ( $i = 0; $i < $num_tokens; ++$i ) {
			if ( 0 < $i )
				$queries[$i] = $queries[$i - 1] . '&';
			else
				$queries[$i] = '';

			$queries[$i] .= $query_token;
		}

		return; 
	}
}
?>
okey
--EXPECT--
okey
