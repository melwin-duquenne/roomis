<?php
// src/Controller/UploadUserImageController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;

class UploadUserImageController extends AbstractController
{
    private string $uploadDir;
    private Security $security;

    public function __construct(string $projectDir, Security $security)
    {
        $this->uploadDir = $projectDir . '/public/uploads/users';
        $this->security = $security;
    }

    #[Route('/api/upload/user-image', name: 'upload_user_image', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $image = $request->files->get('image');
        if (!$image) {
            return $this->json(['error' => 'Fichier image manquant'], Response::HTTP_BAD_REQUEST);
        }

        if (!$image->isValid()) {
            return $this->json(['error' => 'Fichier invalide'], Response::HTTP_BAD_REQUEST);
        }

        $originalExtension = $image->getClientOriginalExtension(); // pas besoin de symfony/mime ici
        $newFilename = bin2hex(random_bytes(10)) . '.' . $originalExtension;

        try {
            $image->move($this->uploadDir, $newFilename);
        } catch (FileException $e) {
            return $this->json(['error' => 'Erreur lors de l\'upload'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $imagePath = '/uploads/users/' . $newFilename;

        return $this->json(['imagePath' => $imagePath], Response::HTTP_CREATED);
    }
}
