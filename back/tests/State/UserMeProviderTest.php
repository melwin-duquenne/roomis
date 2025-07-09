<?php
namespace App\Tests\State\profil;

use App\Entity\User;
use App\State\profil\UserMeProvider;
use App\ApiResource\profil\UserMeRessource;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class UserMeProviderTest extends TestCase
{
    public function testProvideReturnsUserMeResource(): void
    {
        // Mock de l'utilisateur connecté
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(123);
        $user->method('getPseudo')->willReturn('testpseudo');
        $user->method('getEmail')->willReturn('test@example.com');
        $user->method('getPrenom')->willReturn('Jean');
        $user->method('getNom')->willReturn('Dupont');
        $user->method('getImage')->willReturn('image.png');

        // Mock de Security pour retourner cet utilisateur
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $provider = new UserMeProvider($security);

        $operation = $this->createMock(Operation::class);

        $result = $provider->provide($operation);

        $this->assertInstanceOf(UserMeRessource::class, $result);
        $this->assertSame(123, $result->id);
        $this->assertSame('testpseudo', $result->pseudo);
        $this->assertSame('test@example.com', $result->email);
        $this->assertSame('Jean', $result->prenom);
        $this->assertSame('Dupont', $result->nom);
        $this->assertSame('image.png', $result->image);
    }

    public function testProvideThrowsWhenUserNotAuthenticated(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('User not authenticated');

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $provider = new UserMeProvider($security);

        $operation = $this->createMock(Operation::class);

        $provider->provide($operation);
    }
}
