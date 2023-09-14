<?php

declare(strict_types = 1);

namespace App\Services\MinIO;

use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Filesystem\AwsS3V3Adapter as MinIOClient;
use Illuminate\Filesystem\FilesystemManager;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Throwable;

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

    public function createBucket(string $bucket, BucketTypeEnum $type = BucketTypeEnum::Public): void
    {
        $client = $this->getS3ClientForMainBucket();
        $client->createBucket([
            'Bucket' => $bucket,
        ]);

        if ($type === BucketTypeEnum::Public) {
            $client->putBucketPolicy([
                "Bucket" => $bucket,
                "Policy" => $this->getPublicBucketPolicy($bucket),
            ]);
        }
    }

    public function destroyBucket(string $bucket): void
    {
        $client = $this->getS3ClientForMainBucket();
        $iterator = $client->getIterator('ListObjects', [
            'Bucket' => $bucket,
        ]);
        foreach ($iterator as $object) {
            $client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $object['Key'],
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
            "Policy" => $this->getPublicBucketPolicy($main_bucket),
        ]);
    }

    public function getPublicBucketPolicy(string $bucket): MinIOPolicy
    {
        return new MinIOPolicy([
            "Version" => "2012-10-17",
            "Statement" => [
                [
                    "Effect" => "Allow",
                    "Principal" => [
                        "AWS" => ["*"],
                    ],
                    "Action" => [
                        "s3:GetBucketLocation",
                        "s3:ListAllMyBuckets",
                        "s3:ListBucket",
                    ],
                    "Resource" => [
                        "arn:aws:s3:::{$bucket}",
                    ],
                ],
                [
                    "Effect" => "Allow",
                    "Principal" => [
                        "AWS" => ["*"],
                    ],
                    "Action" => [
                        "s3:GetObject",
                    ],
                    "Resource" => [
                        "arn:aws:s3:::{$bucket}/*",
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param  string|null  $name
     * @return BucketDTO[]
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function bucketList(?string $name = null): array
    {
        $client = $this->getS3ClientForMainBucket();
        $buckets_list_result = $client->listBuckets();
        $buckets_list = $buckets_list_result->get('Buckets');
        $buckets = [];

        if (is_array($buckets_list)) {
            foreach ($buckets_list as $bucket) {
                $bucket['type'] = $this->getBucketPolicy($bucket['Name']);

                $buckets[] = new BucketDTO($bucket);
            }
        }

        return $buckets;
    }

    public function getBucketPolicy(string $bucket): BucketTypeEnum
    {
        $client = $this->getS3ClientForMainBucket();

        try {
            $result = $client->getBucketPolicy([
                'Bucket' => $bucket,
            ])->get('Policy');
        } catch (Throwable $e) {
            return BucketTypeEnum::Private;
        }

        if ($result) {
            /** @var Stream $result */
            $current_policy = $result->getContents();
            $current_policy = json_decode($current_policy, true);
            $public_policy = $this->getPublicBucketPolicy($bucket)->policy;

            helper_array_recursive_sort($current_policy);
            helper_array_recursive_sort($public_policy);

            if ($current_policy === $public_policy) {
                return BucketTypeEnum::Public;
            }
        }

        return BucketTypeEnum::Private;
    }
}
