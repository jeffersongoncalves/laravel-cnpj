<?php

namespace JeffersonGoncalves\Cnpj\Support;

abstract class CnpjSupport
{
    public static function getResult(): array
    {
        return [
            'cnpj' => '',
            'razao_social' => '',
            'nome_fantasia' => '',
            'uf' => '',
            'municipio' => '',
            'situacao_cadastral' => '',
            'data' => [],
        ];
    }

    /**
     * Normalize a raw CNPJ (numeric or the alphanumeric format introduced by
     * Receita Federal in July 2026) and validate its check digits. Returns
     * null when invalid, short-circuiting before any database or HTTP lookup.
     */
    public static function sanitize(?string $cnpj): ?string
    {
        if (empty($cnpj)) {
            return null;
        }

        $cnpj = strtoupper(str_replace(['.', '-', '/', ' '], '', $cnpj));

        // Numeric CNPJs stored as integers lose their leading zeros.
        if (ctype_digit($cnpj)) {
            $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);
        }

        if (! preg_match('/^[0-9A-Z]{12}\d{2}$/', $cnpj) || preg_match('/^(.)\1{13}$/', $cnpj)) {
            return null;
        }

        $base = substr($cnpj, 0, 12);
        $base .= self::checkDigit($base);
        $base .= self::checkDigit($base);

        return $base === $cnpj ? $cnpj : null;
    }

    public static function isValid(?string $cnpj): bool
    {
        return self::sanitize($cnpj) !== null;
    }

    public static function format(string $cnpj): string
    {
        return vsprintf('%s.%s.%s/%s-%s', [
            substr($cnpj, 0, 2), substr($cnpj, 2, 3), substr($cnpj, 5, 3), substr($cnpj, 8, 4), substr($cnpj, 12, 2),
        ]);
    }

    /**
     * Modulo 11 with weights 2..9 from right to left. Each character is
     * valued by its ASCII code minus 48, so digits keep their value and
     * letters A-Z become 17-42 (Receita Federal alphanumeric CNPJ spec).
     */
    private static function checkDigit(string $value): int
    {
        $sum = 0;
        $weight = 2;

        for ($i = strlen($value) - 1; $i >= 0; $i--) {
            $sum += (ord($value[$i]) - 48) * $weight;
            $weight = $weight === 9 ? 2 : $weight + 1;
        }

        $rest = $sum % 11;

        return $rest < 2 ? 0 : 11 - $rest;
    }
}
