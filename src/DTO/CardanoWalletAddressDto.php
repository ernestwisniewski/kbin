<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;

class CardanoWalletAddressDto
{
    // @todo string <hex> 40 characters
    public ?string $walletId;

    public function __construct(User $user)
    {
        $this->walletId = $user->cardanoWalletId;
    }
}
