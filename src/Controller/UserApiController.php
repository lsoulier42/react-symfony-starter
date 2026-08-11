<?php

namespace App\Controller;

use App\Dto\PaginationDto;
use App\Dto\UserPayload;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Representative JSON CRUD on User, built with Symfony 8 native APIs:
 *  - #[MapRequestPayload] for validated deserialization of the input DTO
 *  - #[MapQueryString] + PaginationDto for validated pagination of the list
 *  - Serializer groups ("user:read") for the output
 *  - Doctrine param converter for {id}
 *
 * Protected by ROLE_ADMIN (see #[IsGranted] on the class).
 */
#[Route('/api/users', name: 'api_users_')]
#[IsGranted('ROLE_ADMIN')]
final class UserApiController extends AbstractApiController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)]
        PaginationDto $pagination,
    ): JsonResponse {
        $result = $userRepository->paginate($pagination->getPage(), $pagination->getLimit());

        return $this->jsonResponse($result, Response::HTTP_OK, ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->jsonResponse($user, Response::HTTP_OK, ['groups' => 'user:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] UserPayload $payload): JsonResponse
    {
        $user = new User();
        $user->setEmail($payload->email);
        $user->setRoles($payload->roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $payload->plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->created($user, ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(#[MapRequestPayload] UserPayload $payload, User $user): JsonResponse
    {
        $user->setEmail($payload->email);
        $user->setRoles($payload->roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $payload->plainPassword));

        $this->entityManager->flush();

        return $this->jsonResponse($user, Response::HTTP_OK, ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->noContent();
    }
}
