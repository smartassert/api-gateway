<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Exception\UndefinedServiceException;
use App\ServiceProxy\Service;
use App\ServiceProxy\ServiceCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class ServiceResolver implements ValueResolverInterface
{
    public function __construct(
        private ServiceCollection $serviceCollection,
    ) {}

    /**
     * @return iterable<Service>
     *
     * @throws UndefinedServiceException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Service::class !== $argument->getType()) {
            return [];
        }

        $name = $request->attributes->getString('serviceName');
        $service = $this->serviceCollection->get($name);
        if (null === $service) {
            $action = $request->attributes->getString('action');

            throw new UndefinedServiceException($name, $action);
        }

        return [$service];
    }
}
