<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractRefreshTokenTest;
use App\Tests\Functional\GetClientAdapterTrait;

class RefreshTokenTest extends AbstractRefreshTokenTest
{
    use GetClientAdapterTrait;
}
