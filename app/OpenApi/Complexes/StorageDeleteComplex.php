<?php

namespace App\OpenApi\Complexes;

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

class StorageDeleteComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $arguments = new StorageDeleteArgumentsDTO($arguments);
        $complex_result = new ComplexFactoryResult();

        $complex_result->request_body = RequestBody::create()->content(
            MediaType::json()->schema(
                Schema::object('')->properties(
                    Schema::string('bucket')->description('Идентификатор бакета'),
                    Schema::array('paths')->items(
                        Schema::string()
//                            ->default('/путь/к/папке | /путь/к/файлу.txt')
                            ->description('Абсолютный путь к объекту (папке или файлу) относительно корня бакета')
                    )
                        ->default([
                            '/путь/к/папке',
                            '/путь/к/файлу.txt',
                        ])
                        ->description('Список удаляемых объектов'),
                )
            ),
        );

        $complex_result->responses = [
            SuccessResponse::build(
                data: [
                    Schema::array('paths')->items(
                        Schema::string()
                    )
                        ->default([
                            '/путь/к/папке',
                            '/путь/к/файлу.txt',
                        ])
                        ->description('Список удаленных объектов'),
                ],
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
