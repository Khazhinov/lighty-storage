<?php

declare(strict_types = 1);

namespace App\Services\MinIO\DTO;

use Khazhinov\PhpSupport\DTO\DataTransferObject;

class FileDTO extends DataTransferObject
{
    public string $bucket;
    public string $path;
    public string $type = 'file';
    public string $name;
    public string $extension;
    public int $size;
    public string $url;
    //    public string $last_modified;
}
