<?php

namespace App\Controller\Admin;

use App\Entity\MediaFile;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Handler\UploadHandler;


class MediaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MediaFile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Image' => 'img',
                    'Vidéo' => 'video',
                ])
                ->renderExpanded(false)
                ->setRequired(true),

            // Champ pour l'upload (visible dans le formulaire)
            TextField::new('file', 'Fichier')
                ->setFormType(VichFileType::class)
                ->setFormTypeOptions([
                    'required' => $pageName === Crud::PAGE_NEW,
                    'constraints' => [
                        new File([
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'video/mp4'],
                            'mimeTypesMessage' => 'Seuls les fichiers JPEG, PNG, WEBP et MP4 sont autorisés.',
                        ]),
                    ],
                ])
                ->onlyOnForms(),

            // Affichage de l'image dans la liste
            ImageField::new('filePath', 'Aperçu')
                ->setBasePath('/uploads/media')
                ->onlyOnIndex(),

            TextField::new('author', 'Auteur (nom photographe)'),

            TextField::new('about', 'Description (nom pilote)'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof MediaFile) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        $entityInstance->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    #[Route('/media/import', name: 'admin_media_import', methods: ['GET'])]
    public function import(): Response
    {
        return $this->render('admin/media_import.html.twig');
    }

    #[Route('/media/import/upload', name: 'admin_media_import_process', methods: ['POST'])]
    public function importProcess(Request $request, EntityManagerInterface $entityManager, UploadHandler $uploader): JsonResponse
    {
        $files = $request->files->all();

        if (!$files) {
            return new JsonResponse(['error' => 'Aucun fichier reçu.'], 400);
        }

        $uploadedMedia = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $media = new MediaFile();
                $media->setFile($file);
                $media->setType(str_starts_with($file->getMimeType(), 'image/') ? 'img' : 'video');

                $uploader->upload($media, 'file');
                $entityManager->persist($media);

                $uploadedMedia[] = ['filePath' => '/uploads/media/' . $media->getFilePath(), 'type' => $media->getType()];
            }
        }

        $entityManager->flush();

        return new JsonResponse(['success' => 'Fichiers importés avec succès.', 'media' => $uploadedMedia]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $importAction = Action::new('importMedia', 'Import en Masse', 'fas fa-upload')
            ->linkToRoute('admin_media_import');

        return $actions->add(Crud::PAGE_INDEX, $importAction);
    }
}
