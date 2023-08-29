<?php

declare(strict_types = 1);

namespace App\Http\Requests\Bucket;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;

final class BucketUpdateRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'size' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }
}
