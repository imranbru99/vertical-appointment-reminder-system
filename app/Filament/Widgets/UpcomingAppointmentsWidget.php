<?php

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->where('starts_at', '>=', now())
                    ->whereNotIn('status', [
                        AppointmentStatus::Cancelled,
                        AppointmentStatus::NoShow,
                        AppointmentStatus::Completed,
                    ])
                    ->orderBy('starts_at')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('starts_at')->dateTime()->label('When'),
                TextColumn::make('reference')->searchable(),
                TextColumn::make('client.name')->label('Client'),
                TextColumn::make('provider.name')->label('Provider'),
                TextColumn::make('status')->badge(),
                TextColumn::make('no_show_risk')->label('No-show risk'),
            ])
            ->paginated(false);
    }
}