Testing stuff
<?php
flush();
// example usage of ExceptionalClient
include "ExceptionalClient.php";

$_exceptional = new ExceptionalClient("f6f5bd040eefcf518ab2dccdad8386185c7a4395");

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