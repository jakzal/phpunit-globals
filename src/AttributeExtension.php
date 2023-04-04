<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use Zalas\PHPUnit\Globals\Attribute\Env;
use Zalas\PHPUnit\Globals\Attribute\Putenv;
use Zalas\PHPUnit\Globals\Attribute\Server;

final class AttributeExtension implements BeforeTestHook, AfterTestHook
{
    private $server;
    private $env;
    private $getenv;

    public function executeBeforeTest(string $test): void
    {
        $this->backupGlobals();
        $this->readGlobalAttributes($test);
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

    private function readGlobalAttributes(string $test)
    {
        $globalVars = $this->parseGlobalAttributes($test);

        foreach ($globalVars['env'] as $name => $value) {
            $_ENV[$name] = $value;
        }
        foreach ($globalVars['server'] as $name => $value) {
            $_SERVER[$name] = $value;
        }
        foreach ($globalVars['putenv'] as $name => $value) {
            \putenv(\sprintf('%s=%s', $name, $value));
        }

        $unsetVars = $this->findUnsetVarAttributes($test);

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

    private function parseGlobalAttributes(string $test): array
    {
        $globals = ['env' => [], 'server' => [], 'putenv' => []];

        $attributes = $this->findSetVarAttributes($test);
        foreach ($attributes as $attribute) {
            match (true) {
                $attribute instanceof Env => $globals['env'][$attribute->name] = $attribute->value,
                $attribute instanceof Server => $globals['server'][$attribute->name] = $attribute->value,
                $attribute instanceof Putenv => $globals['putenv'][$attribute->name] = $attribute->value,
            };
        }

        return $globals;
    }

    private function findSetVarAttributes(string $test): array
    {
        $attributes = $this->parseTestMethodAttributes($test);

        $attributes = \array_filter(
            $attributes,
            static fn (Env|Server|Putenv $attribute) => false === $attribute->unset,
            ARRAY_FILTER_USE_BOTH
        );

        \usort($attributes, static fn (Env|Server|Putenv $a, Env|Server|Putenv $b) => $a->getTarget() <=> $b->getTarget());

        return $attributes;
    }

    private function findUnsetVarAttributes(string $test): array
    {
        $unset = ['unset-env' => [], 'unset-server' => [], 'unset-getenv' => []];

        $attributes = $this->parseTestMethodAttributes($test);
        foreach ($attributes as $attribute) {
            if (false === $attribute->unset) {
                continue;
            }

            match (true) {
                $attribute instanceof Env => $unset['unset-env'][] = $attribute->name,
                $attribute instanceof Server => $unset['unset-server'][] = $attribute->name,
                $attribute instanceof Putenv => $unset['unset-getenv'][] = $attribute->name,
            };
        }

        return $unset;
    }

    private function parseTestMethodAttributes(string $test): array
    {
        $parts = \preg_split('/ |::/', $test);

        if (!\class_exists($parts[0])) {
            return [];
        }

        $methodAttributes = [];
        if (!empty($parts[1])) {
            $methodAttributes = $this->collectGlobalsFromAttributes(
                (new \ReflectionMethod($parts[0], $parts[1]))->getAttributes()
            );
        }

        return \array_merge(
            $this->collectGlobalsFromAttributes((new \ReflectionClass($parts[0]))->getAttributes()),
            $methodAttributes,
        );
    }

    private function collectGlobalsFromAttributes(array $attributes): array
    {
        $globals = [];

        foreach ($attributes as $attribute) {
            if (!\str_starts_with($attribute->getName(), 'Zalas\\PHPUnit\\Globals\\Attribute\\')) {
                continue;
            }

            /** @var Env|Server|Putenv $instance */
            $instance = $attribute->newInstance();
            $globals[] = $instance->withTarget($attribute->getTarget());
        }

        return $globals;
    }
}
