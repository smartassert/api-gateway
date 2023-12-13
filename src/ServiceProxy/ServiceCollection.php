<?php

declare(strict_types=1);

namespace App\ServiceProxy;

use App\Exception\UndefinedServiceException;

readonly class ServiceCollection
{
    /**
     * @param iterable<Service> $services
     */
    public function __construct(
        private iterable $services,
    ) {
    }

    /**
     * @throws UndefinedServiceException
     */
    public function get(string $name): Service
    {
        foreach ($this->services as $service) {
            if ($service->is($name)) {
                return $service;
            }
        }

        throw new UndefinedServiceException($name);
    }
}
