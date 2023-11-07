<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\Tests\Application\User\AbstractCreateTokenTest;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateTokenTest extends AbstractCreateTokenTest
{
    use GetClientAdapterTrait;
}
