<?php

declare(strict_types = 1);

namespace App\Services\MinIO;

use JsonException;

class MinIOPolicy
{
    /**
     * @param  array<string, mixed>  $policy
     */
    public function __construct(
        public array $policy,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->policy, JSON_THROW_ON_ERROR);
    }
}
