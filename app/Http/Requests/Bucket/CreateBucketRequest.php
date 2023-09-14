<?php

declare(strict_types = 1);

namespace App\Http\Requests\Bucket;

use App\Services\MinIO\BucketTypeEnum;
use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\LaravelLighty\Http\Requests\Enum;

final class CreateBucketRequest extends BaseRequest
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
                'min:3',
                'max:63',
                'regex:"^([a-z]([a-z]|\d|\-|\.)+([a-z]|\d))$"',
            ],
            'type' => [
                'required',
                'string',
                new Enum(BucketTypeEnum::class),
            ],
        ];
    }
}
