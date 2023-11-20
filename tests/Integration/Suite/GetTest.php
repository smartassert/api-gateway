<?php

declare(strict_types=1);

namespace App\Tests\Integration\Suite;

use App\Tests\Application\Suite\AbstractGetTest;
use App\Tests\Integration\GetClientAdapterTrait;

class GetTest extends AbstractGetTest
{
    use GetClientAdapterTrait;
}
