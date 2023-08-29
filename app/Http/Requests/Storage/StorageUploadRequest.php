<?php

declare(strict_types = 1);

namespace App\Http\Requests\Storage;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;

final class StorageUploadRequest extends BaseRequest
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
            'file' => [
                'required',
                'file',
                'max:15000',
            ],
            'path' => [
                'required',
                'string',
            ],
            'name' => [
                'sometimes',
                'string',
            ],
        ];
    }
}
