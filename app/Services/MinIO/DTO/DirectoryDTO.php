<?php

declare(strict_types = 1);

namespace App\Services\MinIO\DTO;

use Khazhinov\PhpSupport\DTO\DataTransferObject;

class DirectoryDTO extends DataTransferObject
{
    public string $bucket;
    public string $path;
    public string $type = 'directory';
    public string $name;
}
