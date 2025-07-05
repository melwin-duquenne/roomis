<?php

namespace App\Dto\profil;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UserUpdateInput
{
    public ?string $prenom = null;
    public ?string $nom = null;
    public ?string $pseudo = null;
    public ?string  $image = null;
    public ?string $password = null;
}
