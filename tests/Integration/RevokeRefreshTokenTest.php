<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractRevokeRefreshTokenTest;

class RevokeRefreshTokenTest extends AbstractRevokeRefreshTokenTest
{
    use GetClientAdapterTrait;
}
