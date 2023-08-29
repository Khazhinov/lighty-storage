<?php

declare(strict_types = 1);

namespace App\Http\Requests\Storage;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;

final class StorageViewRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bucket' => [
                'required',
                'string',
            ],
            'path' => [
                'required',
                'string',
            ],
        ];
    }
}
