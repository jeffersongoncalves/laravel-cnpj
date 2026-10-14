<?php

use JeffersonGoncalves\Cnpj\Support\CnpjSupport;

describe('CNPJ sanitize and validation', function () {
    it('accepts valid numeric CNPJs in any format', function (string $input, string $expected) {
        expect(CnpjSupport::sanitize($input))->toBe($expected);
    })->with([
        ['33000167000101', '33000167000101'],
        ['33.000.167/0001-01', '33000167000101'],
        ['00.000.000/0001-91', '00000000000191'],
        ['191', '00000000000191'], // leading zeros lost (Banco do Brasil)
    ]);

    it('accepts valid alphanumeric CNPJs', function (string $input, string $expected) {
        expect(CnpjSupport::sanitize($input))->toBe($expected);
    })->with([
        ['12ABC34501DE35', '12ABC34501DE35'],
        ['12.ABC.345/01DE-35', '12ABC34501DE35'],
        ['12.abc.345/01de-35', '12ABC34501DE35'],
        ['00.000.000/E08G-12', '00000000E08G12'], // real: Banco do Brasil branch, issued 2026-07-31
    ]);

    it('rejects invalid CNPJs', function (?string $input) {
        expect(CnpjSupport::sanitize($input))->toBeNull()
            ->and(CnpjSupport::isValid($input))->toBeFalse();
    })->with([
        null,
        '',
        'invalid',
        '33000167000102', // wrong check digit
        '12ABC34501DE36', // wrong check digit
        '12ABC34501DEAB', // check digits must be numeric
        '00000000000000',
        '11111111111111',
        '330001670001010', // too long
        '12ABC34501DE3', // too short alphanumeric
    ]);

    it('formats a CNPJ', function () {
        expect(CnpjSupport::format('12ABC34501DE35'))->toBe('12.ABC.345/01DE-35')
            ->and(CnpjSupport::format('33000167000101'))->toBe('33.000.167/0001-01');
    });
});
