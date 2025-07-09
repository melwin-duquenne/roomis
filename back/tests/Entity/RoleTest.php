<?php

namespace App\Tests\Entity;

use App\Entity\Role;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testRoleProperties(): void
    {
        $role = new Role();

        $role->setName('ROLE_ADMIN');

        $this->assertSame('ROLE_ADMIN', $role->getName());
    }

    public function testAddUserId(): void
    {
        $role = new Role();
        $user = $this->createMock(User::class);

        $this->assertCount(0, $role->getUserId());

        $role->addUserId($user);

        $this->assertCount(1, $role->getUserId());
        $this->assertTrue($role->getUserId()->contains($user));
    }

    public function testAddSameUserOnlyOnce(): void
    {
        $role = new Role();
        $user = $this->createMock(User::class);

        $role->addUserId($user);
        $role->addUserId($user); // ajouté deux fois, mais doit être unique

        $this->assertCount(1, $role->getUserId());
    }

    public function testRemoveUserId(): void
    {
        $role = new Role();
        $user = $this->createMock(User::class);

        $role->addUserId($user);
        $this->assertTrue($role->getUserId()->contains($user));

        $role->removeUserId($user);
        $this->assertFalse($role->getUserId()->contains($user));
    }
}
