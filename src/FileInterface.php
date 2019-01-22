<?php

declare(strict_types=1);

namespace FileJet;

interface FileInterface
{
    public function getIdentifier(): string;

    public function getMutation(): ?string;
}
