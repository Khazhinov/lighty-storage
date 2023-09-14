<?php

declare(strict_types = 1);

namespace App\Http\Requests\Bucket;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;

final class DeleteBucketRequest extends BaseRequest
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
                'regex:^([a-z]([a-z]|\d|\-|\.)+([a-z]|\d))$',
            ],
        ];
    }
}
