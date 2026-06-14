<?php

namespace App\Http\Controllers\Api;

use App\Support\Api\ApiResponse;
use App\Support\Traits\AuthorizesRequests;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Str;

{
    use ApiResponse;
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public static array $orderBy = ['id' => 'desc'];

    public static ?string $model = null;

    protected ?int $perPage = 10;

    protected ?Authenticatable $user;

    protected bool $pagination = true;

    public function __construct()
    {
        $this->user = auth()->user();

        if (static::$model) {
            $this->authorizeResource(static::$model, Str::snake(class_basename(static::$model)));
        }
    }

    public static function label()
    {
        return __('app.'.Str::plural(Str::title(Str::snake(class_basename(static::class), ' '))));
    }

    public static function singularLabel()
    {
        return __('app.'.Str::singular(Str::title(Str::snake(class_basename(static::class), ' '))));
    }
}
