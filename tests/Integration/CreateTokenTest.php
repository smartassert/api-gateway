<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractCreateTokenTest;

class CreateTokenTest extends AbstractCreateTokenTest
{
    use GetClientAdapterTrait;
}
