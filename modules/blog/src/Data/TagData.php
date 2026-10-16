<?php

namespace Modules\Blog\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class TagData extends Data
{
    public function __construct(
        public string $name,
        public string $slug,
    ) {}
}
