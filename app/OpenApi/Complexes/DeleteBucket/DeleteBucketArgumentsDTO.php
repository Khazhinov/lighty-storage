<?php

declare(strict_types = 1);

namespace App\OpenApi\Complexes\DeleteBucket;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class DeleteBucketArgumentsDTO extends DataTransferObject
{
    #[ExistsInParents(parent: BaseRequest::class, nullable: true)]
    public string $validation_request;
}
