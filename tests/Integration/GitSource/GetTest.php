<?php

declare(strict_types=1);

namespace App\Tests\Integration\GitSource;

use App\Tests\Application\GitSource\AbstractGetTest;
use App\Tests\Integration\GetClientAdapterTrait;

class GetTest extends AbstractGetTest
{
    use GetClientAdapterTrait;
}
