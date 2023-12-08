<?php

declare(strict_types=1);

namespace App\ServiceProxy;

readonly class ResponseHandlingSpecification
{
    /**
     * @param array<int<100, 599>> $bareResponseStatusCodes
     */
    public function __construct(
        private array $bareResponseStatusCodes = [],
    ) {
    }

    /**
     * @return array<int<100, 599>>
     */
    public function getBareResponseStatusCodes(): array
    {
        return $this->bareResponseStatusCodes;
    }
}
