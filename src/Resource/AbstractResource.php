<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Resource;

use Kongkodesoft\Asaas\Http\Transport;
use Kongkodesoft\Asaas\Request\RequestData;
use Kongkodesoft\Asaas\Response\Page;

abstract readonly class AbstractResource
{
    public function __construct(protected Transport $transport)
    {
    }

    protected static function id(string $id): string
    {
        if ($id === '') {
            throw new \InvalidArgumentException('O identificador não pode ser vazio.');
        }

        return rawurlencode($id);
    }

    /**
     * @template T
     *
     * @param array<array-key, mixed> $filters
     * @param callable(array<array-key, mixed>): T $map
     *
     * @return Page<T>
     */
    protected function page(string $path, array $filters, int $offset, int $limit, callable $map): Page
    {
        if ($limit < 1 || $limit > 100) {
            throw new \InvalidArgumentException('limit deve estar entre 1 e 100.');
        }
        if ($offset < 0) {
            throw new \InvalidArgumentException('offset não pode ser negativo.');
        }
        $body = $this->transport->request('GET', $path, [...$filters, 'offset' => $offset, 'limit' => $limit]);

        return Page::fromArray($body, $map);
    }

    /**
     * Percorre todas as páginas lazily. Respeite o limite de 50 GETs simultâneos ao paralelizar.
     *
     * @template T
     *
     * @param array<array-key, mixed> $filters
     * @param callable(array<array-key, mixed>): T $map
     *
     * @return \Generator<int, T>
     */
    protected function iterateAll(string $path, array $filters, int $limit, callable $map): \Generator
    {
        $offset = 0;
        do {
            $page = $this->page($path, $filters, $offset, $limit, $map);
            yield from $page->data;
            $offset += $limit;
        } while ($page->hasMore && $page->data !== []);
    }

    /**
     * @param RequestData|array<array-key, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    protected static function body(RequestData|array $data): array
    {
        return $data instanceof RequestData ? $data->toArray() : $data;
    }
}
