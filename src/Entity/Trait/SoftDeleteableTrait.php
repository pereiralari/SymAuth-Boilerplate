<?php

namespace App\Entity\Trait;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait SoftDeleteableTrait
{
    #[ORM\Column(nullable: true)]
    private ?DateTimeInterface $deletedAt = null;

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function delete(): self
    {
        $this->deletedAt = new DateTimeImmutable();
        return $this;
    }
}