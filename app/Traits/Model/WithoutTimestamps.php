<?php

namespace App\Traits\Model;

trait WithoutTimestamps
{
    public function saveWithoutTimestamps(): bool
    {
        $this->timestamps = false;
        $result = $this->save();
        $this->timestamps = true;
        return $result;
    }

    public function updateWithoutTimestamps(array $attributes = []): bool
    {
        $this->timestamps = false;
        $result = $this->update($attributes);
        $this->timestamps = true;
        return $result;
    }
}