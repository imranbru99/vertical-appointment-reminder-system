<?php

namespace App\Filament\Resources\Reminders;

use App\Enums\ReminderChannel;
use App\Enums\ReminderStatus;
use App\Filament\Resources\Reminders\Pages\ManageReminders;
use App\Models\Reminder;
use App\Services\ModuleManager;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class ReminderResource extends Resource
{
    protected static ?string $model = Reminder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleManager::class)->enabled('reminders');
    }

    public static function canAccess(): bool
    {
        return static::shouldRegisterNavigation();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('appointment_id')->relationship('appointment', 'reference')->searchable()->required(),
            Select::make('channel')->options(ReminderChannel::options())->required(),
            Select::make('status')->options(ReminderStatus::options())->required(),
            TextInput::make('template_key'),
            DateTimePicker::make('scheduled_for')->required(),
            DateTimePicker::make('sent_at'),
            TextInput::make('recipient'),
            Textarea::make('body')->columnSpanFull(),
            TextInput::make('provider_response'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scheduled_for')->dateTime()->sortable(),
                TextColumn::make('appointment.reference')->label('Appointment'),
                TextColumn::make('channel')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('recipient'),
                TextColumn::make('sent_at')->dateTime(),
            ])
            ->defaultSort('scheduled_for', 'desc')
            ->filters([
                SelectFilter::make('status')->options(ReminderStatus::options()),
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
            'index' => ManageReminders::route('/'),
        ];
    }
}