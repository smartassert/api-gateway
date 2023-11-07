<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\Tests\Application\User\AbstractGetDefaultApiKeyTest;
use App\Tests\Integration\GetClientAdapterTrait;

class GetDefaultApiKeyTest extends AbstractGetDefaultApiKeyTest
{
    use GetClientAdapterTrait;
}
