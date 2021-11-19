<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CardanoTransactionDto
{
    // @todo string <hex> 40 characters
    #[Assert\NotBlank]
    public string $mnemonic;
    #[Assert\NotBlank]
    public string $walletAddress;
    public string $asset = 'ADA';
    #[Assert\NotBlank]
    public float $amount = 0;
}
