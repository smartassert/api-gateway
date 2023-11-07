<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\Tests\Application\User\AbstractCreateUserTest;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateUserTest extends AbstractCreateUserTest
{
    use GetClientAdapterTrait;
}
