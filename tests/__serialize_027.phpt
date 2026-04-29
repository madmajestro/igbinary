--TEST--
The virtual-property warning must contain the unmangled property name even when the original property was protected or private
--SKIPIF--
<?php if (PHP_VERSION_ID < 80400) { echo "skip virtual properties are not supported in php < 8.4"; } ?>
--FILE--
<?php
// Pre-generated igbinary bytes (PHP 8.5) for:
//     class Test { protected int $prop = 1; }
//     bin2hex(igbinary_serialize(new Test()));
// The mangled property key in the serialized data is "\0*\0prop".
$serialized_protected_igb = '0000000217045465737414011107002a0070726f700601';

// Same class name, but `prop` is now declared as a public *virtual* property.
// During hydration the fallback path must report the unmangled property name
// in the warning, otherwise printf truncates the embedded NUL of "\0*\0prop".
class Test {
    public int $prop {
        get => 1;
    }
}

$result = igbinary_unserialize(hex2bin($serialized_protected_igb));
var_dump($result);

?>
--EXPECTF--
Warning: igbinary_unserialize(): Cannot unserialize value for virtual property Test::$prop in %s on line %d
NULL
