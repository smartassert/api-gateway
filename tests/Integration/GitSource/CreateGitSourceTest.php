<?php

declare(strict_types=1);

namespace App\Tests\Integration\GitSource;

use App\Tests\Application\GitSource\AbstractCreateGitSourceTest;
use App\Tests\Integration\GetClientAdapterTrait;

class CreateGitSourceTest extends AbstractCreateGitSourceTest
{
    use GetClientAdapterTrait;
}
