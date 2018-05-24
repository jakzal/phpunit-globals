<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

class AnnotationListener implements TestListener
{
    use TestListenerDefaultImplementation;

    private $server;
    private $env;

    public function startTest(Test $test): void
    {
        $this->backupGlobals();

        if ($test instanceof TestCase) {
            $this->readGlobalAnnotations($test);
        }
    }

    public function endTest(Test $test, float $time): void
    {
        $this->restoreGlobals();
    }

    private function backupGlobals(): void
    {
        $this->server = $_SERVER;
        $this->env = $_ENV;
    }

    private function restoreGlobals(): void
    {
        $_SERVER = $this->server;
        $_ENV = $this->env;
    }

    private function readGlobalAnnotations(TestCase $test)
    {
        $globalVars = $this->parseGlobalAnnotations($test);

        foreach ($globalVars['env'] as $name => $value) {
            $_ENV[$name] = $value;
        }
        foreach ($globalVars['server'] as $name => $value) {
            $_SERVER[$name] = $value;
        }
    }

    private function parseGlobalAnnotations(TestCase $test): array
    {
        return \array_map(function (array $annotations) {
            return \array_reduce($annotations, function ($carry, $annotation) {
                list($name, $value) = \strpos($annotation, '=') ? \explode('=', $annotation, 2) : [$annotation, ''];
                $carry[$name] = $value;

                return $carry;
            }, []);
        }, $this->findGlobalVarAnnotations($test));
    }

    private function findGlobalVarAnnotations(TestCase $test): array
    {
        $annotations = $test->getAnnotations();

        return \array_filter(
            \array_merge_recursive(['env' => [], 'server' => []], $annotations['class'], $annotations['method']),
            function (string $annotationName) {
                return \in_array($annotationName, ['env', 'server']);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
