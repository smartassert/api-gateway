<?php

declare(strict_types=1);

namespace App\Tests\Integration\FileSource;

use App\Tests\Application\FileSource\AbstractRemoveFileTest;
use App\Tests\Integration\GetClientAdapterTrait;

class RemoveFileTest extends AbstractRemoveFileTest
{
    use GetClientAdapterTrait;
}
