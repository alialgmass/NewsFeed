<?php

namespace Modules\User\Http\Requests;

use App\Support\Traits\Request\ValidationRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    use ValidationRequest;

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,'.$this->route('user')?->id],
        ];
    }
}
