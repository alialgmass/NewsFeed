<?php

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;

class ApiBody
{
    protected mixed $data = null;

    protected ?string $message = null;

    protected array $errors = [];

    protected int $statusCode = 200;

    protected array $headers = [];

    public function data(mixed $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function message(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function errors(array $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    public function statusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function header(string $key, string $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function apiResponse(): JsonResponse
    {
        $response = [];

        if ($this->message !== null) {
            $response['message'] = $this->message;
        }

        if (! empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        if ($this->data !== null) {
            $response['data'] = $this->data;
        }

        return response()->json($response, $this->statusCode, $this->headers);
    }
}
