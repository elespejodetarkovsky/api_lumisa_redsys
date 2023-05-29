<?php

namespace App\Controller\Admin;

use App\Entity\DsResponse;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DsResponseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DsResponse::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
