<?php

declare(strict_types=1);

namespace App\Tests\Integration\Suite;

use App\Tests\Application\Suite\AbstractDeleteTest;
use App\Tests\Integration\GetClientAdapterTrait;

class DeleteTest extends AbstractDeleteTest
{
    use GetClientAdapterTrait;
}
