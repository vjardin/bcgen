--TEST--
Bug #76275: Assertion failure in file cache when unserializing empty try_catch_array
--INI--
bcgen.enabled=1
--FILE--
<?php

if (PHP_VERSION_ID >= 70000) {
    echo "Done";
    return;
}

if (!is_callable('random_bytes')) {
    try {
    } catch (com_exception $e) {
    }

    function random_bytes($length)
    {
        throw new Exception(
            'There is no suitable CSPRNG installed on your system'
        );
        return '';
    }
}
--EXPECT--
Done
