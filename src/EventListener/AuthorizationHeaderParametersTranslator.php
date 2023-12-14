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
        if (!$request->headers->has('translate-authorization-to')) {
            return;
        }

        $translateAuthorizationTo = trim((string) $request->headers->get('translate-authorization-to'));
        if ('' === $translateAuthorizationTo) {
            return;
        }

        if ('api-token' !== $translateAuthorizationTo) {
            return;
        }

        if (!$request->headers->has('authorization')) {
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
            $scheme = $authorizationComponents[0] ?? null;
            $parameters = $authorizationComponents[1] ?? null;
        }

        if (!is_string($parameters) || '' === $parameters) {
            return;
        }

        $apiToken = $this->apiTokenProvider->get($parameters);
        $authorizationHeader = $apiToken;
        if (is_string($scheme)) {
            $authorizationHeader = $scheme . ' ' . $authorizationHeader;
        }

        $event->getRequest()->headers->set('authorization', $authorizationHeader);
    }
}
