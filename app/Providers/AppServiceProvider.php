<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureApiDocsAccess();
        $this->configureApiDocs();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Allow a valid Sanctum API token to unlock the Scramble API docs on non-local servers.
     */
    protected function configureApiDocsAccess(): void
    {
        Gate::define('viewApiDocs', function (?User $user): bool {
            if ($user?->can('view admin panel')) {
                return true;
            }

            $sanctumUser = Auth::guard('sanctum')->user();

            return $sanctumUser?->currentAccessToken() instanceof PersonalAccessToken;
        });
    }

    protected function configureApiDocs(): void
    {
        Scramble::configure()->withOperationTransformers(function (Operation $operation): void {
            collect($operation->responses)
                ->filter(fn (mixed $response): bool => $response instanceof Response && isset($response->content['application/json']))
                ->each(fn (Response $response) => $this->documentCurrentPageUrl($response->getContent('application/json')));
        });
    }

    /**
     * Laravel's paginators return `meta.current_page_url`, but Scramble's paginator schema leaves it out.
     */
    private function documentCurrentPageUrl(mixed $schema): void
    {
        if (! $schema instanceof Schema || ! $schema->type instanceof ObjectType) {
            return;
        }

        if (! $schema->type->hasProperty('meta')) {
            return;
        }

        $meta = $schema->type->getProperty('meta');

        if (! $meta instanceof ObjectType || ! $meta->hasProperty('current_page')) {
            return;
        }

        $meta->addProperty('current_page_url', new StringType)
            ->addRequired(['current_page_url']);
    }
}
