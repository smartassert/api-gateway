<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractGetDefaultApiKeyTest;
use App\Tests\Functional\GetClientAdapterTrait;

class GetDefaultApiKeyTest extends AbstractGetDefaultApiKeyTest
{
    use GetClientAdapterTrait;
}
