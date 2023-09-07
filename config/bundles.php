<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    SmartAssert\TestAuthenticationProviderBundle\TestAuthenticationProviderBundle::class => [
        'dev' => true,
        'test' => true
    ],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
];
