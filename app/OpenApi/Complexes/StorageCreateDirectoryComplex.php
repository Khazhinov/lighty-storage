<?php

namespace App\OpenApi\Complexes;

use App\OpenApi\Complexes\StorageCreateDirectory\StorageCreateDirectoryArgumentsDTO;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\RequestReflector;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessSingleResourceResponse;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class StorageCreateDirectoryComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new StorageCreateDirectoryArgumentsDTO($arguments);
        $request_reflector = new RequestReflector();
        $complex_result = new ComplexFactoryResult();

        $complex_result->request_body = RequestBody::create()->content(
            MediaType::json()->schema(
                Schema::object('')->properties(
                    ...$request_reflector->getSchemaByRequest($arguments->validation_request)
                )
            ),
        );

        $complex_result->responses = [
            SuccessSingleResourceResponse::build(
                properties: [
                    Schema::string('bucket')->description('Идентификатор бакета'),
                    Schema::string('path')->default('/')->description('Путь к файлу относительно корня бакета'),
                    Schema::string('type')->default('directory')->description('Тип возвращаемого ресурса'),
                    Schema::string('name')->description('Название папки'),
                ],
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
