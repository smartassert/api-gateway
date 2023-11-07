<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\Tests\Application\User\AbstractListApiKeysTest;
use App\Tests\Integration\GetClientAdapterTrait;

class ListApiKeysTest extends AbstractListApiKeysTest
{
    use GetClientAdapterTrait;
}
