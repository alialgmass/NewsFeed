<?php

namespace Modules\Gateway\Http\Requests;

use App\Support\Traits\Request\ValidationRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateApiKeyRequest extends FormRequest
{
    use ValidationRequest;

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
