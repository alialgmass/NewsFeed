<?php

namespace Modules\Gateway\Http\Requests;

use App\Support\Traits\Request\ValidationRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
{
    use ValidationRequest;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'rate_limit_tier' => ['nullable', 'string', 'in:basic,pro,enterprise,unlimited'],
        ];
    }
}
