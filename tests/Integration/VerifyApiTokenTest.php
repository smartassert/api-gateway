<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractVerifyApiTokenTest;

class VerifyApiTokenTest extends AbstractVerifyApiTokenTest
{
    use GetClientAdapterTrait;
}
