<?php
namespace App\State\profil;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class UserDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?User
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            throw new \RuntimeException('Utilisateur non authentifié');
        }

        $this->em->remove($user);
        $this->em->flush();

        return null; // ou retourne une réponse custom si tu veux
    }
}