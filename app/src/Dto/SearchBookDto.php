<?php

namespace App\Dto;

// #todo jms maybe
class SearchBookDto
{
    private ?string $term = null;
    private ?int $limit = null;
    private ?int $offset = null;

    /**
     * @return string|null
     */
    public function getTerm(): ?string
    {
        return $this->term;
    }

    /**
     * @param string $term
     * @return $this
     */
    public function setTerm(string $term): self
    {
        $this->term = $term;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int {
        return $this->limit ?: 50;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit(?int $limit): void {
        $this->limit = $limit;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int {
        return $this->offset ?: 0;
    }

    /**
     * @param int|null $offset
     */
    public function setOffset(?int $offset): void {
        $this->offset = $offset;
    }
}
