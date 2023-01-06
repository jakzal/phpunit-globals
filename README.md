# PHPUnit Globals

Allows to use annotations to define global variables in PHPUnit test cases.

[![Build](https://github.com/jakzal/phpunit-globals/actions/workflows/build.yml/badge.svg)](https://github.com/jakzal/phpunit-globals/actions/workflows/build.yml)

Supported annotations:

 * `@env` for `$_ENV`
 * `@server` for `$_SERVER`
 * `@putenv` for [`putenv()`](http://php.net/putenv)

Global variables are set before each test case is executed,
and brought to the original state after each test case has finished.
The same applies to `putenv()`/`getenv()` calls.

## Installation

### Composer

```bash
composer require --dev zalas/phpunit-globals
```

### Phar

The extension is also distributed as a PHAR, which can be downloaded from the most recent
[Github Release](https://github.com/jakzal/phpunit-globals/releases).

Put the extension in your PHPUnit extensions directory.
Remember to instruct PHPUnit to load extensions in your `phpunit.xml`:

```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.0/phpunit.xsd"
         extensionsDirectory="tools/phpunit.d"
>
</phpunit>
```

## Usage

Enable the globals annotation extension in your PHPUnit configuration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         verbose="true"
         colors="true">

    <!-- ... -->

    <extensions>
        <extension class="Zalas\PHPUnit\Globals\AnnotationExtension" />
    </extensions>

</phpunit>
```

Make sure the `AnnotationExtension` is registered before any other extensions that might depend on global variables.

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
     * @putenv APP_HOST=localhost
     */
    public function test_global_variables()
    {
        $this->assertSame('bar', $_ENV['FOO']);
        $this->assertSame('foo', $_ENV['APP_ENV']);
        $this->assertSame('0', $_ENV['APP_DEBUG']);
        $this->assertSame('bar', $_SERVER['APP_ENV']);
        $this->assertSame('1', $_SERVER['APP_DEBUG']);
        $this->assertSame('localhost', \getenv('APP_HOST'));
    }
}
```

It's also possible to mark a variable as _unset_ so it will not be present in any of the global variables:

```php
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * @unset-env APP_ENV
     * @unset-server APP_DEBUG
     * @unset-getenv APP_HOST
     */
    public function test_global_variables()
    {
        $this->assertArrayNotHasKey('APP_ENV', $_ENV);
        $this->assertArrayNotHasKey('APP_DEBUG', $_SERVER);
        $this->assertArrayNotHasKey('APP_HOST', \getenv());
    }
}
```

## Updating to PHPUnit 8

When updating from a previous version of this extension that used to work with PHPUnit older than v8,
replace the listener registration in `phpunit.xml`:

```xml
    <listeners>
        <listener class="Zalas\PHPUnit\Globals\AnnotationListener" />
    </listeners>
```

with the extension registration:

```xml
    <extensions>
        <extension class="Zalas\PHPUnit\Globals\AnnotationExtension" />
    </extensions>
```

## Contributing

Please read the [Contributing guide](CONTRIBUTING.md) to learn about contributing to this project.
Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md).
By participating in this project you agree to abide by its terms.
