<?php
namespace App\Dto\profil;

final class RegisterOutput
{
    public function __construct(
        public bool $success,
        public string $message
    ) {}
}