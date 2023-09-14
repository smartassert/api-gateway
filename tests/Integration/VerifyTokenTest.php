<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractVerifyTokenTest;

class VerifyTokenTest extends AbstractVerifyTokenTest
{
    use GetClientAdapterTrait;
}
