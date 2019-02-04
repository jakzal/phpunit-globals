<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use PHPUnit\Util\Test;

class AnnotationExtension implements BeforeTestHook, AfterTestHook
{
    private $server;
    private $env;
    private $getenv;

    public function executeBeforeTest(string $test): void
    {
        $this->backupGlobals();
        $this->readGlobalAnnotations($test);
    }

    public function executeAfterTest(string $test, float $time): void
    {
        $this->restoreGlobals();
    }

    private function backupGlobals(): void
    {
        $this->server = $_SERVER;
        $this->env = $_ENV;
        $this->getenv = \getenv();
    }

    private function restoreGlobals(): void
    {
        $_SERVER = $this->server;
        $_ENV = $this->env;

        foreach (\array_diff_assoc($this->getenv, \getenv()) as $name => $value) {
            \putenv(\sprintf('%s=%s', $name, $value));
        }
        foreach (\array_diff_assoc(\getenv(), $this->getenv) as $name => $value) {
            \putenv($name);
        }
    }

    private function readGlobalAnnotations(string $test)
    {
        $globalVars = $this->parseGlobalAnnotations($test);

        foreach ($globalVars['env'] as $name => $value) {
            $_ENV[$name] = $value;
        }
        foreach ($globalVars['server'] as $name => $value) {
            $_SERVER[$name] = $value;
        }
        foreach ($globalVars['putenv'] as $name => $value) {
            \putenv(\sprintf('%s=%s', $name, $value));
        }
    }

    private function parseGlobalAnnotations(string $test): array
    {
        return \array_map(function (array $annotations) {
            return \array_reduce($annotations, function ($carry, $annotation) {
                list($name, $value) = \strpos($annotation, '=') ? \explode('=', $annotation, 2) : [$annotation, ''];
                $carry[$name] = $value;

                return $carry;
            }, []);
        }, $this->findGlobalVarAnnotations($test));
    }

    private function findGlobalVarAnnotations(string $test): array
    {
        $annotations = $this->parseTestMethodAnnotations($test);

        return \array_filter(
            \array_merge_recursive(['env' => [], 'server' => [], 'putenv' => []], $annotations['class'], $annotations['method']),
            function (string $annotationName) {
                return \in_array($annotationName, ['env', 'server', 'putenv']);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function parseTestMethodAnnotations(string $test): array
    {
        // @see PHPUnit\Framework\TestCase::getAnnotations
        return Test::parseTestMethodAnnotations(...\explode('::', $test));
    }
}
