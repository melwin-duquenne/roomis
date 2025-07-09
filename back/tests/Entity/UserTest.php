<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Role;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserProperties(): void
    {
        $user = new User();

        $user->setPseudo('johndoe')
             ->setEmail('john@example.com')
             ->setPassword('securepassword')
             ->setNom('Doe')
             ->setPrenom('John')
             ->setImage('avatar.png');

        $this->assertSame('johndoe', $user->getPseudo());
        $this->assertSame('john@example.com', $user->getEmail());
        $this->assertSame('securepassword', $user->getPassword());
        $this->assertSame('Doe', $user->getNom());
        $this->assertSame('John', $user->getPrenom());
        $this->assertSame('avatar.png', $user->getImage());
        $this->assertSame('john@example.com', $user->getUserIdentifier());
    }

    public function testDefaultRoles(): void
    {
        $user = new User();

        $roles = $user->getRoles();

        $this->assertIsArray($roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(1, $roles);
    }

    public function testAddRole(): void
    {
        $user = new User();

        $role = $this->createMock(Role::class);
        $role->method('getName')->willReturn('ROLE_ADMIN');
        $role->expects($this->once())->method('addUserId')->with($user);

        $user->addRole($role);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(2, $roles);
    }

    public function testRemoveRole(): void
    {
        $user = new User();

        $role = $this->createMock(Role::class);
        $role->method('getName')->willReturn('ROLE_ADMIN');
        $role->expects($this->once())->method('addUserId')->with($user);
        $role->expects($this->once())->method('removeUserId')->with($user);

        $user->addRole($role);
        $user->removeRole($role);

        $roles = $user->getRoles();

        $this->assertNotContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(1, $roles);
    }

    public function testEraseCredentialsDoesNothing(): void
    {
        $user = new User();
        $this->assertNull($user->eraseCredentials());
    }
}
