<?php

declare(strict_types=1);

namespace App\Tests\Integration\FileSource;

use App\Tests\Application\FileSource\AbstractReadFileTest;
use App\Tests\Integration\GetClientAdapterTrait;

class ReadFileTest extends AbstractReadFileTest
{
    use GetClientAdapterTrait;
}
