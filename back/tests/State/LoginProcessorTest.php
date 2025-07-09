<?php

namespace App\Tests\State;

use App\ApiResource\login\LoginInput;
use App\Dto\login\LoginOutput;
use App\Entity\User;
use App\Repository\UserRepository;
use App\State\login\LoginProcessor;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginProcessorTest extends TestCase
{
    public function testInvalidData(): void
    {
        $processor = new LoginProcessor(
            $this->createMock(UserRepository::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(JWTTokenManagerInterface::class)
        );

        $result = $processor->process(new \stdClass(), $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertInstanceOf(LoginOutput::class, $result);
        $this->assertFalse($result->success);
        $this->assertSame('Invalid data', $result->message);
    }

    public function testInvalidCredentials(): void
    {
        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->willReturn(null); // simulate user not found

        $processor = new LoginProcessor(
            $userRepo,
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(JWTTokenManagerInterface::class)
        );

        $input = new LoginInput();
        $input->email = 'unknown@example.com';
        $input->password = 'wrong';

        $result = $processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertInstanceOf(LoginOutput::class, $result);
        $this->assertFalse($result->success);
        $this->assertSame('Identifiants invalides', $result->message);
    }

    public function testSuccessfulLogin(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPseudo')->willReturn('john_doe');
        $user->method('getImage')->willReturn('avatar.png');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->willReturn($user);

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('isPasswordValid')->willReturn(true);

        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManager->method('create')->willReturn('fake-jwt-token');

        $processor = new LoginProcessor($userRepo, $hasher, $jwtManager);

        $input = new LoginInput();
        $input->email = 'john@example.com';
        $input->password = 'validPassword';

        $result = $processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertInstanceOf(LoginOutput::class, $result);
        $this->assertTrue($result->success);
        $this->assertSame('fake-jwt-token', $result->token);
        $this->assertSame('john_doe', $result->pseudo);
        $this->assertSame('avatar.png', $result->image);
        $this->assertSame('Connexion réussie', $result->message);
    }
}
