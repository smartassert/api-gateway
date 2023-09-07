<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractVerifyFrontendTokenTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class VerifyFrontendTokenTest extends AbstractVerifyFrontendTokenTest
{
    use GetClientAdapterTrait;
}
