<?php

declare(strict_types = 1);

namespace App\OpenApi\Complexes\StorageCreateDirectory;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class StorageCreateDirectoryArgumentsDTO extends DataTransferObject
{
    #[ExistsInParents(parent: BaseRequest::class)]
    public string $validation_request;
}
