<?php

namespace Modules\ComingSoon\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ComingSoon\Settings\ComingSoonSettings;

class ComingSoonController
{
    public function index(ComingSoonSettings $settings): Response|RedirectResponse
    {
        if (! $settings->enabled) {
            return redirect('/');
        }

        return Inertia::render('ComingSoon::Index', [
            'title' => $settings->title,
            'message' => $settings->message,
        ])->withSSR();
    }
}
