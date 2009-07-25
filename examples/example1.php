Testing stuff
<?php
flush();
// example usage of Services_Exceptional
include dirname(__FILE__) . '/../Services/Exceptional.php';

$api_key = 'XXX'; // please change

$exceptional = new Services_Exceptional($api_key);

class Foo
{
    public function bar()
    {
        throw new Exception("This is pretty neat!");
    }
}

$f = new Foo;
$f->bar();
?>

Done
