--TEST--
SCCP 013: Conditional Constant Propagation of non-escaping array elements on PHI
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function loadEntities($entity_information) {
	$entity_types = [];
	foreach ($entity_information as $info) {
		$entity_types[$info] = 1;
	}
	var_dump((bool)($entity_types[$info]));
}

loadEntities(array("first", "second")); 
?>
--EXPECT--
bool(true)
