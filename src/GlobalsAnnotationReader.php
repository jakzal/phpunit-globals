<?php declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Metadata\Annotation\Parser\Registry;

final class GlobalsAnnotationReader implements PreparationStartedSubscriber
{
    public function notify(PreparationStarted $event): void
    {
        $this->readGlobalAnnotations($event->test());
    }

    private function readGlobalAnnotations(TestMethod $method)
    {
        $globalVars = $this->parseGlobalAnnotations($method);

        foreach ($globalVars['env'] as $name => $value) {
            $_ENV[$name] = $value;
        }
        foreach ($globalVars['server'] as $name => $value) {
            $_SERVER[$name] = $value;
        }
        foreach ($globalVars['putenv'] as $name => $value) {
            \putenv(\sprintf('%s=%s', $name, $value));
        }

        $unsetVars = $this->findUnsetVarAnnotations($method);

        foreach ($unsetVars['unset-env'] as $name) {
            unset($_ENV[$name]);
        }
        foreach ($unsetVars['unset-server'] as $name) {
            unset($_SERVER[$name]);
        }
        foreach ($unsetVars['unset-getenv'] as $name) {
            \putenv($name);
        }
    }

    private function parseGlobalAnnotations(TestMethod $method): array
    {
        return \array_map(function (array $annotations) {
            return \array_reduce($annotations, function ($carry, $annotation) {
                list($name, $value) = \strpos($annotation, '=') ? \explode('=', $annotation, 2) : [$annotation, ''];
                $carry[$name] = $value;

                return $carry;
            }, []);
        }, $this->findSetVarAnnotations($method));
    }

    private function findSetVarAnnotations(TestMethod $method): array
    {
        $annotations = $this->parseTestMethodAnnotations($method);

        return \array_filter(
            \array_merge_recursive(
                ['env' => [], 'server' => [], 'putenv' => []],
                $annotations['class'] ?? [],
                $annotations['method'] ?? []
            ),
            function (string $annotationName) {
                return \in_array($annotationName, ['env', 'server', 'putenv']);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function findUnsetVarAnnotations(TestMethod $method): array
    {
        $annotations = $this->parseTestMethodAnnotations($method);

        return \array_filter(
            \array_merge_recursive(
                ['unset-env' => [], 'unset-server' => [], 'unset-getenv' => []],
                $annotations['class'] ?? [],
                $annotations['method'] ?? []
            ),
            function (string $annotationName) {
                return \in_array($annotationName, ['unset-env', 'unset-server', 'unset-getenv']);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    // https://github.com/sebastianbergmann/phpunit/blob/9.5/src/Util/Test.php
    private function parseTestMethodAnnotations(TestMethod $method): array
    {
        $registry = Registry::getInstance();
        $className = $method->className();
        $methodName = $method->methodName();

        if (null !== $methodName) {
            try {
                return [
                    'method' => $registry->forMethod($className, $methodName)->symbolAnnotations(),
                    'class'  => $registry->forClassName($className)->symbolAnnotations(),
                ];
            } catch (Exception $methodNotFound) {
                // ignored
            }
        }

        return [
            'method' => null,
            'class'  => $registry->forClassName($className)->symbolAnnotations(),
        ];
    }
}
