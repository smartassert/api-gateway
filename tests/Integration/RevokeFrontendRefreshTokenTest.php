<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractRevokeFrontendRefreshTokenTest;

class RevokeFrontendRefreshTokenTest extends AbstractRevokeFrontendRefreshTokenTest
{
    use GetClientAdapterTrait;
}
