<?php

namespace Modules\Search\Http\Requests;

use App\Support\Traits\Request\ValidationRequest;
use Illuminate\Foundation\Http\FormRequest;

class AutocompleteRequest extends FormRequest
{
    use ValidationRequest;

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
