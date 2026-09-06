# Changelog

All notable changes to `laravel-world` will be documented in this file.

## Laravel 13 Support - 2026-09-06

### What's Changed

- Added Laravel 13 support.
- Updated Orchestra Testbench for Laravel 13 compatibility.
- Updated Pest and Pest plugins for Laravel 13 compatibility.
- Updated the GitHub Actions test matrix to include Laravel 13.
- Added Laravel 13 testing on PHP 8.3 and PHP 8.5.
- Added Windows testing for Laravel 13.
- Preserved compatibility with Laravel 10, 11, and 12.

### Testing

Laravel compatibility tests passed successfully across Laravel 10, 11, 12, and 13, including Windows compatibility testing.

## v1.1.0 - Laravel 12 Support - 2025-09-23

### Laravel 12 Support Added!

#### New Features

- **Laravel 12.* support** with PHP 8.3
- **Updated testing framework** to Pest v3.8.2 for Laravel 11/12
- **Complete GitHub Actions** support for Windows and Ubuntu

#### Fixes

- Fixed Windows PowerShell compatibility in GitHub Actions
- Resolved Pest version conflicts across Laravel versions

#### Supported Versions

- **Laravel 10.***: PHP 8.2, 8.3
- **Laravel 11.***: PHP 8.2, 8.3
- **Laravel 12.***: PHP 8.3

#### Installation

```bash
composer require altwaireb/laravel-world


```
#### Full Changelog

https://github.com/altwaireb/laravel-world/compare/v1.0.1...v1.1.0

## v1.0.1 - 2024-10-02

Add insert activations only activation countries.
