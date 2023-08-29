<?php

namespace App\OpenApi\Complexes;

use App\OpenApi\Complexes\StorageUpload\StorageUploadArgumentsDTO;
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

class StorageUploadComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new StorageUploadArgumentsDTO($arguments);
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
                    Schema::string('type')->default('file')->description('Тип возвращаемого ресурса'),
                    Schema::string('name')->description('Название файла'),
                    Schema::string('extension')->description('Расширение файла'),
                    Schema::integer('size')->description('Размер файла в байтах'),
                    Schema::string('url')->description('Путь для загрузки файла'),
                    Schema::string('last_modified')->default('2022-10-21 09:49:40')->description('Дата последнего изменения файла'),
                ],
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
