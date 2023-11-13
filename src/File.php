<?php

declare(strict_types=1);

namespace FileJet;

final class File implements FileInterface
{
    /** @var string */
    private string $identifier;
    /** @var null|string */
    private ?string $mutation;

    public function __construct(string $identifier, string $mutation = null)
    {
        $this->identifier = $identifier;
        $this->mutation = $mutation;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getMutation(): ?string
    {
        return $this->mutation;
    }
}
