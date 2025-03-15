<?php

namespace App\Controller\Admin;

use App\Entity\Sponsor;
use App\Form\Type\SponsorLinkType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichFileType;

class SponsorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sponsor::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),

            TextField::new('description', 'Description'),

            // Champ pour l'upload (visible dans le formulaire)
            TextField::new('file', 'Fichier')
                ->setFormType(VichFileType::class)
                ->setFormTypeOptions([
                    'required' => true,
                    'constraints' => [
                        new File([
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                            'mimeTypesMessage' => 'Seuls les fichiers JPEG, PNG et WEBP sont autorisés.',
                        ]),
                    ],
                ])
                ->onlyOnForms(),

            // Affichage de l'image dans la liste
            ImageField::new('filePath', 'Aperçu')
                ->setRequired(true)
                ->setBasePath('/uploads/media')
                ->onlyOnIndex(),

            TextField::new('alt', 'ALT'),

            // Gestion des liens
            CollectionField::new('links', 'Liens')
                ->setEntryType(SponsorLinkType::class)
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->onlyOnForms(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Sponsor) {
            return;
        }

        // Force la mise à jour en base de données
        $entityInstance->setUpdatedAt(new \DateTimeImmutable());

        parent::persistEntity($entityManager, $entityInstance);
    }

}
