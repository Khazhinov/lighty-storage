<?php

namespace App\Services\MinIO;

enum BucketTypeEnum: string
{
    case Public = 'public';
    case Private = 'private';
}
