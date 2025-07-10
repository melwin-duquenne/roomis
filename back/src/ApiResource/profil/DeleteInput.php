<?php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use App\State\profil\UserDeleteProcessor;

#[ApiResource(
    operations: [
        // ... autres opérations

        new Delete(
            uriTemplate: '/me/deleted',
            processor: UserDeleteProcessor::class,
            name: 'delete_user'
        )
    ]
)]
class DeleteInput
{
    // ...
}
