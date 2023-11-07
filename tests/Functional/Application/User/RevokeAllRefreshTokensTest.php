<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractRevokeAllRefreshTokensTest;
use App\Tests\Functional\GetClientAdapterTrait;

class RevokeAllRefreshTokensTest extends AbstractRevokeAllRefreshTokensTest
{
    use GetClientAdapterTrait;
}
