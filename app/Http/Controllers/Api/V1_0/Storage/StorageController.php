<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1_0\Storage;

use App\Http\Requests\Storage\StorageCreateDirectoryRequest;
use App\Http\Requests\Storage\StorageDeleteRequest;
use App\Http\Requests\Storage\StorageMoveRequest;
use App\Http\Requests\Storage\StorageUploadRequest;
use App\Http\Requests\Storage\StorageViewRequest;
use App\OpenApi\Complexes\StorageCreateDirectoryComplex;
use App\OpenApi\Complexes\StorageDeleteComplex;
use App\OpenApi\Complexes\StorageMoveComplex;
use App\OpenApi\Complexes\StorageMoveDirectoryComplex;
use App\OpenApi\Complexes\StorageUploadComplex;
use App\OpenApi\Complexes\StorageViewComplex;
use App\Services\MinIO\DTO\DirectoryDTO;
use App\Services\MinIO\DTO\FileDTO;
use App\Services\MinIO\MinIOService;
use Illuminate\Http\UploadedFile;
use JsonException;
use Khazhinov\LaravelFlyDocs\Generator\Attributes as OpenApi;
use Khazhinov\LaravelLighty\Http\Controllers\Api\ApiController;
use League\Flysystem\FilesystemException;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\Response;

#[OpenApi\PathItem]
class StorageController extends ApiController
{
    public function __construct(
        protected MinIOService $storageService,
    ) {
        parent::__construct();
    }

