<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDto
{
    #[Assert\GreaterThanOrEqual(1)]
    private int $page = 1;

    #[Assert\Range(min: 1, max: 100)]
    private int $limit = 10;

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }
}
