<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

use Kongkodesoft\Asaas\Enum\PixInstructionPurpose;
use Kongkodesoft\Asaas\Enum\PixInstructionStatus;

final readonly class PixPaymentInstruction
{
    /** @param array<array-key, mixed> $raw */
    public function __construct(
        public string $id,
        public ?string $authorizationId,
        public ?\DateTimeImmutable $dueDate,
        public ?PixInstructionStatus $status,
        public ?string $paymentId,
        public ?PixInstructionPurpose $purpose,
        public ?string $refusalReason,
        public ?int $retryAttempt,
        public array $raw,
    ) {
    }

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            Fields::string($d, 'id') ?? '',
            Fields::string(Fields::map($d, 'authorization'), 'id'),
            Fields::date($d, 'dueDate'),
            Fields::enum($d, 'status', PixInstructionStatus::class),
            Fields::string($d, 'paymentId'),
            Fields::enum($d, 'purpose', PixInstructionPurpose::class),
            Fields::string($d, 'refusalReason'),
            Fields::int($d, 'retryAttempt'),
            $d,
        );
    }
}
