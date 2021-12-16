<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CardanoTransactionDto
{
    #[Assert\NotBlank]
    public ?string $walletAddress = null;
    #[Assert\NotBlank]
    public string $asset = 'ADA';
    #[Assert\NotBlank]
    public float $amount = 0;
}
