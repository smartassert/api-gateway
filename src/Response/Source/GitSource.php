<?php

declare(strict_types=1);

namespace App\Response\Source;

use App\Response\LabelledBody;
use App\Response\Response;
use SmartAssert\SourcesClient\Model\GitSource as SourcesClientGitSource;

class GitSource extends Response
{
    public function __construct(SourcesClientGitSource $source)
    {
        parent::__construct(new LabelledBody('git_source', new GitSourceBody($source)));
    }
}
