<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Application\AbstractUndefinedServiceTest;

class UndefinedServiceTest extends AbstractUndefinedServiceTest
{
    use GetClientAdapterTrait;
}
