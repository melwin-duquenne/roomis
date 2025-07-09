<?php

namespace App\Tests\State;

use App\ApiResource\profil\RegisterInput;
use App\Dto\profil\RegisterOutput;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\State\profil\RegisterProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterProcessorTest extends TestCase
{
    private function createProcessor(
        ?UserRepository $userRepo = null,
        ?RoleRepository $roleRepo = null,
        ?UserPasswordHasherInterface $hasher = null,
        ?EntityManagerInterface $em = null
    ): RegisterProcessor {
        return new RegisterProcessor(
            $em ?? $this->createMock(EntityManagerInterface::class),
            $userRepo ?? $this->createMock(UserRepository::class),
            $roleRepo ?? $this->createMock(RoleRepository::class),
            $hasher ?? $this->createMock(UserPasswordHasherInterface::class),
        );
    }

    public function testInvalidData(): void
    {
        $processor = $this->createProcessor();

        $result = $processor->process(new \stdClass(), $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertInstanceOf(RegisterOutput::class, $result);
        $this->assertFalse($result->success);
        $this->assertSame('Invalid data', $result->message);
    }

    public function testInvalidEmail(): void
    {
        $input = new RegisterInput();
        $input->pseudo = 'testuser';
        $input->email = 'not-an-email';
        $input->password = 'Password123!';

        $processor = $this->createProcessor();

        $result = $processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertFalse($result->success);
        $this->assertSame('Email invalide', $result->message);
    }

    public function testPseudoAlreadyUsed(): void
    {
        $input = new RegisterInput();
        $input->pseudo = 'testuser';
        $input->email = 'test@example.com';
        $input->password = 'Password123!';

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')
            ->willReturnCallback(fn($criteria) => isset($criteria['pseudo']) ? new User() : null);

        $processor = $this->createProcessor($userRepo);

        $result = $processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertFalse($result->success);
        $this->assertSame('Pseudo déjà utilisé', $result->message);
    }

    public function testEmailAlreadyUsed(): void
    {
        $input = new RegisterInput();
        $input->pseudo = 'testuser';
        $input->email = 'test@example.com';
        $input->password = 'Password123!';

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')
            ->willReturnCallback(fn($criteria) => isset($criteria['email']) ? new User() : null);

        $processor = $this->createProcessor($userRepo);

        $result = $processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertFalse($result->success);
        $this->assertSame('Email déjà utilisé', $result->message);
    }

    public function testSuccessfulRegistration(): void
    {
        $input = new RegisterInput();
        $input->pseudo = 'testuser';
        $input->email = 'test@example.com';
        $input->password = 'Password123!';

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->willReturn(null);

        $role = $this->createMock(Role::class);
        $roleRepo = $this->createMock(RoleRepository::class);
        $roleRepo->method('findOneBy')->willReturn($role);

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashedpassword');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $processor = $this->createProcessor($userRepo, $roleRepo, $hasher, $em);

        $result = $processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertTrue($result->success);
        $this->assertSame('Inscription réussie', $result->message);
    }
}
