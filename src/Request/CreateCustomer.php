<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Request;

final readonly class CreateCustomer implements RequestData
{
    /**
     * @param list<string>|null $additionalEmails
     */
    public function __construct(
        public string $name,
        public string $cpfCnpj,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $mobilePhone = null,
        public ?string $address = null,
        public ?string $addressNumber = null,
        public ?string $complement = null,
        public ?string $province = null,
        public ?string $postalCode = null,
        public ?string $externalReference = null,
        public ?bool $notificationDisabled = null,
        public ?array $additionalEmails = null,
        public ?string $municipalInscription = null,
        public ?string $stateInscription = null,
        public ?string $observations = null,
        public ?string $groupName = null,
        public ?string $company = null,
        public ?bool $foreignCustomer = null,
    ) {
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);
        $data['additionalEmails'] = $this->additionalEmails === null ? null : implode(',', $this->additionalEmails);

        return $data;
    }
}
