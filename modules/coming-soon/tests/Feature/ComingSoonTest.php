<?php

namespace Modules\ComingSoon\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\ComingSoon\Filament\Pages\ComingSoonSettings as SettingsPage;
use Modules\ComingSoon\Settings\ComingSoonSettings;
use Tests\TestCase;

class ComingSoonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders(['Accept' => 'text/html']);
        Route::middleware('web')->group(function (): void {
            Route::get('/coming-soon-test', fn () => 'Public page')->name('coming-soon-test.public');
            Route::get('/coming-soon-private', fn () => 'Private page')->middleware('auth');
            Route::get('/coming-soon-bound/{user}', fn (User $user) => $user->name);
            Route::post('/coming-soon-test', fn () => 'Submitted');
            Route::get('/coming-soon-script.js', fn () => response('window.loaded = true;', 200, ['Content-Type' => 'application/javascript']));
        });
        Route::middleware('api')->get('/coming-soon-api', fn () => ['available' => true]);
    }

    public function test_installation_is_disabled_and_the_page_redirects_home(): void
    {
        $this->get('/coming-soon-test')->assertSee('Public page');
        $this->get(route('coming-soon.index'))->assertRedirect('/');
    }

    public function test_enabled_mode_redirects_visitors_and_regular_users(): void
    {
        $this->enableComingSoon();

        $this->get('/coming-soon-test')->assertRedirectToRoute('coming-soon.index');
        $this->actingAs($this->createUser())->get('/coming-soon-test')->assertRedirectToRoute('coming-soon.index');
        $this->head('/coming-soon-test')->assertRedirectToRoute('coming-soon.index');
    }

    public function test_redirects_run_before_authentication_and_model_binding(): void
    {
        $this->enableComingSoon();

        $this->get('/coming-soon-private')->assertRedirectToRoute('coming-soon.index');
        $this->get('/coming-soon-bound/999999')->assertRedirectToRoute('coming-soon.index');
    }

    public function test_inertia_visits_are_redirected(): void
    {
        $this->enableComingSoon();

        $this->get('/coming-soon-test', ['X-Inertia' => 'true'])->assertRedirectToRoute('coming-soon.index');
    }

    public function test_the_page_shows_saved_content_without_a_redirect_loop(): void
    {
        $this->enableComingSoon();
        $settings = app(ComingSoonSettings::class);
        $settings->title = 'Launching soon';
        $settings->message = '<strong>Plain text only</strong>';
        $settings->save();

        $this->get(route('coming-soon.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('ComingSoon::Index', false)
            ->where('title', 'Launching soon')
            ->where('message', '<strong>Plain text only</strong>')
            ->where('comingSoon.showBanner', false)
            ->where('comingSoon.settingsUrl', null));
    }

    public function test_the_page_path_is_configurable(): void
    {
        config(['coming-soon.path' => '/launch']);
        Route::getRoutes()->getByName('coming-soon.index')->name('previous');
        Route::domain(parse_url(config('app.url'), PHP_URL_HOST))->group(module_path('coming-soon', 'routes/web.php'));
        Route::getRoutes()->refreshNameLookups();
        app('url')->setRoutes(Route::getRoutes());
        $this->enableComingSoon();

        $this->get('/coming-soon-test')->assertRedirect('/launch');
        $this->get('/launch')->assertOk();
    }

    public function test_exact_and_wildcard_route_exceptions_are_application_owned(): void
    {
        $this->enableComingSoon();
        config(['coming-soon.except_routes' => ['coming-soon-test.public']]);
        $this->get('/coming-soon-test')->assertSee('Public page');

        config(['coming-soon.except_routes' => ['coming-soon-test.*']]);
        $this->get('/coming-soon-test')->assertSee('Public page');
        $this->get('/coming-soon-private')->assertRedirectToRoute('coming-soon.index');
    }

    public function test_non_page_requests_and_unknown_urls_are_unchanged(): void
    {
        $this->enableComingSoon();

        $this->getJson('/coming-soon-test')->assertSee('Public page');
        $this->post('/coming-soon-test')->assertSee('Submitted');
        $this->get('/coming-soon-api')->assertJson(['available' => true]);
        $this->get('/coming-soon-script.js', ['Accept' => '*/*'])->assertSee('window.loaded = true;');
        $this->get('/coming-soon-missing')->assertNotFound();
    }

    public function test_settings_managers_bypass_redirects_and_receive_the_banner_prop(): void
    {
        $this->enableComingSoon();
        $user = $this->createUser();
        $user->givePermissionTo('manage settings');

        $this->actingAs($user)->get('/coming-soon-test')->assertSee('Public page');
        $this->get(route('coming-soon.index'))->assertInertia(fn (Assert $page) => $page
            ->where('comingSoon.showBanner', true)
            ->where('comingSoon.settingsUrl', null));
    }

    public function test_admins_receive_a_link_to_coming_soon_settings(): void
    {
        $this->enableComingSoon();
        $user = $this->createUser();
        $user->givePermissionTo(['manage settings', 'access admin panel']);

        $this->actingAs($user)->get(route('coming-soon.index'))->assertInertia(fn (Assert $page) => $page
            ->where('comingSoon.settingsUrl', SettingsPage::getUrl()));
    }

    public function test_disabling_restores_pages_and_hides_the_admin_banner(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo('manage settings');
        $this->actingAs($user);
        Route::middleware('web')->get('/coming-soon-props', fn () => Inertia::render('ComingSoon::Index', ['title' => 'Test', 'message' => 'Test']));

        $this->get('/coming-soon-props')->assertInertia(fn (Assert $page) => $page->where('comingSoon.showBanner', false));
    }

    public function test_application_login_and_panel_access_remain_protected(): void
    {
        config(['coming-soon.except_routes' => []]);
        $this->enableComingSoon();
        $this->get(route('login'))->assertRedirectToRoute('coming-soon.index');

        config(['coming-soon.except_routes' => ['login', 'filament.*']]);

        $this->get(route('login'))->assertOk();
        $this->get('/admin')->assertRedirectToRoute('login');
    }

    private function enableComingSoon(): void
    {
        $settings = app(ComingSoonSettings::class);
        $settings->enabled = true;
        $settings->save();
    }
}