    /**
     * Загрузка файла в бакет
     *
     * @throws UnknownProperties
     * @throws ReflectionException
     * @throws JsonException
     */
    #[OpenApi\Operation(tags: ['Storage'])]
    #[OpenApi\Complex(
        factory: StorageUploadComplex::class,
        validation_request: StorageUploadRequest::class,
    )]
    public function upload(StorageUploadRequest $request): Response
    {
        $bucket = $request->get('bucket');
        $client = $this->storageService->getStorageClientForSpecifyBucket($bucket);
        /** @var UploadedFile $uploaded_file */
        $uploaded_file = $request->file('file');

        $path = $this->normalizeStoragePath($request->get('path'));

        $extension = $uploaded_file->getClientOriginalExtension();

        if ($request->has('name')) {
            $name = $request->get('name');
        } else {
            $name = $uploaded_file->getClientOriginalName();
        }

        if (! str_ends_with($name, ".{$extension}")) {
            $name .= ".{$extension}";
        }

        $upload_result = $client->putFileAs($path, $uploaded_file, $name);

        if (! $upload_result) {
            throw new UploadException('Не удалось загрузить файл на сервер.');
        }

        $file = new FileDTO([
            'bucket' => $bucket,
            'path' => $path,
            'name' => $name,
            'extension' => $uploaded_file->getClientOriginalExtension(),
            'size' => $uploaded_file->getSize(),
            'url' => $this->makeUrl($client->url($path.$name)),
        ]);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $file->toArray(),
            )
        );
    }

    /**
     * Создание папки в бакете
     *
     * @throws UnknownProperties
     * @throws ReflectionException
     * @throws JsonException
     * @throws FilesystemException
     */
    #[OpenApi\Operation(tags: ['Storage'])]
    #[OpenApi\Complex(
        factory: StorageCreateDirectoryComplex::class,
        validation_request: StorageCreateDirectoryRequest::class,
    )]
    public function createDirectory(StorageCreateDirectoryRequest $request): Response
    {
        $bucket = $request->get('bucket');
        $client = $this->storageService->getStorageClientForSpecifyBucket($bucket);

        $path = $this->normalizeStoragePath($request->get('path'));
        $name = $request->get('name');

        $client->createDirectory($path . $name);

        $directory = new DirectoryDTO([
            'bucket' => $bucket,
            'path' => $path,
            'name' => $name,
        ]);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $directory->toArray(),
            )
        );
    }

    /**
     * Просмотр списка доступных объектов бакета
     *
     * @param  StorageViewRequest  $request
     * @return Response
     * @throws ReflectionException
     * @throws UnknownProperties
     * @throws JsonException
     */
    #[OpenApi\Operation(tags: ['Storage'])]
    #[OpenApi\Complex(
        factory: StorageViewComplex::class,
        validation_request: StorageViewRequest::class,
    )]
    public function view(StorageViewRequest $request): Response
    {
        $bucket = $request->get('bucket');
        $client = $this->storageService->getStorageClientForSpecifyBucket($bucket);

        $path = $this->normalizeStoragePath($request->get('path'));

        $result = [];
        $directories = $client->directories($path);
        foreach ($directories as $directory) {
            $_ = new DirectoryDTO([
                'bucket' => $bucket,
                'path' => $path,
                'name' => $directory,
            ]);

            $result[] = $_->toArray();
        }

        $files = $client->files($path);
        foreach ($files as $file) {
            if (str_starts_with($file, substr($path, 1, strlen($path)))) {
                $file = str_replace(substr($path, 1, strlen($path)), '', $file);
            }

            $fileExtension = explode('.', $file);
            $fileExtension = last($fileExtension);

            $_ = new FileDTO([
                'bucket' => $bucket,
                'path' => $path,
                'name' => $file,
                'extension' => $fileExtension,
                'size' => $client->size($path.$file),
                'url' => $this->makeUrl($client->url($path.$file)),
            ]);

            $result[] = $_->toArray();
        }

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $result
            )
        );
    }

    /**
     * Удаление объектов из бакета
     *
     * @param  StorageDeleteRequest  $request
     * @return Response
     * @throws ReflectionException
     * @throws UnknownProperties
     * @throws JsonException
     */
    #[OpenApi\Operation(tags: ['Storage'])]
    #[OpenApi\Complex(
        factory: StorageDeleteComplex::class,
    )]
    public function delete(StorageDeleteRequest $request): Response
    {
        $bucket = $request->get('bucket');
        $client = $this->storageService->getStorageClientForSpecifyBucket($bucket);
        $paths = $request->get('paths');

        foreach ($paths as $path) {
            $path = explode('/', $path);
            $file = array_pop($path);
            // Если нет расширения, значит папка
            if (! str_contains($file, '.')) {
                $path[] = $file;
                $file = "";
            }
            $path = implode('/', $path);
            $path = $this->normalizeStoragePath($path);

            if ($file === "") {
                $client->deleteDirectory($path);
            } else {
                $client->delete($path.$file);
            }
        }

        return $this->respond(
            $this->buildActionResponseDTO(
                data: [
                    'paths' => $paths,
                ]
            )
        );
    }

    /**
     * Перемещение объекта
     *
     * @param  StorageMoveRequest  $request
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    #[OpenApi\Operation(tags: ['Storage'])]
    #[OpenApi\Complex(
        factory: StorageMoveComplex::class,
    )]
    public function move(StorageMoveRequest $request): Response
    {
        $bucket = $request->get('bucket');
        $from = $request->get('from');
        $to = $request->get('to');
        $client = $this->storageService->getStorageClientForSpecifyBucket($bucket);

        //        $path = explode('/', $from);
        //        $file = array_pop($path);
        //        // Если нет расширения, значит папка
        //        if (! str_contains($file, '.')) {
        //            $path[] = $file;
        //            $file = "";
        //        }
        //        $path = implode('/', $path);
        //        $path = $this->normalizeStoragePath($path);
        //
        //        if ($result = $client->copy($from, $to)) {
        //            if ($file === "") {
        //                $result = $client->deleteDirectory($path);
        //            } else {
        //                $result = $client->delete($path.$file);
        //            }
        //        }

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $client->move($from, $to),
            )
        );
    }

    /**
     * Перемещение объекта
     *
     * @param StorageMoveRequest $request
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws UnknownProperties
     * @throws FilesystemException
     */
    #[OpenApi\Operation(tags: ['Storage'])]
    #[OpenApi\Complex(
        factory: StorageMoveDirectoryComplex::class,
    )]
    public function moveDirectory(StorageMoveRequest $request): Response
    {
        $bucket = $request->get('bucket');
        $from = $request->get('from');
        $to = $request->get('to');
        $client = $this->storageService->getStorageClientForSpecifyBucket($bucket);
        $images = $client->allFiles($from);
        $result = false;

        if (empty($images)) {
            $dirs = $client->allDirectories();
            foreach ($dirs as $dir) {
                $dir = '/' . $dir . '/';
                if (str_starts_with($dir, $from. '/')) {
                    $new_dir = str_replace($from, $to, $dir);
                    $client->createDirectory($new_dir);

                    $client->deleteDirectory($from);
                    $result = true;

                }
            }

        } else {
            foreach($images as $image) {
                $from_dir = trim($from, '/');
                $new_loc = str_replace($from_dir, $to, $image);

                if ($client->move($image, $new_loc)) {
                    $result = true;
                }
            }
        }

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $result,
            )
        );
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

    protected function normalizeStoragePath(string $path): string
    {
        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }
        if (! str_ends_with($path, '/')) {
            $path .= '/';
        }

        return $path;
    }
}
