<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Handler\UploadHandler;

#[Route('/media/bulk-upload')]
class MediaBulkUploadController extends AbstractController
{
    #[Route('/', name: 'admin_media_bulk_upload', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/media_import.html.twig');
    }

    #[Route('/upload', name: 'admin_media_bulk_upload_process', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $entityManager, UploadHandler $uploader): JsonResponse
    {
        $files = $request->files->all();

        if (!$files) {
            return new JsonResponse(['error' => 'Aucun fichier reçu.'], 400);
        }

        $uploadedMedia = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $media = new Media();
                $media->setFile($file);
                $media->setType(str_starts_with($file->getMimeType(), 'image/') ? 'img' : 'video');

                $uploader->upload($media, 'file'); // Utilisation de VichUploaderBundle
                $entityManager->persist($media);
                $uploadedMedia[] = [
                    'filePath' => '/uploads/media/' . $media->getFilePath(),
                    'type' => $media->getType()
                ];
            }
        }

        $entityManager->flush();

        return new JsonResponse(['success' => 'Fichiers importés avec succès.', 'media' => $uploadedMedia]);
    }
}
