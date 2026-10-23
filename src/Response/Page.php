<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

/**
 * @template T
 */
final readonly class Page
{
    /**
     * @param list<T> $data
     */
    public function __construct(
        public array $data,
        public bool $hasMore,
        public int $totalCount,
        public int $limit,
        public int $offset,
    ) {
    }

    /**
     * @template R
     *
     * @param array<array-key, mixed> $body
     * @param callable(array<array-key, mixed>): R $map
     *
     * @return Page<R>
     */
    public static function fromArray(array $body, callable $map): self
    {
        $items = [];
        $rows = $body['data'] ?? [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            if (is_array($row)) {
                $items[] = $map($row);
            }
        }

        return new self(
            $items,
            (bool) ($body['hasMore'] ?? false),
            Fields::int($body, 'totalCount') ?? count($items),
            Fields::int($body, 'limit') ?? count($items),
            Fields::int($body, 'offset') ?? 0,
        );
    }
}
