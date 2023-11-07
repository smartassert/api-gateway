<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractCreateTokenTest;
use App\Tests\Functional\GetClientAdapterTrait;

class CreateTokenTest extends AbstractCreateTokenTest
{
    use GetClientAdapterTrait;
}
