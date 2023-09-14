<?php

namespace App\OpenApi\Complexes;

use App\OpenApi\Complexes\StorageUpload\StorageUploadArgumentsDTO;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelFlyDocs\Generator\Objects\MediaTypeWithFormData;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\RequestReflector;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessSingleResourceResponse;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class StorageSimpleUploadComplex extends ComplexFactory
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
            MediaTypeWithFormData::formData()->schema(
                Schema::object('')->properties(
                    Schema::string('bucket')
                        ->nullable(false)
                        ->default('main')
                        ->description('Название бакета.'),
                    Schema::string('path')
                        ->nullable(false)
                        ->default('/')
                        ->description('Путь в бакете.'),
                    Schema::string('file')
                        ->nullable(false)
                        ->format('binary')
                        ->description('Файл.'),
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
