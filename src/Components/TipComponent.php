<?php declare(strict_types=1);

namespace App\Components;

use App\Entity\Contracts\ContentInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('tip')]
class TipComponent
{
    public ContentInterface $subject;
    public PersistentCollection $transactions;
    public string $key = '';
    public FormView $mnemonicForm;
    public FormView $transactionForm;

    public function __construct()
    {
        $this->key = (string) rand(0, 200);
    }
}
