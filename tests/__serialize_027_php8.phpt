--TEST--
__sleep() returning undefined declared properties
--SKIPIF--
<?php
if (!extension_loaded("igbinary")) print "skip";
if (PHP_VERSION_ID < 80000) die("skip error message different in php < 8");
?>
--FILE--
<?php

class Test {
    public $pub;
    protected $prot;
    private $priv;

    public function __construct() {
        unset($this->pub, $this->prot, $this->priv);
    }

    public function __sleep() {
        return ['pub', 'prot', 'priv'];
    }
}

echo "\nphp:";
var_dump(serialize(new Test));
echo "\nigbinary:";
var_dump(bin2hex(igbinary_serialize(new Test)));

?>
--EXPECTF--
php:
Warning: serialize(): "pub" returned as member variable from __sleep() but does not exist in %s on line %d

Warning: serialize(): "prot" returned as member variable from __sleep() but does not exist in %s on line %d

Warning: serialize(): "priv" returned as member variable from __sleep() but does not exist in %s on line %d
string(15) "O:4:"Test":0:{}"

igbinary:
Warning: igbinary_serialize(): "pub" returned as member variable from __sleep() but does not exist in %s on line %d

Warning: igbinary_serialize(): "prot" returned as member variable from __sleep() but does not exist in %s on line %d

Warning: igbinary_serialize(): "priv" returned as member variable from __sleep() but does not exist in %s on line %d
string(30) "000000021704546573741403000000"
