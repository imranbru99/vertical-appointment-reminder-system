<?php

namespace App\Filament\Resources\WaitlistEntries;

use App\Enums\WaitlistStatus;
use App\Filament\Resources\WaitlistEntries\Pages\ManageWaitlistEntries;
use App\Models\WaitlistEntry;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class WaitlistEntryResource extends Resource
{
    protected static ?string $model = WaitlistEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Waitlist';

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('waitlist');
    }

    public static function canAccess(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('client_id')->relationship('client', 'name')->searchable()->required(),
            Select::make('service_id')->relationship('service', 'name')->searchable(),
            Select::make('provider_id')->relationship('provider', 'name')->searchable(),
            Select::make('offered_appointment_id')->relationship('offeredAppointment', 'reference')->searchable(),
            Select::make('status')->options(WaitlistStatus::options())->required(),
            TextInput::make('priority')->numeric()->default(50),
            DatePicker::make('preferred_date'),
            TextInput::make('preferred_time_window'),
            DateTimePicker::make('offered_at'),
            DateTimePicker::make('expires_at'),
            Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')->searchable(),
                TextColumn::make('service.name'),
                TextColumn::make('priority')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('preferred_date')->date(),
                TextColumn::make('offered_at')->dateTime(),
            ])
            ->defaultSort('priority', 'desc')
            ->filters([
                SelectFilter::make('status')->options(WaitlistStatus::options()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWaitlistEntries::route('/'),
        ];
    }
}