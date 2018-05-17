--TEST--
Bug #75681: Warning: Narrowing occurred during type inference (specific case)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php

function foobar()
{
    do {
        foreach ($a as $i => $_) {
            $a[$i][0] += 1;
        }

        $a[] = array(0, 0);
    } while ($x !== false);
}

?>
===DONE===
--EXPECT--
===DONE===
