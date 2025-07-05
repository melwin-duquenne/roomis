<?php
namespace App\Dto\login;

final class LoginOutput
{
  public function __construct(
        public bool $success,
        public ?string $token,
        public ?string $pseudo = null,
        public ?string $image = null,
        public ?string $message = null
    ) {}
}