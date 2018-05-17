--TEST--
Phi sources remove 002
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php
function func($blogname, $user = '' ) {
	if (! is_object( $user ) || ( is_object($user) && ( $user->login != $blogname )) ) {
		test();
	}

	$result = array('user' => $user);

	return true;
}
?>
okey
--EXPECT--
okey
