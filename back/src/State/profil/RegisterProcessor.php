<?php
namespace App\State\profil;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\profil\RegisterInput;
use App\Dto\profil\RegisterOutput;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): RegisterOutput
    {
        if (!$data instanceof RegisterInput) {
            return new RegisterOutput(false, 'Invalid data');
        }
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            return new RegisterOutput(false, 'Email invalide');
        }
        if ($this->userRepository->findOneBy(['pseudo' => $data->pseudo])) {
            return new RegisterOutput(false, 'Pseudo déjà utilisé');
        }
        if ($this->userRepository->findOneBy(['email' => $data->email])) {
            return new RegisterOutput(false, 'Email déjà utilisé');
        }
        
        // if (
        //     strlen($data->password) < 8 ||
        //     !preg_match('/[A-Z]/', $data->password) ||
        //     !preg_match('/[a-z]/', $data->password) ||
        //     !preg_match('/[0-9]/', $data->password) ||
        //     !preg_match('/[\W_]/', $data->password)
        // ) {
        //     return new RegisterOutput(false, 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.');
        // }

        $user = new User();
        $user->setPseudo($data->pseudo);
        $user->setEmail($data->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data->password));


        // Ajout du rôle USER_ROLE
        $role = $this->roleRepository->findOneBy(['name' => 'USER_ROLE']);
        if ($role) {
            $user->addRole($role);
        }

        $this->em->persist($user);
        $this->em->flush();

        return new RegisterOutput(true, 'Inscription réussie');
    }
}