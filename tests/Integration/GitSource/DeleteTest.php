<?php

declare(strict_types=1);

namespace App\Tests\Integration\GitSource;

use App\Tests\Application\GitSource\AbstractDeleteTest;
use App\Tests\Integration\GetClientAdapterTrait;

class DeleteTest extends AbstractDeleteTest
{
    use GetClientAdapterTrait;
}
