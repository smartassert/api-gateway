<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractVerifyApiTokenTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class VerifyApiTokenTest extends AbstractVerifyApiTokenTest
{
    use GetClientAdapterTrait;
}
