<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Web\Storage;

use App\Http\Requests\Storage\StorageDownloadRequest;
use App\Services\MinIO\MinIOService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Khazhinov\LaravelLighty\Http\Controllers\Api\ApiController;

class StorageController extends ApiController
{
    public function __construct(
        protected MinIOService $storageService,
    ) {
        parent::__construct();
    }

    public function download(string $bucket, StorageDownloadRequest $request): RedirectResponse|Redirector
    {
        $path = $request->get('path');

        if (! str_ends_with($path, '/')) {
            $path .= '/';
        }

        if ($path === '/') {
            $path = '';
        }

        $filename = $request->get('filename');
        $client = $this->storageService->getS3ClientForSpecifyBucket($bucket);

        $key = $path.$filename;

        $command = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $key,
        ]);

        $request = $client->createPresignedRequest($command, '+1 minute');
        $result_url = $this->makeUrl((string) $request->getUri());

        return redirect($result_url);
    }

    protected function makeUrl(string $storage_url): string
    {
        /** @var string $storage_endpoint */
        $storage_endpoint = config('filesystems.disks.minio.endpoint');
        /** @var string $storage_public_endpoint */
        $storage_public_endpoint = config('filesystems.disks.minio.public_endpoint');

        return str_replace(
            $storage_endpoint,
            $storage_public_endpoint,
            $storage_url
        );
    }
}
