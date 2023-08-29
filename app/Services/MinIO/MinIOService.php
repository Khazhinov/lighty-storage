<?php

declare(strict_types = 1);

namespace App\Services\MinIO;

use Aws\S3\S3Client;
use Illuminate\Filesystem\AwsS3V3Adapter as MinIOClient;
use Illuminate\Filesystem\FilesystemManager;

class MinIOService
{
    public function __construct(
        protected FilesystemManager $filesystemManager,
    ) {
    }

    public function getStorageClientForMainBucket(): MinIOClient
    {
        /** @var MinIOClient $storage_client */
        $storage_client = $this->filesystemManager->createS3Driver(config('filesystems.disks.minio'));

        return $storage_client;
    }

    public function getS3ClientForMainBucket(): S3Client
    {
        return $this->getStorageClientForMainBucket()->getClient();
    }

    public function getStorageClientForSpecifyBucket(string $bucket): MinIOClient
    {
        $minio_configuration = config('filesystems.disks.minio');
        if (array_key_exists('bucket', $minio_configuration)) {
            unset($minio_configuration['bucket']);
        }

        $minio_configuration['bucket'] = $bucket;

        /** @var MinIOClient $storage_client */
        $storage_client = $this->filesystemManager->createS3Driver($minio_configuration);

        return $storage_client;
    }

    public function getS3ClientForSpecifyBucket(string $bucket): S3Client
    {
        return $this->getStorageClientForSpecifyBucket($bucket)->getClient();
    }

    public function createBucket(string $bucket): void
    {
        $client = $this->getS3ClientForMainBucket();
        $client->createBucket([
            'Bucket' => $bucket,
        ]);

        $client->putBucketPolicy([
            "Bucket" => $bucket,
            "Policy" => $this->getDefaultBucketPolicy($bucket),
        ]);
    }

    public function destroyBucket(string $bucket): void
    {
        $client = $this->getS3ClientForMainBucket();
        $iterator = $client->getIterator('ListObjects', [
            'Bucket' => $bucket
        ]);
        foreach ($iterator as $object) {
            $client->deleteObject([
                'Bucket'  =>  $bucket,
                'Key'     =>  $object['Key']
            ]);
        }
        $client->deleteBucket([
            'Bucket' => $bucket,
        ]);
    }

    public function initMainBucket(): void
    {
        $main_bucket = config('filesystems.disks.minio.bucket');
        $client = $this->getS3ClientForMainBucket();
        if (! $client->doesBucketExist($main_bucket)) {
            $client->createBucket([
                'Bucket' => config('filesystems.disks.minio.bucket'),
                'ACL' => 'public-read',
            ]);
        }

        $client->putBucketPolicy([
            "Bucket" => $main_bucket,
            "Policy" => $this->getDefaultBucketPolicy($main_bucket),
        ]);
    }

    public function getDefaultBucketPolicy(string $bucket): MinIOPolicy
    {
        return new MinIOPolicy([
            "Version" => "2012-10-17",
            "Statement" => [
                [
                    "Effect" => "Allow",
                    "Action" => [
                        "s3:ListBucket",
                        "s3:ListAllMyBuckets",
                        "s3:GetBucketLocation",
                    ],
                    "Resource" => [
                        "arn:aws:s3:::{$bucket}",
                    ],
                    "Principal" => "*",
                ],
                [
                    "Effect" => "Allow",
                    "Action" => [
                        "s3:GetObject",
                    ],
                    "Resource" => [
                        "arn:aws:s3:::{$bucket}/*",
                    ],
                    "Principal" => "*",
                ],
            ],
        ]);
    }
}
