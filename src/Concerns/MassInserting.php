<?php

namespace Bokt\Phpbb\Concerns;

trait MassInserting
{
    protected static function unsafeInsert(string $class, callable $callable)
    {
        forward_static_call([$class, 'query'])
            ->getConnection()
            ->getSchemaBuilder()
            ->disableForeignKeyConstraints();

        $return = null;

        forward_static_call([$class, 'unguarded'], function () use ($class, $callable, &$return) {
            $return = $callable();
        });

        forward_static_call([$class, 'query'])
            ->getConnection()
            ->getSchemaBuilder()
            ->enableForeignKeyConstraints();

        return $return;
    }
}
