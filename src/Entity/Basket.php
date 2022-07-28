<?php

namespace App\Entity;

use App\Repository\BasketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasketRepository::class)]
class Basket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreated = null;

    #[ORM\OneToMany(mappedBy: 'basket', targetEntity: ContentShoppingCart::class)]
    private Collection $contentShoppingCarts;

    public function __construct()
    {
        $this->contentShoppingCarts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return Collection<int, ContentShoppingCart>
     */
    public function getContentShoppingCarts(): Collection
    {
        return $this->contentShoppingCarts;
    }

    public function addContentShoppingCart(ContentShoppingCart $contentShoppingCart): self
    {
        if (!$this->contentShoppingCarts->contains($contentShoppingCart)) {
            $this->contentShoppingCarts->add($contentShoppingCart);
            $contentShoppingCart->setBasket($this);
        }

        return $this;
    }

    public function removeContentShoppingCart(ContentShoppingCart $contentShoppingCart): self
    {
        if ($this->contentShoppingCarts->removeElement($contentShoppingCart)) {
            // set the owning side to null (unless already changed)
            if ($contentShoppingCart->getBasket() === $this) {
                $contentShoppingCart->setBasket(null);
            }
        }

        return $this;
    }
}
