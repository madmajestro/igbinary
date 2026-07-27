--TEST--
Deeply nested input to igbinary_serialize() throws a catchable Error instead of crashing
--SKIPIF--
<?php
if (!extension_loaded("igbinary")) die("skip");
if (ini_get('zend.max_allowed_stack_size') === false) die("skip No stack limit support");
if (getenv('SKIP_ASAN')) die("skip ASAN needs different stack limit setting due to more stack space usage");
?>
--INI--
zend.max_allowed_stack_size=512K
--FILE--
<?php
class Node
{
    public $next;
}
$firstNode = new Node();
$node = $firstNode;
for ($i = 0; $i < 30000; $i++) {
    $newNode = new Node();
    $node->next = $newNode;
    $node = $newNode;
}

try {
    igbinary_serialize($firstNode);
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}

// Unlink iteratively so the engine does not recurse while destroying the chain.
while ($next = $firstNode->next) {
    $firstNode->next = $next->next;
}

?>
--EXPECT--
Maximum call stack size reached. Infinite recursion?
