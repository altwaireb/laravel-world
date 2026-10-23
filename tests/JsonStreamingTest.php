<?php

use Altwaireb\World\Data\WorldDataReader;
use JsonMachine\Exception\SyntaxErrorException;

/**
 * @throws JsonException
 */
function createWorldJsonFile(array $items): array
{
    $directory = sys_get_temp_dir()
        .DIRECTORY_SEPARATOR
        .'world-json-'.uniqid('', true);

    mkdir($directory, 0777, true);

    file_put_contents(
        $directory.DIRECTORY_SEPARATOR.'cities.json',
        json_encode($items, JSON_THROW_ON_ERROR),
    );

    return [$directory, new WorldDataReader($directory)];
}

function removeWorldJsonDirectory(string $directory): void
{
    foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
        @unlink($file);
    }

    @rmdir($directory);
}

it('streams dataset items in order', function () {
    [$directory, $reader] = createWorldJsonFile([
        ['id' => 1, 'name' => 'City 1'],
        ['id' => 2, 'name' => 'City 2'],
        ['id' => 3, 'name' => 'City 3'],
    ]);

    try {
        $items = iterator_to_array(
            $reader->iterate('cities'),
            false,
        );

        expect($items)->toHaveCount(3)
            ->and($items[0]->id)->toBe(1)
            ->and($items[1]->name)->toBe('City 2')
            ->and($items[2]->id)->toBe(3);
    } finally {
        removeWorldJsonDirectory($directory);
    }
});

it('reads all dataset items into an array', function () {
    [$directory, $reader] = createWorldJsonFile([
        ['id' => 1],
        ['id' => 2],
        ['id' => 3],
    ]);

    try {
        $items = $reader->all('cities');

        expect($items)->toHaveCount(3)
            ->and($items[0]->id)->toBe(1)
            ->and($items[2]->id)->toBe(3);
    } finally {
        removeWorldJsonDirectory($directory);
    }
});

it('rejects unsupported dataset names', function () {
    $reader = new WorldDataReader(sys_get_temp_dir());

    expect(fn () => $reader->all('unknown'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => iterator_to_array(
            $reader->iterate('unknown'),
            false,
        ))->toThrow(InvalidArgumentException::class);

});

it('returns an empty array when the dataset file is missing', function () {
    $directory = sys_get_temp_dir()
        .DIRECTORY_SEPARATOR
        .'world-json-'.uniqid('', true);

    mkdir($directory);

    try {
        $reader = new WorldDataReader($directory);

        expect($reader->all('cities'))->toBe([])
            ->and(fn () => iterator_to_array(
                $reader->iterate('cities'),
                false,
            ))->toThrow(RuntimeException::class);

    } finally {
        removeWorldJsonDirectory($directory);
    }
});

it('returns an empty array for an empty dataset file', function () {
    $directory = sys_get_temp_dir()
        .DIRECTORY_SEPARATOR
        .'world-json-'.uniqid('', true);

    mkdir($directory);
    file_put_contents(
        $directory.DIRECTORY_SEPARATOR.'cities.json',
        '',
    );

    try {
        $reader = new WorldDataReader($directory);

        expect($reader->all('cities'))->toBe([]);
    } finally {
        removeWorldJsonDirectory($directory);
    }
});

it('processes streamed items in batches', function () {
    $items = [];

    for ($id = 1; $id <= 1_000; $id++) {
        $items[] = ['id' => $id];
    }

    [$directory, $reader] = createWorldJsonFile($items);

    unset($items);

    try {
        $batchSize = 200;
        $batch = [];
        $batchSizes = [];
        $total = 0;

        foreach ($reader->iterate('cities') as $item) {
            $batch[] = $item;
            $total++;

            if (count($batch) === $batchSize) {
                $batchSizes[] = count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $batchSizes[] = count($batch);
        }

        expect($total)->toBe(1_000)
            ->and($batchSizes)->toBe([
                200, 200, 200, 200, 200,
            ]);
    } finally {
        removeWorldJsonDirectory($directory);
    }
});

it('throws an exception when the dataset contains invalid JSON', function () {
    $directory = sys_get_temp_dir()
        .DIRECTORY_SEPARATOR
        .'world-json-'.uniqid('', true);

    mkdir($directory);

    file_put_contents(
        $directory.DIRECTORY_SEPARATOR.'cities.json',
        '{"id": 1, invalid}',
    );

    try {
        $reader = new WorldDataReader($directory);

        expect(fn () => iterator_to_array(
            $reader->iterate('cities'),
            false,
        ))->toThrow(
            SyntaxErrorException::class,
        );
    } finally {
        removeWorldJsonDirectory($directory);
    }
});

it('throws an exception when loading a dataset with invalid JSON', function () {
    $directory = sys_get_temp_dir()
        .DIRECTORY_SEPARATOR
        .'world-json-'.uniqid('', true);

    mkdir($directory);

    file_put_contents(
        $directory.DIRECTORY_SEPARATOR.'cities.json',
        '{"id": 1, invalid}',
    );

    try {
        $reader = new WorldDataReader($directory);

        expect(fn () => $reader->all('cities'))
            ->toThrow(SyntaxErrorException::class);
    } finally {
        removeWorldJsonDirectory($directory);
    }
});
