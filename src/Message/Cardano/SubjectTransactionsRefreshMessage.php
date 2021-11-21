<?php declare(strict_types=1);

namespace App\Message\Cardano;

class SubjectTransactionsRefreshMessage
{
    public function __construct(public int $txInitId)
    {
    }
}
