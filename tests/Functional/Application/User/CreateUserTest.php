<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractCreateUserTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class CreateUserTest extends AbstractCreateUserTest
{
    use GetClientAdapterTrait;
}
