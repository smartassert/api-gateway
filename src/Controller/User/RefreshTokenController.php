<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Exception\ServiceException;
use App\Exception\UndefinedServiceException;
use App\ServiceProxy\ServiceCollection;
use App\ServiceProxy\ServiceProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

readonly class RefreshTokenController
{
    public function __construct(
        private ServiceProxy $serviceProxy,
        private ServiceCollection $serviceCollection,
    ) {
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route('/user/refresh-token/revoke-all-for-user', name: 'user_revoke_all_refresh_token', methods: ['POST'])]
    public function revokeAllForUser(Request $request): Response
    {
        return $this->serviceProxy->proxy($this->serviceCollection->get('user'), $request);
    }

    /**
     * @throws ServiceException
     * @throws UndefinedServiceException
     */
    #[Route('/user/refresh-token/revoke ', name: 'user_revoke_refresh_token', methods: ['POST'])]
    public function revoke(Request $request): Response
    {
        return $this->serviceProxy->proxy($this->serviceCollection->get('user'), $request);
    }
}
