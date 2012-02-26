# Exceptional PHP (CI Edition)

The power of [Exceptional](http://getexceptional.com) for PHP using CodeIginiter Framework

## Super simple setup
Add the following 2 arrays in your CI's configuration file (application/config/config.php)

```php
$config['exceptional_api'] ="YOUR-API-KEY";
$config['exceptional_use_ssl'] = false;
```

You can turn off exception notifications by passing an empty string as the API key.  This is great for development.

```php
if (PHP_ENV == "production") {
	$config['exceptional_api'] ="YOUR-API-KEY";
}
else {
	$config['exceptional_api'] ="";
}
```

You can turn on SSL by setting the $config['exceptional_use_ssl'] to `true`.

```php
$config['exceptional_use_ssl'] = true;
```

You can auto-load the exceptional library by adding it in the autoload.php file (application/config/autoload.php)

```php
$autoload['libraries'] = array('exceptional');
```


## Filtering sensitive data

You can blacklist sensitive fields from being submitted to Exceptional:

```
$this->exceptional->addBlacklist(array('password', 'creditcardnumber'));
```

## Exceptions and errors

Exceptional PHP catches both errors and exceptions. You can control which errors are caught. If you want to ignore certain errors, use `error_reporting()`. Here's a common setting:

```php
error_reporting(E_ALL & ~E_NOTICE);  // ignore notices
```

Custom error and exception handlers are supported - see examples/advanced.php.

Fatal and parse errors are caught, too - as long the setup file parses correctly.

## 404 support

Add the following code to your 404 handler to track 404 errors:

```php
throw new Http404Error();
```

## Send extra data with your exceptions

```php
$context = array(
    "user_id" => 1
);
$this->exceptional->addContext($context);
```

See the [Exceptional documentation](http://docs.getexceptional.com/extras/context/) for more details.
