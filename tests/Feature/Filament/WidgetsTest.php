<?php

use App\Filament\Resources\Acts\Widgets\AppreciatesStatsWidget;
use App\Filament\Widgets\ActsWidget;
use App\Filament\Widgets\CommentsWidget;
use App\Filament\Widgets\FlagsWidget;
use App\Models\Act;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');
    $this->actingAs($moderator);
});

test('dashboard chart widgets render for every period filter', function (string $widget, string $filter) {
    Act::factory()->count(2)->create();

    Livewire::test($widget)
        ->set('filter', $filter)
        ->assertOk();
})->with([
    ActsWidget::class,
    CommentsWidget::class,
    FlagsWidget::class,
])->with(['week', 'month', '3months']);

test('dashboard chart widgets select only the trend columns, not the table counts', function (string $widget) {
    Act::factory()->count(2)->create();
    DB::enableQueryLog();

    Livewire::test($widget)->assertOk();

    $trendQueries = collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(fn (string $query) => str_contains($query, 'as aggregate'));

    expect($trendQueries)->not->toBeEmpty()
        ->each(fn ($query) => $query->not->toContain('.*', '_count'));
})->with([
    ActsWidget::class,
    CommentsWidget::class,
    FlagsWidget::class,
]);

test('the appreciations stat counts appreciations, not acts', function () {
    $act = Act::factory()->create();
    $act->appreciates()->createMany(
        User::factory()->count(3)->create()->map(fn (User $user) => ['user_id' => $user->id])->all()
    );

    $widget = Livewire::test(AppreciatesStatsWidget::class)->instance();
    $stats = (new ReflectionMethod($widget, 'getStats'))->invoke($widget);

    expect($stats[0]->getLabel())->toBe('Appreciation Count')
        ->and($stats[0]->getValue())->toBe(3);
});

test('the appreciations stat charts daily appreciations over the last 14 days', function () {
    $act = Act::factory()->create();
    $this->travelTo(today()->subDays(2)->setHour(12));
    $act->appreciates()->create(['user_id' => User::factory()->create()->id]);
    $this->travelBack();
    $act->appreciates()->createMany(
        User::factory()->count(2)->create()->map(fn (User $user) => ['user_id' => $user->id])->all()
    );

    $widget = Livewire::test(AppreciatesStatsWidget::class)->instance();
    $chart = (new ReflectionMethod($widget, 'getStats'))->invoke($widget)[0]->getChart();

    expect($chart)->toHaveCount(14)
        ->and($chart[11])->toBe(1)
        ->and($chart[13])->toBe(2)
        ->and(array_sum($chart))->toBe(3);
});
