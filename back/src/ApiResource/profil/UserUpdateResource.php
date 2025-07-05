<?php

namespace App\ApiResource\profil;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Dto\profil\UserUpdateInput;
use App\State\profil\UserUpdateProcessor;

#[ApiResource(
    operations: [
        new Patch(
            uriTemplate: '/me/edit',
            input: UserUpdateInput::class,
            processor: UserUpdateProcessor::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            name: 'patch_me'
        )
    ]
)]
class UserUpdateResource
{
    // Pas besoin de propriétés, c’est juste un point d’entrée
}
