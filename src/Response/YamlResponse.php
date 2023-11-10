<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class YamlResponse extends SymfonyResponse
{
    public function __construct(string $content)
    {
        parent::__construct($content, 200, ['content-type' => 'application/yaml']);
    }
}
