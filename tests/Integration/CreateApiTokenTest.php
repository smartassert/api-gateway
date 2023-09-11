<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\User\AbstractCreateApiTokenTest;

class CreateApiTokenTest extends AbstractCreateApiTokenTest
{
    use GetClientAdapterTrait;
}
