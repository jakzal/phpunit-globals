<?php

declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use Exception;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Metadata\Annotation\Parser\Registry;

final class PrepareTestEnvironmentSubscriber implements PreparationStartedSubscriber
{
    public function __construct(
        private readonly Context $context,
    ) {
    }

    public function notify(PreparationStarted $event): void
    {
        $this->context->backupGlobals();

        $this->readGlobalAnnotations($event->test());
    }

    private function readGlobalAnnotations(Test $test): void
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

        $unsetVars = $this->findUnsetVarAnnotations($test);

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

    private function parseGlobalAnnotations(Test $test): array
    {
        return \array_map(function (array $annotations) {
            return \array_reduce($annotations, function ($carry, $annotation) {
                list($name, $value) = \strpos($annotation, '=') ? \explode('=', $annotation, 2) : [$annotation, ''];
                $carry[$name] = $value;

                return $carry;
            }, []);
        }, $this->findSetVarAnnotations($test));
    }

    private function findSetVarAnnotations(Test $test): array
    {
        $annotations = $this->parseTestMethodAnnotations($test);

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

    private function findUnsetVarAnnotations(Test $test): array
    {
        $annotations = $this->parseTestMethodAnnotations($test);

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

    private function parseTestMethodAnnotations(Test $test): array
    {
        $parts = \preg_split('/ |::/', $test->id());

        if (!\class_exists($parts[0])) {
            return [];
        }

        $className = $parts[0];
        $methodName = $parts[1] ?? null;

        $registry = Registry::getInstance();

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
