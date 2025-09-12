<?php

declare(strict_types=1);

namespace Debug;

class DebugHelper
{
    public static function debug(mixed $data): void
    {
        echo '<pre>';
        echo json_encode(self::objectToArray($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo '</pre>';
    }

    public static function objectToArray(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map(fn($item) => self::objectToArray($item), $data);
        }

        if (is_object($data)) {
            // If object has toArray() method, use it
            if (method_exists($data, 'toArray')) {
                return self::objectToArray($data->toArray());
            }

            // Otherwise, use reflection to access properties
            $result = [];
            $reflection = new \ReflectionObject($data);
            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                $result[$property->getName()] = self::objectToArray($property->getValue($data));
            }
            return $result;
        }

        return $data; // scalar value
    }

}
