<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Source;

use App\Tests\Application\Source\AbstractGetTest;
use App\Tests\Application\Source\AbstractListTest;
use App\Tests\Functional\GetClientAdapterTrait;

class GetTest extends AbstractGetTest
{
    use GetClientAdapterTrait;
}
