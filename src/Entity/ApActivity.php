<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Repository\ApActivityRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: ApActivityRepository::class)]
class ApActivity
{
    use CreatedAtTrait {
        CreatedAtTrait::__construct as createdAtTraitConstruct;
    }

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'awards')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public User $user;

    #[ManyToOne(targetEntity: Magazine::class, inversedBy: 'awards')]
    #[JoinColumn(nullable: true, onDelete: 'CASCADE')]
    public ?Magazine $magazine;

    #[Column(type: 'string', nullable: false)]
    public int $subjectId;

    #[Column(type: 'string', nullable: false)]
    public string $type;

    #[Column(type: 'json', nullable: true, options: ['jsonb' => true])]
    public string $body;
}
