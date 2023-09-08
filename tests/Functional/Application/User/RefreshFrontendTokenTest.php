<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractRefreshFrontendTokenTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class RefreshFrontendTokenTest extends AbstractRefreshFrontendTokenTest
{
    use GetClientAdapterTrait;
}
