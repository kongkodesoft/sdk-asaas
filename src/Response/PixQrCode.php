<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Response;

final readonly class PixQrCode
{
    public function __construct(
        /** Imagem do QR Code em base64. */
        public string $encodedImage,
        /** Pix copia e cola. */
        public string $payload,
        public ?\DateTimeImmutable $expirationDate,
        public ?string $description,
    ) {
    }

    /** @param array<array-key, mixed> $d */
    public static function fromArray(array $d): self
    {
        return new self(
            Fields::string($d, 'encodedImage') ?? '',
            Fields::string($d, 'payload') ?? '',
            Fields::date($d, 'expirationDate'),
            Fields::string($d, 'description'),
        );
    }
}
