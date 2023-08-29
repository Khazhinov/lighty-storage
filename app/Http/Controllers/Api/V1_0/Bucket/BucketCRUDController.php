<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1_0\Bucket;

use App\Http\Requests\Bucket\BucketStoreRequest;
use App\Http\Requests\Bucket\BucketUpdateRequest;
use App\Http\Resources\Bucket\BucketCollection;
use App\Http\Resources\Bucket\BucketResource;
use App\Models\Bucket;
use App\Services\MinIO\MinIOService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use JsonException;
use Khazhinov\LaravelFlyDocs\Generator\Attributes as OpenApi;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\ApiCRUDController;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ActionClosureModeEnum;
use Khazhinov\LaravelLighty\Http\Controllers\Api\CRUD\DTO\ApiCRUDControllerMetaDTO;
use Khazhinov\LaravelLighty\Http\Requests\CRUD\IndexRequest;
use Khazhinov\LaravelLighty\OpenApi\Complexes\DestroyActionComplex;
use Khazhinov\LaravelLighty\OpenApi\Complexes\IndexActionComplex;
use Khazhinov\LaravelLighty\OpenApi\Complexes\ShowActionComplex;
use Khazhinov\LaravelLighty\OpenApi\Complexes\StoreActionComplex;
use Khazhinov\LaravelLighty\OpenApi\Complexes\UpdateActionComplex;
use Khazhinov\LaravelLighty\Transaction\WithDBTransaction;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

#[OpenApi\PathItem]
final class BucketCRUDController extends ApiCRUDController
{
    use WithDBTransaction;

    /**
     * @throws UnknownProperties
     * @throws ReflectionException
     */
    public function __construct(
        protected MinIOService $storage_service,
    ) {
        parent::__construct(new ApiCRUDControllerMetaDTO([
            'model_class' => Bucket::class,
            'single_resource_class' => BucketResource::class,
            'collection_resource_class' => BucketCollection::class,
        ]));
    }

    /**
     * Поиск сущностей
     *
     * @param  IndexRequest  $request
     * @return BinaryFileResponse|Response
     * @throws ReflectionException
     * @throws UnknownProperties
     * @throws JsonException
     */
    #[OpenApi\Operation(tags: ['Bucket'])]
    #[OpenApi\Complex(
        factory: IndexActionComplex::class,
        model_class: Bucket::class,
        collection_resource: BucketCollection::class,
        options: []
    )]
    public function index(IndexRequest $request): mixed
    {
        return $this->indexAction(
            request: $request,
            options: []
        );
    }

    /**
     * Получение сущности по идентификатору
     *
     * @param  string $key Идентификатор сущности
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    #[OpenApi\Operation(tags: ['Bucket'])]
    #[OpenApi\Complex(
        factory: ShowActionComplex::class,
        model_class: Bucket::class,
        single_resource: BucketResource::class,
        options: []
    )]
    public function show(string $key): Response
    {
        return $this->showAction(
            key: $key,
            options: []
        );
    }

    /**
     * Создание сущности
     *
     * @param  BucketStoreRequest  $request
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    #[OpenApi\Operation(tags: ['Bucket'])]
    #[OpenApi\Complex(
        factory: StoreActionComplex::class,
        model_class: Bucket::class,
        single_resource: BucketResource::class,
        validation_request: BucketStoreRequest::class,
        options: []
    )]
    public function store(BucketStoreRequest $request): Response
    {
        return $this->storeAction(
            request: $request,
            options: [],
            closure: function (mixed $model, ActionClosureModeEnum $mode) {
                if ($mode === ActionClosureModeEnum::AfterSave) {
                    // Регистрация бакета в MinIO
                    $this->storage_service->createBucket($model->id);
                }
            }
        );
    }

    /**
     * Изменение сущности по идентификатору
     *
     * @param  BucketUpdateRequest  $request
     * @param  string $key Идентификатор сущности
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    #[OpenApi\Operation(tags: ['Bucket'])]
    #[OpenApi\Complex(
        factory: UpdateActionComplex::class,
        model_class: Bucket::class,
        single_resource: BucketResource::class,
        validation_request: BucketUpdateRequest::class,
        options: []
    )]
    public function update(BucketUpdateRequest $request, string $key): Response
    {
        return $this->updateAction(
            request: $request,
            key: $key,
            options: [],
        );
    }

    /**
     * Удаление сущности по идентификатору
     *
     * @param  string  $key Идентификатор сущности
     * @return Response
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws UnknownProperties
     */
    #[OpenApi\Operation(tags: ['Bucket'])]
    #[OpenApi\Complex(
        factory: DestroyActionComplex::class,
        model_class: Bucket::class,
        options: []
    )]
    public function destroy(string $key): Response
    {
        return $this->destroyAction(
            key: $key,
            options: [],
            closure: function (mixed $model, ActionClosureModeEnum $mode) {
                if ($mode === ActionClosureModeEnum::AfterDeleting) {
                    // Удаление бакета в MinIO
                    $this->storage_service->destroyBucket($model->id);
                }
            }
        );
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultOrder(): array
    {
        return [
            '-id',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getQueryBuilder(): Builder|DatabaseBuilder
    {
        /** @var Builder|DatabaseBuilder $builder */
        $builder = Bucket::query();

        return $builder;
    }
}
