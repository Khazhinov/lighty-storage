<?php

namespace App\OpenApi\Complexes;

use App\OpenApi\Complexes\StorageMove\StorageMoveArgumentsDTO;
use App\OpenApi\Complexes\StorageDelete\StorageDeleteArgumentsDTO;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessResponse;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class StorageMoveComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new StorageMoveArgumentsDTO($arguments);
        $complex_result = new ComplexFactoryResult();

        $complex_result->request_body = RequestBody::create()->content(
            MediaType::json()->schema(
                Schema::object('')->properties(
                    Schema::string('bucket')->description('Идентификатор бакета'),
                    Schema::string('from')->description('Текущий абсолютный путь к объекту (папке или файлу) относительно корня бакета')
                        ->default('/текущий/путь/к/папке/или/файлу'),
                    Schema::string('to')->description('Новый абсолютный путь к объекту (папке или файлу) относительно корня бакета')
                        ->default('/новый/путь/к/папке/или/файлу'),
                )
            ),
        );

        $complex_result->responses = [
            SuccessResponse::build(
                data: [
                    Schema::boolean('status')->default(true)->description('Статус выполнения перемещения'),
                ],
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
