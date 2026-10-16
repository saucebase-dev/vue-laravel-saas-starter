<?php

namespace Modules\Blog\Tests\Unit;

use Modules\Blog\Models\Post;
use Tests\TestCase;

class PostTest extends TestCase
{
    public function test_cover_media_collection_is_registered_as_single_file(): void
    {
        $post = new Post;
        $post->registerMediaCollections();

        $collection = $post->getMediaCollection('cover');

        $this->assertNotNull($collection);
        $this->assertTrue($collection->singleFile);
    }
}
