<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractVerifyTokenTest;
use App\Tests\Functional\GetClientAdapterTrait;

class VerifyTokenTest extends AbstractVerifyTokenTest
{
    use GetClientAdapterTrait;
}
