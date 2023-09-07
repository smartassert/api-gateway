<?php

declare(strict_types=1);

namespace App\Response;

interface BodyInterface
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
