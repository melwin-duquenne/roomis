<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Operation;
use App\Dto\profil\UserUpdateInput;
use App\Entity\User;
use App\Repository\UserRepository;
use App\State\profil\UserUpdateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserUpdateProcessorTest extends TestCase
{
    private function createProcessor(User $currentUser, ?UserRepository $userRepoMock = null): UserUpdateProcessor
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($currentUser);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($currentUser);
        $em->expects($this->once())->method('flush');

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed-password');

        $userRepoMock = $userRepoMock ?? $this->createMock(UserRepository::class);

        return new UserUpdateProcessor($security, $em, $hasher, $userRepoMock);
    }

    public function testSuccessfulUpdate(): void
    {
        $currentUser = new User();
        $currentUser->setPseudo('oldPseudo');
        $input = new UserUpdateInput();
        $input->nom = 'Doe';
        $input->prenom = 'John';
        $input->pseudo = 'newPseudo';
        $input->password = 'NewPassword123!';

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->willReturn(null); // Pseudo not used

        $processor = $this->createProcessor($currentUser, $userRepo);
        $result = $processor->process($input, $this->createMock(\ApiPlatform\Metadata\Operation::class));

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame('Doe', $result->getNom());
        $this->assertSame('John', $result->getPrenom());
        $this->assertSame('newPseudo', $result->getPseudo());
        $this->assertSame('hashed-password', $result->getPassword());
    }

    public function testPseudoAlreadyTaken(): void
    {
        $user = new User();
        $user->setPseudo('currentuser');

        $existingUser = new User(); // simulons un autre utilisateur avec le même pseudo

        $input = new UserUpdateInput();
        $input->pseudo = 'existinguser';

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->with(['pseudo' => 'existinguser'])->willReturn($existingUser);

        $em = $this->createMock(EntityManagerInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $processor = new UserUpdateProcessor($security, $em, $hasher, $userRepo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pseudo déjà utilisé.');

        $processor->process($input, $this->createMock(Operation::class));
    }

}
