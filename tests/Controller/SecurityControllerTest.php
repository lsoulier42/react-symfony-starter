<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use App\Tests\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends AbstractWebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[action*="login"]');
    }

    public function testLoginWithValidCredentialsRedirects(): void
    {
        $client = static::createClient();

        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        UserFactory::createOne([
            'email' => 'user@example.com',
            'password' => $hasher->hashPassword(new User(), 'secret'),
        ]);

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => 'user@example.com',
            '_password' => 'secret',
        ]);

        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
}
