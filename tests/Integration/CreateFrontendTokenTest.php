<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractCreateFrontendTokenTest;

class CreateFrontendTokenTest extends AbstractCreateFrontendTokenTest
{
    use GetClientAdapterTrait;
}
