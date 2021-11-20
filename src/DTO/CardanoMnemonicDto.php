<?php declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CardanoMnemonicDto
{
    // @todo string <hex> 40 characters
    #[Assert\NotBlank]
    public string $mnemonic;
}
