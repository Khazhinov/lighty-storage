<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1_0\Bucket;

use App\Http\Requests\Bucket\CreateBucketRequest;
use App\Http\Requests\Bucket\DeleteBucketRequest;
use App\OpenApi\Complexes\BucketListComplex;
use App\OpenApi\Complexes\CreateBucketComplex;
use App\OpenApi\Complexes\DeleteBucketComplex;
use App\Services\MinIO\BucketTypeEnum;
use App\Services\MinIO\MinIOService;
use JsonException;
use Khazhinov\LaravelFlyDocs\Generator\Attributes as OpenApi;
use Khazhinov\LaravelLighty\Http\Controllers\Api\ApiController;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\Response;

#[OpenApi\PathItem]
final class BucketCRUDController extends ApiController
{
    public function __construct(
        protected MinIOService $storageService,
    ) {
        parent::__construct();
    }

    /**
     * Возвращает список доступных Bucket
     *
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    #[OpenApi\Operation(tags: ['Bucket'])]
    #[OpenApi\Complex(
        factory: BucketListComplex::class,
    )]
    public function bucketList(): Response
    {
        $buckets = $this->storageService->bucketList();

        return $this->respond(
            $this->buildActionResponseDTO(
                data: $buckets
            )
        );
    }

    /**
     * Создание Bucket
     *
     * @throws UnknownProperties
     * @throws ReflectionException
     * @throws JsonException
     */
    #[OpenApi\Operation(tags: ['Bucket'])]
    #[OpenApi\Complex(
        factory: CreateBucketComplex::class,
        validation_request: CreateBucketRequest::class,
    )]
    public function createBucket(CreateBucketRequest $request): Response
    {
        /** @var string $bucket_name */
        $bucket_name = $request->get('name');
        $bucket_type = BucketTypeEnum::from($request->get('type'));
        $this->storageService->createBucket($bucket_name, $bucket_type);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: 'ok'
            )
        );
    }

    /**
     * Удаление Bucket
     *
     * @throws UnknownProperties
     * @throws ReflectionException
     * @throws JsonException
     */
    #[OpenApi\Operation(tags: ['Bucket'])]
    #[OpenApi\Complex(
        factory: DeleteBucketComplex::class,
        validation_request: DeleteBucketRequest::class,
    )]
    public function deleteBucket(DeleteBucketRequest $request): Response
    {
        /** @var string $bucket_name */
        $bucket_name = $request->get('name');
        $this->storageService->destroyBucket($bucket_name);

        return $this->respond(
            $this->buildActionResponseDTO(
                data: 'ok'
            )
        );
    }
}
