<?php

namespace Modules\Gateway\Services;

use Modules\Gateway\Contracts\ApiKeyAuthenticatorInterface;
use Modules\Gateway\Models\ApiKey;

class ApiKeyService implements ApiKeyAuthenticatorInterface
{
    public function validate(string $apiKey): ?ApiKey
    {
        $key = $this->find($apiKey);

        if (! $key) {
            return null;
        }

        if (! $key->isValid()) {
            return null;
        }

        $this->checkIpRestriction($key);

        $key->markUsed();

        return $key->fresh();
    }

    public function find(string $key): ?ApiKey
    {
        return ApiKey::where('key', $key)->first();
    }

    public function generateKey(string $name, array $options = []): ApiKey
    {
        return ApiKey::generate($name, $options);
    }

    protected function checkIpRestriction(ApiKey $apiKey): void
    {
        if ($apiKey->allowed_ips === null || $apiKey->allowed_ips === []) {
            return;
        }

        $request = request();

        if ($request === null) {
            return;
        }

        $requestIp = $request->ip();

        if (! in_array($requestIp, $apiKey->allowed_ips)) {
            abort(401, __('IP address not allowed for this API key'));
        }
    }
}
