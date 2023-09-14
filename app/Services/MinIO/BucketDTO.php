<?php

declare(strict_types = 1);

namespace App\Services\MinIO;

use Khazhinov\PhpSupport\DTO\Custer\EnumCaster;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\MapFrom;

class BucketDTO extends DataTransferObject
{
    #[MapFrom('Name')]
    public string $name;

    #[CastWith(EnumCaster::class, enumType: BucketTypeEnum::class)]
    public BucketTypeEnum $type;
}
