<?php

namespace App\Tests\Controller;

use App\Tests\AbstractWebTestCase;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class UserApiControllerTest extends AbstractWebTestCase
{
    private function loginAsAdmin(KernelBrowser $client): void
    {
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $realUser = UserFactory::repository()->find($admin->getId());
        \assert($realUser !== null);
        $client->loginUser($realUser, 'main');
    }

    public function testIndexDeniesAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users');

        $this->assertResponseRedirects('/login');
    }

    public function testIndexReturnsUsersWithEmailButNoPassword(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $user = UserFactory::createOne(['email' => 'listed@example.com']);

        $client->request('GET', '/api/users?limit=100');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('items', $data);
        $items = $data['items'];
        $emails = array_column($items, 'email');
        $this->assertContains('listed@example.com', $emails);
        // password must never be serialized
        $this->assertArrayNotHasKey('password', $items[0] ?? []);

        foreach (['total', 'page', 'limit', 'pages'] as $key) {
            $this->assertArrayHasKey($key, $data);
        }
    }

    public function testIndexHonorsPagination(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        UserFactory::createMany(3, ['roles' => ['ROLE_USER']]);

        $client->request('GET', '/api/users?page=1&limit=2');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(2, $data['limit']);
        $this->assertCount(2, $data['items']);
        $this->assertGreaterThanOrEqual(3, $data['total']);
    }

    public function testIndexInvalidPaginationReturns422(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/users?limit=0');

        $this->assertResponseIsUnprocessable();
    }

    public function testShowReturnsUserWithoutPassword(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $user = UserFactory::createOne(['email' => 'reader@example.com']);
        $client->request('GET', '/api/users/' . $user->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('reader@example.com', $data['email']);
        $this->assertSame($user->getId(), $data['id']);
        $this->assertArrayNotHasKey('password', $data);
    }

    public function testShowUnknownUserReturns404(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/api/users/999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreatePersistsUserAndReturns201WithoutPassword(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'new@example.com',
                'plainPassword' => 'secret123',
                'roles' => ['ROLE_ADMIN'],
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('new@example.com', $data['email']);
        $this->assertArrayNotHasKey('password', $data);

        $this->assertNotNull(UserFactory::repository()->findOneBy(['email' => 'new@example.com']));
    }

    public function testCreateInvalidPayloadReturns422(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode(['email' => 'not-an-email', 'plainPassword' => 'short']),
        );

        $this->assertResponseIsUnprocessable();
    }

    public function testUpdateChangesEmailAndReturns200(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $user = UserFactory::createOne(['email' => 'before@example.com']);
        $client->request(
            'PUT',
            '/api/users/' . $user->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => 'after@example.com',
                'plainPassword' => 'newsecret1',
                'roles' => ['ROLE_USER'],
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $updated = UserFactory::repository()->find($user->getId());
        $this->assertNotNull($updated);
        $this->assertSame('after@example.com', $updated->getEmail());
    }

    public function testDeleteReturns204AndThen404(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $user = UserFactory::createOne(['email' => 'todelete@example.com']);
        $id = $user->getId();
        $client->request('DELETE', '/api/users/' . $id);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request('GET', '/api/users/' . $id);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
