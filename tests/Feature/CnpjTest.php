<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JeffersonGoncalves\Cnpj\Models\Cnpj;
use JeffersonGoncalves\Cnpj\Support\CnpjSupport;

function minhaReceitaPayload(string $cnpj = '33000167000101', string $razaoSocial = 'EMPRESA TESTE LTDA'): array
{
    return [
        'cnpj' => $cnpj,
        'razao_social' => $razaoSocial,
        'nome_fantasia' => 'TESTE',
        'uf' => 'SP',
        'municipio' => 'SAO PAULO',
        'descricao_situacao_cadastral' => 'ATIVA',
        'cnae_fiscal' => 6201501,
        'qsa' => [['nome_socio' => 'FULANO DE TAL']],
    ];
}

describe('findByCnpj', function () {
    it('fetches from Minha Receita and stores the record', function () {
        Http::fake(['https://minhareceita.org/33000167000101' => Http::response(minhaReceitaPayload())]);

        $result = Cnpj::findByCnpj('33.000.167/0001-01');

        expect($result['cnpj'])->toBe('33000167000101')
            ->and($result['razao_social'])->toBe('EMPRESA TESTE LTDA')
            ->and($result['situacao_cadastral'])->toBe('ATIVA')
            ->and($result['data']['qsa'][0]['nome_socio'])->toBe('FULANO DE TAL')
            ->and(Cnpj::find('33000167000101')->data['cnae_fiscal'])->toBe(6201501);
    });

    it('fetches alphanumeric CNPJs', function () {
        Http::fake(['https://minhareceita.org/12ABC34501DE35' => Http::response(minhaReceitaPayload('12ABC34501DE35'))]);

        expect(Cnpj::findByCnpj('12.abc.345/01de-35')['cnpj'])->toBe('12ABC34501DE35');
    });

    it('serves stored records without HTTP', function () {
        Http::fake(['*' => Http::response(minhaReceitaPayload())]);
        Cnpj::findByCnpj('33000167000101');

        $result = Cnpj::findByCnpj('33000167000101');

        expect($result['razao_social'])->toBe('EMPRESA TESTE LTDA');
        Http::assertSentCount(1);
    });

    it('returns empty result without caching on 404, 503 and connection errors', function ($response) {
        Http::fake(['*' => $response]);

        expect(Cnpj::findByCnpj('33000167000101'))->toBe(CnpjSupport::getResult())
            ->and(Cnpj::count())->toBe(0);
    })->with([
        'not found' => fn () => Http::response(['message' => 'CNPJ 33.000.167/0001-01 não encontrado.'], 404),
        'unavailable' => fn () => Http::response(['message' => 'Serviço temporariamente indisponível, tente novamente.'], 503),
        'connection' => fn () => fn () => throw new ConnectionException('timeout'),
    ]);

    it('does not call the API for invalid CNPJs', function () {
        Http::fake();

        expect(Cnpj::checkCnpj('33000167000102'))->toBeFalse()
            ->and(Cnpj::checkCnpj('invalid'))->toBeFalse()
            ->and(Cnpj::checkCnpj(null))->toBeFalse();

        Http::assertNothingSent();
    });

    it('uses the configured url', function () {
        config()->set('cnpj.url', 'https://receita.example.com/');
        Http::fake(['https://receita.example.com/33000167000101' => Http::response(minhaReceitaPayload())]);

        expect(Cnpj::checkCnpj('33000167000101'))->toBeTrue();
    });
});

describe('findByCnpjInDatabase', function () {
    it('never calls the API', function () {
        Http::fake();
        Cnpj::updateByCnpj('33000167000101', minhaReceitaPayload());

        expect(Cnpj::findByCnpjInDatabase('33000167000101')['uf'])->toBe('SP')
            ->and(Cnpj::findByCnpjInDatabase('00000000000191'))->toBe(CnpjSupport::getResult());

        Http::assertNothingSent();
    });
});

describe('cache TTL', function () {
    it('re-fetches stale records', function () {
        config()->set('cnpj.cache_ttl', 60);
        Cnpj::updateByCnpj('33000167000101', minhaReceitaPayload(razaoSocial: 'OLD'));
        Cnpj::query()->update(['updated_at' => now()->subSeconds(120)]);
        Http::fake(['*' => Http::response(minhaReceitaPayload())]);

        expect(Cnpj::findByCnpj('33000167000101')['razao_social'])->toBe('EMPRESA TESTE LTDA');
    });

    it('serves fresh records from the database', function () {
        config()->set('cnpj.cache_ttl', 60);
        Cnpj::updateByCnpj('33000167000101', minhaReceitaPayload());
        Http::fake();

        expect(Cnpj::findByCnpj('33000167000101')['razao_social'])->toBe('EMPRESA TESTE LTDA');
        Http::assertNothingSent();
    });
});
