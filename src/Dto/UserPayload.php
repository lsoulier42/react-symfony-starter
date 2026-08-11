<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Input DTO for the User API (validated + deserialized via #[MapRequestPayload]).
 * The plain password is hashed by the controller; it is never stored as-is.
 */
final class UserPayload
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public string $plainPassword = '';

    /**
     * @var list<string>
     */
    #[Assert\All([new Assert\NotBlank()])]
    public array $roles = ['ROLE_USER'];
}
