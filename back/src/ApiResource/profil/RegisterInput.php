<?php
namespace App\ApiResource\profil;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\profil\RegisterOutput;
use App\State\profil\RegisterProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/register',
            input: RegisterInput::class,
            output: RegisterOutput::class,
            processor: RegisterProcessor::class,
            name: 'register'
        )
    ],
    paginationEnabled: false
)]
final class RegisterInput
{
    public string $pseudo;
    public string $email;
    public string $password;
}