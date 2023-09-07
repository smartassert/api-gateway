<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractCreateFrontendTokenTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class CreateFrontendTokenTest extends AbstractCreateFrontendTokenTest
{
    use GetClientAdapterTrait;
}
