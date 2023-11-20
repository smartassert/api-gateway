<?php

declare(strict_types=1);

namespace App\Tests\Integration\Suite;

use App\Tests\Application\Suite\AbstractCreateTest;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateTest extends AbstractCreateTest
{
    use GetClientAdapterTrait;
}
