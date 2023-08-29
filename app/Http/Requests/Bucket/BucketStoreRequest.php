<?php

declare(strict_types = 1);

namespace App\Http\Requests\Bucket;

use App\Models\Enums\BucketModelTypeEnum;
use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\LaravelLighty\Http\Requests\Enum;

final class BucketStoreRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'model_type' => [
                'required',
                'string',
                new Enum(BucketModelTypeEnum::class),
            ],
            'model_id' => [
                'required',
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
