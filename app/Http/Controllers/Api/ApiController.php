<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;

abstract class ApiController extends Controller
{
    use ApiResponse;
}
