<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractRefreshTokenTest;

class RefreshTokenTest extends AbstractRefreshTokenTest
{
    use GetClientAdapterTrait;
}
