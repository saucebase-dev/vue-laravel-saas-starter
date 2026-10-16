<?php

namespace Modules\Blog\Contracts;

/**
 * A public page whose old paths redirect to it. Use with `HasRedirects`.
 */
interface Redirectable
{
    /** The page's current public URL. */
    public function url(): string;

    /** Whether the public may see the page, and so be redirected to it. */
    public function isPubliclyVisible(): bool;
}
