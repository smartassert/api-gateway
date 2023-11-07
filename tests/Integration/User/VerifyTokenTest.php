<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\Tests\Application\User\AbstractVerifyTokenTest;
use App\Tests\Integration\GetClientAdapterTrait;

class VerifyTokenTest extends AbstractVerifyTokenTest
{
    use GetClientAdapterTrait;
}
