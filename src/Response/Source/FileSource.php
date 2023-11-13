<?php

declare(strict_types=1);

namespace App\Response\Source;

use App\Response\LabelledBody;
use App\Response\Response;
use SmartAssert\SourcesClient\Model\FileSource as SourcesClientFileSource;

class FileSource extends Response
{
    public function __construct(SourcesClientFileSource $source)
    {
        parent::__construct(new LabelledBody('file_source', new FileSourceBody($source)));
    }
}
