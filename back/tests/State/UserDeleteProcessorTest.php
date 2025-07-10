<?php

namespace App\Tests\State\profil;

use ApiPlatform\Metadata\GraphQl\Operation;
use App\Entity\User;
use App\State\profil\UserDeleteProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class UserDeleteProcessorTest extends TestCase
{
    public function testProcessDeletesAuthenticatedUser(): void
    {
        // Arrange
        $user = new User();
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('remove')->with($user);
        $entityManager->expects($this->once())->method('flush');

        $processor = new UserDeleteProcessor($security, $entityManager);

        // Act
        $result = $processor->process(null, $this->createMock(Operation::class));

        // Assert
        $this->assertNull($result);
    }

    public function testProcessThrowsWhenNoAuthenticatedUser(): void
    {
        // Arrange
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $processor = new UserDeleteProcessor($security, $entityManager);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Utilisateur non authentifié');

        // Act
        $processor->process(null, $this->createMock(Operation::class));
    }
}
