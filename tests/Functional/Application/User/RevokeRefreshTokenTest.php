<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractRevokeRefreshTokenTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class RevokeRefreshTokenTest extends AbstractRevokeRefreshTokenTest
{
    use GetClientAdapterTrait;
}
