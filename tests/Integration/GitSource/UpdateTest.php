<?php

declare(strict_types=1);

namespace App\Tests\Integration\GitSource;

use App\Tests\Application\GitSource\AbstractUpdateTest;
use App\Tests\Integration\GetClientAdapterTrait;

class UpdateTest extends AbstractUpdateTest
{
    use GetClientAdapterTrait;
}
