<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;

class CardanoTransactionDto
{
    // @todo string <hex> 40 characters
    public string $mnemonic;
    public string $walletId;
    public string $asset = 'ADA';
    public float $amount = 0;

    public function __construct(User $user)
    {
        $this->walletId = $user->cardanoWalletId;
    }
}
