<?php

declare(strict_types=1);

namespace FileJet;

final class File implements FileInterface
{
    /** @var string */
    private $identifier;
    /** @var null|string */
    private $mutation;
    /** @var null|string */
    private $customName;

    public function __construct(string $identifier, ?string $mutation, ?string $customName)
    {
        $this->identifier = $identifier;
        $this->mutation = $mutation;
        $this->customName = $customName;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getMutation(): ?string
    {
        return $this->mutation;
    }

    public function getCustomName(): ?string 
    {
        return $this->customName;
    }
}
