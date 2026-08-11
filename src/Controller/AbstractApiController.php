<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Minimal base for JSON REST APIs, using Symfony's native API building blocks
 * (no API Platform required):
 *  - deserialize/validate the request body with #[MapRequestPayload]
 *  - bind query params with #[MapQueryString]
 *  - serialize the response with the Serializer via $this->json()
 *
 * Concrete controllers should extend this and use the helpers below.
 */
abstract class AbstractApiController extends AbstractBaseController
{
    /**
     * Serialize $data to JSON. Pass $context (e.g. ['groups' => [...]]) as needed.
     *
     * @param array<string, mixed> $context
     */
    protected function jsonResponse(mixed $data, int $status = Response::HTTP_OK, array $context = []): JsonResponse
    {
        return $this->json($data, $status, [], $context);
    }

    /**
     * 201 Created.
     *
     * @param array<string, mixed> $context
     */
    protected function created(mixed $data = null, array $context = []): JsonResponse
    {
        return $this->jsonResponse($data, Response::HTTP_CREATED, $context);
    }

    /**
     * 204 No Content.
     */
    protected function noContent(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * 422 Unprocessable Entity with a { "errors": { field: message } } body.
     *
     * @param array<string, string> $errors
     */
    protected function unprocessable(array $errors): JsonResponse
    {
        return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
