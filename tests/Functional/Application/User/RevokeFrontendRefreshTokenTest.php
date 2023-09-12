<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractRevokeFrontendRefreshTokenTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class RevokeFrontendRefreshTokenTest extends AbstractRevokeFrontendRefreshTokenTest
{
    use GetClientAdapterTrait;
}
