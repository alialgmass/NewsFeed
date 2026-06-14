<?php

namespace App\Exceptions\ApiException;

class ValidationExceptionResponse extends ApiException
{
    protected $code = 422;

    public static function instance(array $errors): static
    {
        return (new static(collect($errors)->first()[0]))->setCustomBody($errors);
    }

    public function getCustomMessage(): ?string
    {
        return $this->message;
    }

    public function getCustomBody(): ?array
    {
        return $this->customBody;
    }

    public function getCustomCode(): int
    {
        return 4220;
    }
}
