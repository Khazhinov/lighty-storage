<?php

declare(strict_types = 1);

namespace App\OpenApi\Complexes\StorageUpload;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class StorageUploadArgumentsDTO extends DataTransferObject
{
    #[ExistsInParents(parent: BaseRequest::class)]
    public string $validation_request;
}
