<?php

declare(strict_types=1);

namespace App\Tests\Exception\Http;

use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends \Exception implements ClientExceptionInterface
{
}
