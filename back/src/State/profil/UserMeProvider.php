<?php
namespace App\State\profil;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use App\ApiResource\profil\UserMeRessource;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

final class UserMeProvider implements ProviderInterface
{
    public function __construct(private Security $security) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserMeRessource|null
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            throw new AccessDeniedException('User not authenticated');
        }

        $dto = new UserMeRessource();
        $dto->id = $user->getId();
        $dto->pseudo = $user->getPseudo();
        $dto->email = $user->getEmail();
        $dto->prenom = $user->getPrenom();
        $dto->nom = $user->getNom();
        $dto->image = method_exists($user, 'getImage') ? $user->getImage() : null;

        return $dto;
    }
}
