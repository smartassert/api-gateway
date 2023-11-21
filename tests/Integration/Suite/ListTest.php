<?php

declare(strict_types=1);

namespace App\Tests\Integration\Suite;

use App\Tests\Application\Suite\AbstractListTest;
use App\Tests\Integration\GetClientAdapterTrait;

class ListTest extends AbstractListTest
{
    use GetClientAdapterTrait;
}
