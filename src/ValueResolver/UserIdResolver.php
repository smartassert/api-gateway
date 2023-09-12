<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Exception\EmptyUserIdException;
use App\Security\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UserIdResolver implements ValueResolverInterface
{
    /**
     * @return UserId[]
     *
     * @throws EmptyUserIdException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (UserId::class !== $argument->getType()) {
            return [];
        }

        $id = $request->request->get('id');
        if (!is_string($id) || '' === $id) {
            throw new EmptyUserIdException();
        }

        return [new UserId($id)];
    }
}
