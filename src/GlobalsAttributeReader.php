<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use Zalas\PHPUnit\Globals\Attribute\Env;
use Zalas\PHPUnit\Globals\Attribute\Putenv;
use Zalas\PHPUnit\Globals\Attribute\Server;

final class GlobalsAttributeReader implements PreparationStartedSubscriber
{
    public function notify(PreparationStarted $event): void
    {
        $this->readGlobalAttributes($event->test());
    }

    private function readGlobalAttributes(TestMethod $method): void
    {
        $attributes = $this->parseTestMethodAttributes($method);
        $setVars = $this->findSetVarAttributes($attributes);

        foreach ($setVars as $var) {
            match (true) {
                $var instanceof Env => $_ENV[$var->name] = $var->value,
                $var instanceof Server => $_SERVER[$var->name] = $var->value,
                $var instanceof Putenv => \putenv(\sprintf('%s=%s', $var->name, $var->value))
            };
        }

        $unsetVars = $this->findUnsetVarAttributes($attributes);

        foreach ($unsetVars as $var) {
            $callable = match (true) {
                $var instanceof Env => static function ($var) {
                    unset($_ENV[$var->name]);
                },
                $var instanceof Server => static function ($var) {
                    unset($_SERVER[$var->name]);
                },
                $var instanceof Putenv => static function ($var) {
                    \putenv($var->name);
                }
            };

            $callable($var);
        }
    }

    /**
     * @param array<Env|Putenv|Server> $attributes
     */
    private function findSetVarAttributes(array $attributes): array
    {
        $attributes = \array_filter(
            $attributes,
            static fn (Env|Server|Putenv $attribute) => false === $attribute->unset,
            ARRAY_FILTER_USE_BOTH
        );

        \usort($attributes, static fn (Env|Server|Putenv $a, Env|Server|Putenv $b) => $a->getTarget() <=> $b->getTarget());

        return $attributes;
    }

    /**
     * @param array<Env|Putenv|Server> $attributes
     */
    private function findUnsetVarAttributes(array $attributes): array
    {
        $attributes = \array_filter(
            $attributes,
            static fn (Env|Server|Putenv $attribute) => true === $attribute->unset,
            ARRAY_FILTER_USE_BOTH
        );

        \usort($attributes, static fn (Env|Server|Putenv $a, Env|Server|Putenv $b) => $a->getTarget() <=> $b->getTarget());

        return $attributes;
    }

    /**
     * @return array<Env|Putenv|Server>
     */
    private function parseTestMethodAttributes(TestMethod $method): array
    {
        $className = $method->className();
        $methodName = $method->methodName();

        $methodAttributes = null;

        if (null !== $methodName) {
            $methodAttributes = $this->collectGlobalsFromAttributes(
                (new \ReflectionMethod($className, $methodName))->getAttributes()
            );
        }

        return \array_merge(
            $methodAttributes,
            $this->collectGlobalsFromAttributes((new \ReflectionClass($className))->getAttributes())
        );
    }

    /**
     * @param array<\ReflectionAttribute> $attributes
     * @return array<Env|Putenv|Server>
     */
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
