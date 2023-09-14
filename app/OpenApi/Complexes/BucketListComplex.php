<?php

namespace App\OpenApi\Complexes;

use App\Services\MinIO\BucketTypeEnum;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactory;
use Khazhinov\LaravelFlyDocs\Generator\Factories\ComplexFactoryResult;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\ErrorResponse;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Responses\SuccessCollectionResourceResponse;

class BucketListComplex extends ComplexFactory
{
    /**
     * @param  mixed  ...$arguments
     * @return ComplexFactoryResult
     */
    public function build(...$arguments): ComplexFactoryResult
    {
        $complex_result = new ComplexFactoryResult();

        $complex_result->responses = [
            SuccessCollectionResourceResponse::build(
                item: Schema::object()->properties(
                    Schema::string('name')->default('bucket-name'),
                    Schema::string('type')->enum(...helper_enum_get_values(BucketTypeEnum::class))->default('public'),
                ),
                is_pagination_enable: false
            ),
            ErrorResponse::build(),
        ];

        return $complex_result;
    }
}
