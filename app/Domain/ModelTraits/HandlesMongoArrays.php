<?php

namespace App\Domain\ModelTraits;

trait HandlesMongoArrays
{
    public function setArrayAttribute($key, $value)
    {
        $this->attributes[$key] = $this->ensureMongoArray($value);
    }
    protected function ensureMongoArray($value): array
    {
        if (is_string($value) && str_starts_with($value, '[')) {
            return json_decode($value, true);
        }
        return (array)$value;
    }
}
