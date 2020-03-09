# php-retry

php retry class


### usage
```php
function hello(string $name)
{
    if (random_int(0, 9) != 1) {
        throw new Exception("exception");
    } else {
        echo "hello {$name}\n";
    }
}


$retry = new Retry(3, 3); //delay 3 seconds, try 3 times
$retry->setException([Exception::class]); //set listening exceptions
$retry->call('hello', "world"); //call hello function
```