<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use App\Tests\Factory\UserFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminControllerTest extends AbstractWebTestCase
{
    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseRedirects('/login');
    }

    public function testAdminCanAccessDashboard(): void
    {
        $client = static::createClient();

        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        UserFactory::createOne([
            'email' => 'admin@example.com',
            'password' => $hasher->hashPassword(new User(), 'secret'),
            'roles' => ['ROLE_ADMIN'],
        ]);

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => 'admin@example.com',
            '_password' => 'secret',
        ]);
        $client->followRedirect();

        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }
}
