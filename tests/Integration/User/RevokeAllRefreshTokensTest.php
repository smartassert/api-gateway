<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\Tests\Application\User\AbstractRevokeAllRefreshTokensTest;
use App\Tests\Integration\GetClientAdapterTrait;

class RevokeAllRefreshTokensTest extends AbstractRevokeAllRefreshTokensTest
{
    use GetClientAdapterTrait;
}
