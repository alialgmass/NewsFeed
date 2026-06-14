<?php

namespace App\Support\Traits\Request;

use App\Support\Api\ApiBody;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait ValidationRequest
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            (new ApiBody)
                ->errors($validator->errors()->toArray())
                ->statusCode(422)
                ->apiResponse()
        );
    }
}
