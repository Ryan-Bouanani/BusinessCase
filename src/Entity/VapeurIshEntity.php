<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait VapeurIshEntity
{

    #[
        Assert\NotBlank([
            'message' => "Veuillez remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 3,
            'max'=> 180,
            'minMessage' => 'Veuillez entrer un produict contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuillez entrer un produict contenant au maximum {{ limit }} caractères',
        ]),
    ]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $slug;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

}