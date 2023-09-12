<?php

declare(strict_types=1);

namespace App\Response;

readonly class EmptyBody implements BodyInterface
{
    /**
     * @return array{}
     */
    public function toArray(): array
    {
        return [];
    }
}
