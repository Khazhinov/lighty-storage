<?php

namespace App\OpenApi\Complexes;

use App\OpenApi\Complexes\StorageView\StorageViewArgumentsDTO;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\RequestReflector;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessResponse;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class StorageViewComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new StorageViewArgumentsDTO($arguments);
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
            SuccessResponse::build(
                data: Schema::object()->properties(
                    Schema::string('bucket')->description('Идентификатор бакета'),
                    Schema::string('path')->default('/')->description('Путь относительно корня бакета'),
                    Schema::string('type')->default('file|directory')->description('Тип возвращаемого ресурса'),
                    Schema::string('name')->description('Название файла или папки'),
                    Schema::string('extension')->description('Расширение файла (только если type == file)'),
                    Schema::integer('size')->description('Размер файла в байтах (только если type == file)'),
                    Schema::string('url')->description('Путь для загрузки файла (только если type == file)'),
                    Schema::string('last_modified')->default('2022-10-21 09:49:40')->description('Дата последнего изменения файла (только если type == file)'),
                ),
                data_type: 'array'
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
