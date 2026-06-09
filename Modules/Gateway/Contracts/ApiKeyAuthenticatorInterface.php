<?php

namespace Modules\Gateway\Contracts;

use Modules\Gateway\Models\ApiKey;

interface ApiKeyAuthenticatorInterface
{
    public function validate(string $apiKey): ?ApiKey;

    public function find(string $key): ?ApiKey;

    public function generateKey(string $name, array $options = []): ApiKey;
}
