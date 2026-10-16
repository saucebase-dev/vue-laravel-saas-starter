<?php

namespace Modules\Blog\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Blog\Models\Redirect;

/**
 * Remembers a page's old path whenever its URL changes, and forgets them all
 * when it is deleted. The model implements `Redirectable`, which says where the
 * page is now and who may see it.
 */
trait HasRedirects
{
    /**
     * Declared so Eloquent treats it as a property, not a column to save.
     */
    protected ?string $urlBeforeUpdate = null;

    public static function bootHasRedirects(): void
    {
        // Read from the database, not the model: by now the model already holds
        // the new slug or category, and the row still holds the old ones.
        static::updating(function (self $model): void {
            $model->urlBeforeUpdate = $model->fresh()?->url();
        });

        static::updated(function (self $model): void {
            if ($model->urlBeforeUpdate === null || $model->urlBeforeUpdate === $model->url()) {
                return;
            }

            $oldPath = parse_url($model->urlBeforeUpdate, PHP_URL_PATH);

            if (is_string($oldPath)) {
                $model->rememberPath($oldPath);
            }
        });

        static::deleted(fn (self $model) => $model->redirects()->delete());
    }

    /**
     * @return MorphMany<Redirect, $this>
     */
    public function redirects(): MorphMany
    {
        return $this->morphMany(Redirect::class, 'redirectable');
    }

    /**
     * Remember that this page used to live at `$path`. A path is stored once, so
     * whatever left it most recently takes it over.
     */
    public function rememberPath(string $path): void
    {
        Redirect::updateOrCreate(
            ['path' => $path],
            ['redirectable_type' => $this->getMorphClass(), 'redirectable_id' => $this->getKey()],
        );
    }
}
