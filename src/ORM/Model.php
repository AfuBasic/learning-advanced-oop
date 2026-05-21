<?php

declare(strict_types=1);

namespace App\ORM;

use App\Query\Builder;
use BadMethodCallException;

abstract class Model
{
    public function getTable(): string
    {
        return 'users';
    }

    public function newQuery(): Builder
    {
        return new Builder($this->getTable());
    }

    /**
     * Intercept missing static calls (e.g., User::where(...))
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        // Late Static Binding instantiates the dynamic child subclass (e.g., User)
        $instance = new static();

        // Forward the method call straight to a fresh instance of the query builder
        return $instance->newQuery()->{$method}(...$parameters);
    }

    /**
     * Intercept missing instance calls (e.g., $user->where(...))
     */
    public function __call(string $method, array $parameters): mixed
    {
        $builder = $this->newQuery();

        if (method_exists($builder, $method)) {
            return $builder->{$method}(...$parameters);
        }

        throw new BadMethodCallException(sprintf(
            "Method %s::%s does not exist on the model or query builder.",
            static::class,
            $method
        ));
    }
}
