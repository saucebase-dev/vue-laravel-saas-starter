<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Blog\Contracts\Redirectable;

/**
 * A path a post, category or tag page used to live at, kept so old links and
 * search results still reach it.
 *
 * @property int $id
 * @property string $path
 */
class Redirect extends Model
{
    protected $table = 'blog_redirects';

    protected $fillable = ['path', 'redirectable_type', 'redirectable_id'];

    /**
     * @return MorphTo<Model, $this>
     */
    public function redirectable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * A permanent redirect from the requested path to where its page lives now,
     * or a 404.
     *
     * Only asked once no live page matched, so a new page that takes an old path
     * is shown rather than redirected away from. The target is its current URL,
     * however many times it has moved since, so there are no chains, and it must
     * be one the public may see, or the old path is a 404 like any other.
     */
    public static function responseFor(Request $request): RedirectResponse
    {
        $target = static::where('path', '/'.$request->path())->first()?->redirectable;

        abort_unless($target instanceof Redirectable && $target->isPubliclyVisible(), 404);

        return redirect($target->url(), 301);
    }
}
