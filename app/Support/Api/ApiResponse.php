<?php

namespace App\Support\Api;

trait ApiResponse
{
    protected function apiBody(): ApiBody
    {
        return new ApiBody;
    }
}
