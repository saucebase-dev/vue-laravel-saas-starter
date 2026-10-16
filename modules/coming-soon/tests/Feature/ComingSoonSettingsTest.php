<?php

namespace Modules\ComingSoon\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\ComingSoon\Filament\Pages\ComingSoonSettings as SettingsPage;
use Modules\ComingSoon\Settings\ComingSoonSettings;
use Tests\TestCase;

class ComingSoonSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_managers_can_save_and_disable_coming_soon(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo(['access admin panel', 'manage settings']);
        $this->actingAs($user);

        Livewire::test(SettingsPage::class)
            ->fillForm(['enabled' => true, 'title' => 'Launching soon', 'message' => 'Back tomorrow.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = new ComingSoonSettings;
        $this->assertTrue($settings->enabled);
        $this->assertSame('Launching soon', $settings->title);
        $this->assertSame('Back tomorrow.', $settings->message);
        $this->get(SettingsPage::getUrl())->assertOk();

        Livewire::test(SettingsPage::class)->fillForm(['enabled' => false])->call('save')->assertHasNoFormErrors();
        $this->assertFalse((new ComingSoonSettings)->enabled);
    }

    public function test_panel_access_alone_does_not_allow_managing_coming_soon(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo('access admin panel');

        $this->actingAs($user)->get(SettingsPage::getUrl())->assertForbidden();
        Livewire::test(SettingsPage::class)->assertForbidden();
    }

    public function test_title_and_message_are_required(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo(['access admin panel', 'manage settings']);
        $this->actingAs($user);

        Livewire::test(SettingsPage::class)
            ->fillForm(['title' => '', 'message' => ''])
            ->call('save')
            ->assertHasFormErrors(['title' => 'required', 'message' => 'required']);

        $this->assertFalse((new ComingSoonSettings)->enabled);
    }
}
