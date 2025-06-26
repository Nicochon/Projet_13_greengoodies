<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('product:read')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('product:read')]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups('product:read')]
    private ?float $price = null;

    #[ORM\Column(type: "text")]
    #[Groups('product:read')]
    private ?string $short_description;

    #[ORM\Column(type: "text")]
    #[Groups('product:read')]
    private ?string $full_description;

    #[ORM\Column(length: 255)]
    #[Groups('product:read')]
    private ?string $url_img = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->short_description;
    }

    public function setShortDescription(string $short_description): self
    {
        $this->short_description = $short_description;

        return $this;
    }

    public function getFullDescription(): ?string
    {
        return $this->full_description;
    }

    public function setFullDescription(string $full_description): self
    {
        $this->full_description = $full_description;

        return $this;
    }

    public function getUrlImg(): ?string
    {
        return $this->url_img;
    }

    public function setUrlImg(string $url_img): static
    {
        $this->url_img = $url_img;

        return $this;
    }
}
