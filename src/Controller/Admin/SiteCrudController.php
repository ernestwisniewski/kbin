<?php declare(strict_types = 1);

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Repository\SiteRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SiteCrudController extends AbstractCrudController
{
    public function __construct(private SiteRepository $repository)
    {

    }

    public static function getEntityFqcn(): string
    {
        return Site::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('domain'),
            TextField::new('title'),
            TextareaField::new('description'),
            BooleanField::new('enabled'),
            BooleanField::new('registrationOpen'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        if (count($this->repository->findAll())) {
            return $actions
                ->disable(Action::NEW, Action::DELETE);
        }

        return $actions;
    }
}
