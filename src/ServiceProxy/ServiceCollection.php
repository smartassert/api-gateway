<?php

declare(strict_types=1);

namespace App\ServiceProxy;

readonly class ServiceCollection
{
    /**
     * @param iterable<Service> $services
     */
    public function __construct(
        private iterable $services,
    ) {}

    public function get(string $name): ?Service
    {
        foreach ($this->services as $service) {
            if ($service->is($name)) {
                return $service;
            }
        }

        return null;
    }
}
