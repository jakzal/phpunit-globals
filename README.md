# PHPUnit Globals

Allows to use attributes to define global variables in PHPUnit test cases.

[![Build](https://github.com/jakzal/phpunit-globals/actions/workflows/build.yml/badge.svg)](https://github.com/jakzal/phpunit-globals/actions/workflows/build.yml)

Supported attributes:
 * `#[Env]` for `$_ENV`
 * `#[Server]` for `$_SERVER`
 * `#[Putenv]` for [`putenv()`](http://php.net/putenv)

Supported annotations:

 * `@env` and `@unset-env` for `$_ENV`
 * `@server` and `@unset-server` for `$_SERVER`
 * `@putenv` and `@unset-getenv` for [`putenv()`](http://php.net/putenv)

> Annotations are deprecated since v3.1.0 and will be removed in v3.4.0.
> The latest annotation support is planned to be removed is when PHPUnit 12 is released.
> Annotation support is complete, so if you plan on using them keep using v3.3 of this package.

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
Remember to instruct PHPUnit to load extensions in your `phpunit.xml` using the `extensionsDirectory` attribute:

```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="file://./vendor/phpunit/phpunit/phpunit.xsd"
         extensionsDirectory="tools/phpunit.d"
>
</phpunit>
```

## Usage

Enable the globals attribute extension in your PHPUnit configuration:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit ...>

    <!-- ... -->

    <extensions>
        <bootstrap class="Zalas\PHPUnit\Globals\AttributeExtension" />
    </extensions>
</phpunit>
```

> If you are using a version before PHP 8.1 you can use the `AnnotationExtension` instead.

Make sure the `AttributeExtension` is **registered before** any other extensions that might depend on global variables.

Global variables can now be defined in attributes:

```php
use PHPUnit\Framework\TestCase;
use Zalas\PHPUnit\Globals\Attribute\Env;
use Zalas\PHPUnit\Globals\Attribute\Server;
use Zalas\PHPUnit\Globals\Attribute\Putenv;

 #[Env('FOO', 'bar')]
class ExampleTest extends TestCase
{
    #[Env('APP_ENV', 'foo')]
    #[Env('APP_DEBUG', '0')]
    #[Server('APP_ENV', 'bar')]
    #[Server('APP_DEBUG', '1')]
    #[Putenv('APP_HOST', 'localhost')]
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

It's also possible to mark an attribute with _unset_ so it will not be present in any of the global variables:

```php
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    #[Env('APP_ENV', unset: true)]
    #[Server('APP_DEBUG', unset: true)]
    #[Putenv('APP_HOST', unset: true)]
    public function test_global_variables()
    {
        $this->assertArrayNotHasKey('APP_ENV', $_ENV);
        $this->assertArrayNotHasKey('APP_DEBUG', $_SERVER);
        $this->assertArrayNotHasKey('APP_HOST', \getenv());
    }
}
```
## Updating to PHPUnit 10

When updating from a previous version of this extension dedicated to work with PHPUnit 9,
replace the extension registration in `phpunit.xml`:

```xml
    <extensions>
        <extension class="Zalas\PHPUnit\Globals\AttributeExtension" />
    </extensions>
```

with:

```xml
    <extensions>
        <bootstrap class="Zalas\PHPUnit\Globals\AttributeExtension" />
    </extensions>
```

## Contributing

Please read the [Contributing guide](CONTRIBUTING.md) to learn about contributing to this project.
Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md).
By participating in this project you agree to abide by its terms.
