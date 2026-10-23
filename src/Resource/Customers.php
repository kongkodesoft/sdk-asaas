<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Resource;

use Kongkodesoft\Asaas\Request\CreateCustomer;
use Kongkodesoft\Asaas\Response\Customer;
use Kongkodesoft\Asaas\Response\DeleteResult;
use Kongkodesoft\Asaas\Response\Page;

final readonly class Customers extends AbstractResource
{
    public function create(CreateCustomer $request): Customer
    {
        return Customer::fromArray($this->transport->request('POST', 'customers', json: $request->toArray()));
    }

    public function get(string $id): Customer
    {
        return Customer::fromArray($this->transport->request('GET', 'customers/' . self::id($id)));
    }

    /** @param array<array-key, mixed> $fields */
    public function update(string $id, array $fields): Customer
    {
        return Customer::fromArray($this->transport->request('PUT', 'customers/' . self::id($id), json: $fields));
    }

    public function delete(string $id): DeleteResult
    {
        return DeleteResult::fromArray($this->transport->request('DELETE', 'customers/' . self::id($id)));
    }

    public function restore(string $id): Customer
    {
        return Customer::fromArray($this->transport->request('POST', 'customers/' . self::id($id) . '/restore'));
    }

    /**
     * @param array<array-key, mixed> $filters name, email, cpfCnpj, groupName, externalReference
     *
     * @return Page<Customer>
     */
    public function list(array $filters = [], int $offset = 0, int $limit = 10): Page
    {
        return $this->page('customers', $filters, $offset, $limit, Customer::fromArray(...));
    }

    /**
     * @param array<array-key, mixed> $filters
     *
     * @return \Generator<int, Customer>
     */
    public function iterate(array $filters = [], int $limit = 100): \Generator
    {
        return $this->iterateAll('customers', $filters, $limit, Customer::fromArray(...));
    }
}
