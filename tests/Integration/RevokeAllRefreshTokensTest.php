<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractRevokeAllRefreshTokensTest;

class RevokeAllRefreshTokensTest extends AbstractRevokeAllRefreshTokensTest
{
    use GetClientAdapterTrait;
}
