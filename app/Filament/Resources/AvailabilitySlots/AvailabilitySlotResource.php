<?php

namespace App\Filament\Resources\AvailabilitySlots;

use App\Filament\Resources\AvailabilitySlots\Pages\ManageAvailabilitySlots;
use App\Models\AvailabilitySlot;
use App\Models\Provider;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AvailabilitySlotResource extends Resource
{
    protected static ?string $model = AvailabilitySlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Scheduling';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Availability';

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('scheduling');
    }

    public static function canAccess(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('provider_id')->options(Provider::query()->pluck('name', 'id'))->searchable()->required(),
            Select::make('weekday')->options([
                1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
                5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
            ])->required(),
            TextInput::make('starts_at')->required()->placeholder('09:00'),
            TextInput::make('ends_at')->required()->placeholder('17:00'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider.name')->searchable(),
                TextColumn::make('weekday')->formatStateUsing(fn ($state) => [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'][$state] ?? $state),
                TextColumn::make('starts_at'),
                TextColumn::make('ends_at'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAvailabilitySlots::route('/')];
    }
}
