<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ServiceException;
use App\Security\ApiTokenProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class AuthorizationHeaderParametersTranslator implements EventSubscriberInterface
{
    public function __construct(
        private ApiTokenProvider $apiTokenProvider,
    ) {
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => [
                ['translateAuthorizationHeaderParameters', 0],
            ],
        ];
    }

    /**
     * @throws ServiceException
     */
    public function translateAuthorizationHeaderParameters(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $translateAuthorizationTo = trim((string) $request->headers->get('translate-authorization-to'));
        if ('api-token' !== $translateAuthorizationTo) {
            return;
        }

        $authorizationHeader = trim((string) $request->headers->get('authorization'));
        if ('' === $authorizationHeader) {
            return;
        }

        $scheme = null;
        $parameters = $authorizationHeader;
        if (str_contains($parameters, ' ')) {
            $authorizationComponents = explode(' ', $parameters, 2);
            $scheme = $authorizationComponents[0];
            $parameters = (string) ($authorizationComponents[1] ?? null);
        }

        if ('' === $parameters) {
            return;
        }

        $authorizationHeader = $this->apiTokenProvider->get($parameters);
        if (is_string($scheme)) {
            $authorizationHeader = $scheme . ' ' . $authorizationHeader;
        }

        $event->getRequest()->headers->set('authorization', $authorizationHeader);
    }
}
