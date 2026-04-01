--TEST--
__serialize() mechanism (015): Uninitialized properties from __sleep should be ignored when serializing
--SKIPIF--
<?php
if (PHP_VERSION_ID < 70400) { echo "skip __serialize/__unserialize not supported in php < 7.4 for compatibility with igbinary_serialize()"; }
?>
--INI--
error_reporting=E_ALL & ~E_DEPRECATED
--FILE--
<?php
error_reporting(E_ALL);
set_error_handler(function ($errno, $message) {
    echo $message . "\n";
});
class OSI {
    public stdClass $o;
    public string $s;
    public ?int $i;
    public float $f;
    public function __sleep() {
        return ['o', 's', 'i'];
    }
}
class SimplePublic {
    public ?int $i;
    public function __sleep() {
        return ['i'];
    }
}
class SimpleProtected {
    protected ?int $i;
    public function __sleep() {
        return ['i'];
    }
    public function __set($name, $value) {
        $this->$name = $value;
    }
}
class SimplePrivate {
    private ?int $i;
    public function __sleep() {
        return ['i'];
    }
    public function __set($name, $value) {
        $this->$name = $value;
    }
}
// 00000002               -- header
// 17 03 4d79436c617373   -- object of type "MyClass"
//   14 03 000000           -- with 3 uninitialized properties
$m = new OSI();
function try_serialize_invalid($o) {
    try {
        var_dump(bin2hex($s = igbinary_serialize($o)));
    } catch (Error $e) {
        printf("Caught %s: %s\n", get_class($e), $e->getMessage());
    }
}

echo "PHP behavior:\n";
echo addslashes(serialize(new OSI())) . "\n";
echo addslashes(serialize(new SimplePublic())) . "\n";
echo addslashes(serialize(new SimpleProtected())) . "\n";
echo addslashes(serialize(new SimplePrivate())) . "\n";
$s = new SimplePublic();
$s->i = null;
echo addslashes(serialize($s)) . "\n";
$s = new SimpleProtected();
$s->i = 0;
echo addslashes(serialize($s)) . "\n";
$s = new SimplePrivate();
$s->i = null;
echo addslashes(serialize($s)) . "\n";

echo "Igbinary behavior:\n";
// These should not throw whether or not the uninitialized property is nullable.
try_serialize_invalid(new OSI());
try_serialize_invalid(new SimplePublic());
try_serialize_invalid(new SimpleProtected());
try_serialize_invalid(new SimplePrivate());
$s = new SimplePublic();
$s->i = null;
try_serialize_invalid($s);
$s = new SimpleProtected();
$s->i = 0;
try_serialize_invalid($s);
$s = new SimplePrivate();
$s->i = null;
try_serialize_invalid($s);

--EXPECT--
PHP behavior:
O:3:\"OSI\":0:{}
O:12:\"SimplePublic\":0:{}
O:15:\"SimpleProtected\":0:{}
O:13:\"SimplePrivate\":0:{}
O:12:\"SimplePublic\":1:{s:1:\"i\";N;}
O:15:\"SimpleProtected\":1:{s:4:\"\0*\0i\";i:0;}
O:13:\"SimplePrivate\":1:{s:16:\"\0SimplePrivate\0i\";N;}
Igbinary behavior:
string(28) "0000000217034f53491403000000"
string(42) "00000002170c53696d706c655075626c6963140100"
string(48) "00000002170f53696d706c6550726f746563746564140100"
string(44) "00000002170d53696d706c6550726976617465140100"
string(48) "00000002170c53696d706c655075626c6963140111016900"
string(62) "00000002170f53696d706c6550726f74656374656414011104002a00690600"
string(80) "00000002170d53696d706c6550726976617465140111100053696d706c6550726976617465006900"
