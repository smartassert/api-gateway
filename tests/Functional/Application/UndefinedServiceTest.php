<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application;

use App\Tests\Application\AbstractUndefinedServiceTest;
use App\Tests\Functional\GetClientAdapterTrait;

class UndefinedServiceTest extends AbstractUndefinedServiceTest
{
    use GetClientAdapterTrait;
}
