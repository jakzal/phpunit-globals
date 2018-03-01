# PHPUnit Globals

Allows to use annotations to define global variables in PHPUnit test cases.

[![Build Status](https://travis-ci.org/jakzal/phpunit-globals.svg?branch=master)](https://travis-ci.org/jakzal/phpunit-globals)

Supported annotations:

 * `@env` for `$_ENV`
 * `@server` for `$_SERVER` 

Global variables are set before each test case is executed,
and brought to the original state after each test case has finished.

## Installation

```bash
composer require --dev zalas/phpunit-globals
```

## Usage

Enable the globals listener in your PHPUnit configuration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/7.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         verbose="true"
         colors="true">

    <!-- ... -->

    <listeners>
        <listener class="Zalas\PHPUnit\Globals\AnnotationListener" />
    </listeners>

</phpunit>
```

Global variables can now be defined in annotations:

```php
use PHPUnit\Framework\TestCase;

/**
 * @env FOO=bar
 */
class ExampleTest extends TestCase
{
    /**
     * @env APP_ENV=foo
     * @env APP_DEBUG=0
     * @server APP_ENV=bar
     * @server APP_DEBUG=1
     */
    public function test_global_variables()
    {
        $this->assertSame('bar', $_ENV['FOO']);
        $this->assertSame('foo', $_ENV['APP_ENV']);
        $this->assertSame('0', $_ENV['APP_DEBUG']);
        $this->assertSame('bar', $_SERVER['APP_ENV']);
        $this->assertSame('1', $_SERVER['APP_DEBUG']);
    }
}
```
