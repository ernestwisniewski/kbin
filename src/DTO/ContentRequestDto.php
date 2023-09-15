<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Entry;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;

#[OA\Schema()]
class ContentRequestDto extends ImageUploadDto
{
    #[Groups([
        Entry::ENTRY_TYPE_ARTICLE,
        Entry::ENTRY_TYPE_LINK,
        'post',
        'comment',
    ])]
    #[OA\Property(example: 'We can post cat pics from the API now! What are you going to do with this power?')]
    public ?string $body = null;
    #[Groups(['common'])]
    #[OA\Property(example: 'en', nullable: true, minLength: 2, maxLength: 3)]
    public ?string $lang = null;
    #[Groups(['common'])]
    #[OA\Property(example: false)]
    public bool $isAdult = false;
}
