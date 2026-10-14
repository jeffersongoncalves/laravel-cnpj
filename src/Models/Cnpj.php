<?php

namespace JeffersonGoncalves\Cnpj\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\Cnpj\Services\CnpjService;
use JeffersonGoncalves\Cnpj\Support\CnpjSupport;

/**
 * @property string $cnpj
 * @property string|null $razao_social
 * @property string|null $nome_fantasia
 * @property string|null $uf
 * @property string|null $municipio
 * @property string|null $situacao_cadastral
 * @property array|null $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class Cnpj extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'cnpj';

    protected $primaryKey = 'cnpj';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public static function checkCnpj(?string $cnpj): bool
    {
        $result = self::findByCnpj($cnpj);

        return ! empty($result['cnpj']);
    }

    public static function findByCnpj(?string $cnpj): array
    {
        $cnpj = CnpjSupport::sanitize($cnpj);

        if ($cnpj === null) {
            return CnpjSupport::getResult();
        }

        $model = self::query()->find($cnpj);

        if ($model !== null && ! $model->isStale()) {
            return $model->toResult();
        }

        return CnpjService::findByCnpj($cnpj);
    }

    /**
     * Look up a CNPJ using only the local database, without ever calling the
     * external API (and therefore without writing to the database).
     */
    public static function findByCnpjInDatabase(?string $cnpj): array
    {
        $cnpj = CnpjSupport::sanitize($cnpj);

        if ($cnpj === null) {
            return CnpjSupport::getResult();
        }

        return self::query()->find($cnpj)?->toResult() ?? CnpjSupport::getResult();
    }

    public function isStale(): bool
    {
        $ttl = config('cnpj.cache_ttl');

        if ($ttl === null) {
            return false;
        }

        if ($this->updated_at === null) {
            return true;
        }

        return $this->updated_at->addSeconds((int) $ttl)->isPast();
    }

    public function toResult(): array
    {
        $default = CnpjSupport::getResult();

        return array_merge($default, array_filter($this->only(array_keys($default)), fn ($value) => $value !== null));
    }

    public static function updateByCnpj(string $cnpj, array $data): self
    {
        return self::updateOrCreate([
            'cnpj' => $cnpj,
        ], [
            'razao_social' => $data['razao_social'] ?? null,
            'nome_fantasia' => $data['nome_fantasia'] ?? null,
            'uf' => $data['uf'] ?? null,
            'municipio' => $data['municipio'] ?? null,
            'situacao_cadastral' => $data['descricao_situacao_cadastral'] ?? null,
            'data' => $data,
        ]);
    }
}
