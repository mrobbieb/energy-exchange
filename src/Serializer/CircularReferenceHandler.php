<?php

namespace App\Serializer;

class CircularReferenceHandler
{
    public function __invoke(object $object, string $format = null, array $context = []): string|int
    {
        // When hitting a circular reference, just return the ID if possible
        if (method_exists($object, 'getId')) {
            return $object->getId();
        }

        // Fallback: unique object id
        return spl_object_id($object);
    }
}
