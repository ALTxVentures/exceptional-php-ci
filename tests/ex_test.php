Testing stuff
<?php
flush();
// example usage of ExceptionalClient
include dirname(__FILE__) . '/../Services/Exceptional/Client.php';

$_exceptional = new Services_Exceptional_Client('f6f5bd040eefcf518ab2dccdad8386185c7a4395');

class Foo
{
    function bar()
    {
        throw new Exception("This is pretty neat!");
    }
}

$f = new Foo;
$f->bar();
?>

Done