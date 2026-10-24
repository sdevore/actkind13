<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Mail\BulkEmailUsers;
use App\Models\User;
use Closure;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['administrator', 'super-admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(['md' => 2])
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): string => $operation === 'create'
                        ? 'Leave blank to email the user a link to set their own password.'
                        : 'Leave blank to keep the current password.'),
                Select::make('roles')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => self::assignableRoles($query),
                    )
                    ->multiple()
                    ->preload()
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            $assignableRoleIds = self::assignableRoles(Role::query())
                                ->pluck('id')
                                ->map(fn (int $id): string => (string) $id);

                            $unassignableRoleIds = collect($value)
                                ->map(fn (mixed $id): string => (string) $id)
                                ->diff($assignableRoleIds);

                            if ($unassignableRoleIds->isNotEmpty()) {
                                $fail("You can't assign one of the selected roles.");
                            }
                        },
                    ]),
            ]);
    }

    /**
     * Only a super-admin may grant the super-admin role.
     *
     * @param  Builder<Role>  $query
     * @return Builder<Role>
     */
    private static function assignableRoles(Builder $query): Builder
    {
        if (auth()->user()?->hasRole('super-admin')) {
            return $query;
        }

        return $query->where('name', '!=', 'super-admin');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('acts_count')
                    ->label('Acts')
                    ->counts('acts'),
                TextColumn::make('comments_count')
                    ->label(new HtmlString(Blade::render(' <x-icon name="far-comments" class="mr-2 h-4 w-4" />')))
                    ->counts('comments')
                    ->sortable(),
                TextColumn::make('flag_ct')
                    ->label(new HtmlString(Blade::render(' <x-icon name="fas-flag" class="mr-2 h-4 w-4" />')))
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('email')
                        ->authorize('emailAny')
                        ->icon('heroicon-o-envelope')
                        ->color('primary')
                        ->form([
                            TextInput::make('subject')
                                ->required(),
                            MarkdownEditor::make('body')
                                ->required(),
                            TextInput::make('email')
                                ->label('From Email')
                                ->default(Config::get('mail.from.address'))
                                ->required(),
                            TextInput::make('name')
                                ->label('From name')
                                ->default(Config::get('mail.from.name'))
                                ->required(),

                        ])
                        ->action(function (array $data, Collection $records): void {
                            /** @var User $record */
                            foreach ($records as $record) {
                                $user = new Address($record->email, $record->name);
                                Mail::to($user)->queue(new BulkEmailUsers($data, $record));
                            }
                            Notification::make('success')
                                ->title('Email queued for '.Str::plural('User', $records->count()))
                                ->success()
                                ->icon('heroicon-o-envelope')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
