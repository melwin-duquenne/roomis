<?php
namespace App\ApiResource\login;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\login\LoginOutput;
use App\State\login\LoginProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/login',
            input: LoginInput::class,
            output: LoginOutput::class,
            processor: LoginProcessor::class,
            name: 'login'
        )
    ],
    paginationEnabled: false
)]
final class LoginInput
{
    public string $email;
    public string $password;
}