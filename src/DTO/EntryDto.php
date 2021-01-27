<?php declare(strict_types = 1);

namespace App\DTO;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Magazine;
use App\Entity\Entry;
use App\Entity\User;

class EntryDto
{
    private int $id;
    /**
     * @Assert\NotBlank()
     */
    private string $title;
    private ?string $body = null;
    private ?string $url = null;
    /**
     * @Assert\NotBlank()
     */
    private Magazine $magazine;
    private User $user;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (null === $this->getBody() && null === $this->getUrl()) {
            $this->buildViolation($context, 'url');
            $this->buildViolation($context, 'body');
        }
    }

    private function buildViolation(ExecutionContextInterface $context, $path)
    {
        $context->buildViolation('This value should not be blank.')
            ->atPath($path)
            ->addViolation();
    }

    public function getType(): string
    {
        if ($this->getBody()) {
            return Entry::ENTRY_TYPE_ARTICLE;
        }

        return Entry::ENTRY_TYPE_LINK;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getMagazine(): ?Magazine
    {
        return $this->magazine;
    }

    public function setMagazine(Magazine $magazine): void
    {
        $this->magazine = $magazine;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
