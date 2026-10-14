# Changelog

All notable changes to `laravel-cnpj` will be documented in this file.

## 1.0.1 - 2026-10-13

- Tests: add a real alphanumeric CNPJ (`00.000.000/E08G-12`) to the validation dataset, confirmed against the live Minha Receita API

No changes to the package code.

## 1.0.0 - 2026-10-13

Initial release.

- CNPJ lookup through the [Minha Receita](https://minhareceita.org) API (public or self-hosted via `CNPJ_URL`)
- Support for the new alphanumeric CNPJ format, with check digit validation before any HTTP request
- Database cache with optional TTL; full API payload stored in the `data` column
- `Cnpj::findByCnpj()`, `Cnpj::checkCnpj()`, `Cnpj::findByCnpjInDatabase()`
- `CnpjSupport::sanitize()`, `isValid()` and `format()` helpers
- Laravel 12/13, PHP 8.2+
