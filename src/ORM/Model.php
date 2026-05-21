<?php

declare(strict_types=1);

namespace App\ORM;

use App\Query\Builder;
use BadMethodCallException;
use ReflectionClass;

abstract class Model
{
    protected string $table = '';
    protected static array $tableCache = [];
    public function getTable(): string
    {
        if (!empty($this->table)) {
            return strtolower($this->table);
        }

        $className = static::class;
        if (isset(static::$tableCache[$className])) {
            return static::$tableCache[$className];
        }

        /**
         * Use reflection class to get the shortname of the class 
         * since the static returns the full class name including the namespace
         * @var mixed
         */
        $reflect = new ReflectionClass($this);
        $shortName = $reflect->getShortName();

        /**
         * use Regex to to convert to snake_case
         * @var mixed
         */
        $snakeName = preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName);
        /**
         * General pluralization
         * @var mixed
         */
        $tableName = strtolower($snakeName);

        if (preg_match('/([^aeiou])y$/', $tableName)) {
            // Category -> categories, Company -> companies (ends in consonant + y)
            $tableName = preg_replace('/y$/', 'ies', $tableName);
        } elseif (preg_match('/(ch|sh|x|ss)$/', $tableName)) {
            // Box -> boxes, Wish -> wishes, Match -> matches
            $tableName .= 'es';
        } elseif (!str_ends_with($tableName, 's')) {
            // Standard fallback rule
            $tableName .= 's';
        }

        return static::$tableCache[$className] = $tableName;
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
