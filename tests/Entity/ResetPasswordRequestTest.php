<?php

namespace App\Tests\Entity;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ResetPasswordRequestTest extends TestCase
{
    public function testGetters()
    {
        // Créer un utilisateur pour les besoins du test
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        $user->setFirstname('John');
        $user->setLastname('Doe');

        // Créer une instance de ResetPasswordRequest
        $expiresAt = new \DateTime('+1 hour');
        $selector = 'selector_string';
        $hashedToken = 'hashed_token_string';
        $resetPasswordRequest = new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        // Vérifier que les getters fonctionnent correctement
        $this->assertInstanceOf(ResetPasswordRequest::class, $resetPasswordRequest);
        $this->assertEquals($user, $resetPasswordRequest->getUser());
        $this->assertEquals($expiresAt, $resetPasswordRequest->getExpiresAt());
        $this->assertEquals($hashedToken, $resetPasswordRequest->getHashedToken());
    }
}
