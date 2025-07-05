<?php
namespace App\State\login;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\login\LoginInput;
use App\Dto\login\LoginOutput;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class LoginProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
    ) {}

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): LoginOutput
    {
        if (!$data instanceof LoginInput) {
            return new LoginOutput(false, null, 'Invalid data');
        }

        $user = $this->userRepository->findOneBy(['email' => $data->email]);
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data->password)) {
            return new LoginOutput(false, null, 'Identifiants invalides');
        }

        $token = $this->jwtManager->create($user);

        return new LoginOutput(true, $token, $user->getPseudo(), $user->getImage() ?? 'none', 'Connexion réussie');
    }
}