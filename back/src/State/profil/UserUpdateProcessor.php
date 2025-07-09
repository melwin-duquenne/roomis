<?php
namespace App\State\profil;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\profil\UserUpdateInput;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UserUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        /** @var User $user */
        $user = $this->security->getUser();
        /** @var UserUpdateInput $input */
        $input = $data;

        if ($input->prenom !== null) {
            $user->setPrenom($input->prenom);
        }

        if ($input->nom !== null) {
            $user->setNom($input->nom);
        }

        if ($input->pseudo !== null && $input->pseudo !== $user->getPseudo()) {
            $existingUser = $this->userRepository->findOneBy(['pseudo' => $input->pseudo]);

            if ($existingUser && $existingUser !== $user) {
                throw new \RuntimeException('Pseudo déjà utilisé.');
            }

            $user->setPseudo($input->pseudo);
        }

        if ($input->image !== null) {
            $user->setImage($input->image);
        }

        if ($input->password !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $input->password);
            $user->setPassword($hashedPassword);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

}
