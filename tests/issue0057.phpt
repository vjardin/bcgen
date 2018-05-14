--TEST--
ISSUE #57 (segfaults in drupal7)
--INI--
bcgen.enable=1
bcgen.optimization_level=-1
--SKIPIF--
<?php require_once('skipif.inc'); ?>
--FILE--
<?php

class ZException extends Exception {
}

function dummy($query) {
    try {
        switch ($query) {
            case 1;
            break;
            case 2;
            break;
        default:
            throw new Exception('exception');
        }
    } catch (ZException $e) {
        return NULL;
    }
}

try {
    dummy(0);
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
--EXPECT--
exception
