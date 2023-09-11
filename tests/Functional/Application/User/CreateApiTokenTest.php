<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\User;

use App\Tests\Application\User\AbstractCreateApiTokenTest;
use App\Tests\Functional\Application\GetClientAdapterTrait;

class CreateApiTokenTest extends AbstractCreateApiTokenTest
{
    use GetClientAdapterTrait;
}
