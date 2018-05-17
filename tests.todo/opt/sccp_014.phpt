--TEST--
SCCP 014: Conditional Constant Propagation of non-escaping object properties on PHI
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function loadEntities($entity_information) {
	$entity_types = new StdClass();
	$entity_types->a = 1;
	foreach ($entity_information as $info) {
		$entity_types->a = 0;
	}
	var_dump((bool)($entity_types->a));
}

loadEntities(array("first", "second")); 
?>
--EXPECT--
bool(false)
