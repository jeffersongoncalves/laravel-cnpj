<?php

namespace JeffersonGoncalves\Cnpj\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Cnpj\Models\Cnpj;
use JeffersonGoncalves\Cnpj\Support\CnpjSupport;

abstract class CnpjService
{
    /**
     * Fetch a sanitized CNPJ from Minha Receita and store it in the database.
     * Not found (404), rate limited/unavailable (429/503) and connection
     * errors all return an empty result and are never cached.
     */
    public static function findByCnpj(string $cnpj): array
    {
        try {
            $response = Http::timeout((int) config('cnpj.timeout', 10))
                ->withOptions(['verify' => (bool) config('cnpj.verify_ssl', true)])
                ->acceptJson()
                ->get(rtrim((string) config('cnpj.url', 'https://minhareceita.org'), '/').'/'.$cnpj);
        } catch (ConnectionException) {
            return CnpjSupport::getResult();
        }

        $data = $response->json();

        if (! $response->successful() || ! is_array($data) || empty($data['cnpj'])) {
            return CnpjSupport::getResult();
        }

        return Cnpj::updateByCnpj($cnpj, $data)->toResult();
    }
}
