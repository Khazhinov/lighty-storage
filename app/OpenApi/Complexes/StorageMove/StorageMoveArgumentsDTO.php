<?php

declare(strict_types = 1);

namespace App\OpenApi\Complexes\StorageMove;

use Khazhinov\LaravelLighty\Http\Requests\BaseRequest;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Khazhinov\PhpSupport\DTO\Validation\ExistsInParents;

class StorageMoveArgumentsDTO extends DataTransferObject
{
//    #[ExistsInParents(parent: BaseRequest::class)]
//    public string $validation_request;
}
