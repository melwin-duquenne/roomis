<?php
// src/ApiResource/UserMe.php
namespace App\ApiResource\profil;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\profil\UserMe;
use App\State\profil\UserMeProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/me',
            provider: UserMeProvider::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            name: 'get_me',
        ),
    ],
    // inutile ici de mettre shortName, car ce n'est pas une entité doctrine
)]
final class UserMeRessource
{
    public ?int $id = null;
    public ?string $pseudo = null;
    public ?string $email = null;
    public ?string $prenom = null;
    public ?string $nom = null;
    public ?string $image = null;
}
