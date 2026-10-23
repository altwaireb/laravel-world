<?php

namespace Altwaireb\World\Data;

use InvalidArgumentException;
use JsonMachine\Items;
use RuntimeException;

class WorldDataReader
{
    private const SUPPORTED_DATASETS = [
        'countries',
        'states',
        'cities',
    ];

    public function __construct(
        private readonly ?string $dataPath = null,
    ) {}

    /**
     * Read all items from a dataset into memory.
     *
     * @return array<int, object>
     *
     * @throws \JsonMachine\Exception\InvalidArgumentException
     */
    public function all(string $dataset): array
    {
        $path = $this->resolvePath($dataset);

        if (! is_file($path) || ! is_readable($path)) {
            return [];
        }

        if (filesize($path) === 0) {
            return [];
        }

        return iterator_to_array(
            $this->iterate($dataset),
            false,
        );
    }

    /**
     * Iterate over dataset items without loading the entire file.
     *
     * @return iterable<object>
     *
     * @throws \JsonMachine\Exception\InvalidArgumentException
     */
    public function iterate(string $dataset): iterable
    {
        $path = $this->resolvePath($dataset);

        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException(
                "Dataset file is missing or unreadable: {$path}",
            );
        }

        foreach (Items::fromFile($path) as $item) {
            yield $item;
        }
    }

    private function resolvePath(string $dataset): string
    {
        if (! in_array($dataset, self::SUPPORTED_DATASETS, true)) {
            throw new InvalidArgumentException(
                "Unsupported dataset: {$dataset}",
            );
        }

        $directory = $this->dataPath
            ?? dirname(__DIR__, 2).'/database/data';

        return rtrim($directory, '/\\')
            .DIRECTORY_SEPARATOR
            .$dataset
            .'.json';
    }
}
