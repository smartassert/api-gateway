<?php

declare(strict_types=1);

namespace App\Tests\Integration\Source;

use App\Tests\Application\Source\AbstractGetTest;
use App\Tests\Integration\GetClientAdapterTrait;

class GetTest extends AbstractGetTest
{
    use GetClientAdapterTrait;
}
