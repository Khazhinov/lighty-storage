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
        $result_url = (string) $request->getUri();

        return redirect($result_url);
    }
}
