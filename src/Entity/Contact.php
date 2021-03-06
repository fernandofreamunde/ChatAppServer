<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactRepository")
 */
class Contact
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("list")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="contacts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("list")
     */
    private $owner;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("list")
     */
    private $contact;

    /**
     * @ORM\Column(type="string", length=25)
     * @Groups("list")
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getContact(): ?User
    {
        return $this->contact;
    }

    public function setContact(User $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
