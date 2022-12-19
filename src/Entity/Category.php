<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    use VapeurIshEntity;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'categoryChild')]
    private ?self $categoryParent = null;

    #[ORM\OneToMany(mappedBy: 'categoryParent', targetEntity: self::class, cascade: [
        'remove'
    ])]
    private Collection $categoryChildren;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class)]
    private Collection $products;

    public function __construct()
    {
        $this->categoryChildren = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function PrePersist() {
        $this->slug = (new Slugify())->slugify($this->name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryParent(): ?self
    {
        return $this->categoryParent;
    }

    public function setCategoryParent(?self $categoryParent): self
    {
        $this->categoryParent = $categoryParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getCategoryChildren(): Collection
    {
        return $this->categoryChildren;
    }

    public function addCategoryChildren(self $categoryChildren): self
    {
        if (!$this->categoryChildren->contains($categoryChildren)) {
            $this->categoryChildren->add($categoryChildren);
            $categoryChildren->setCategoryParent($this);
        }

        return $this;
    }

    public function removeCategoryChildren(self $categoryChildren): self
    {
        if ($this->categoryChildren->removeElement($categoryChildren)) {
            // set the owning side to null (unless already changed)
            if ($categoryChildren->getCategoryParent() === $this) {
                $categoryChildren->setCategoryParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }
}
