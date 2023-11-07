<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\Tests\Application\User\AbstractRevokeRefreshTokenTest;
use App\Tests\Integration\GetClientAdapterTrait;

class RevokeRefreshTokenTest extends AbstractRevokeRefreshTokenTest
{
    use GetClientAdapterTrait;
}
