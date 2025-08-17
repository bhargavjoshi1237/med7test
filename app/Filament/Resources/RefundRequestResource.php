<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundRequestResource\Pages;
use App\Filament\Resources\RefundRequestResource\RelationManagers;
use App\Models\RefundRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefundRequestResource extends Resource
{
    protected static ?string $model = RefundRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?string $navigationGroup = 'Sales';   

    protected static ?string $label = 'Refund Request';

    public static function getNavigationBadge(): ?string
    {
        return (string) RefundRequest::count();

    }

    protected static ?string $pluralLabel = 'Refund Requests';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('order_id')
                ->relationship('order', 'id') // or another identifying field
                ->label('Order')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name') // assuming users have a 'name' field
                ->label('Requested By')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('approve')
                ->label('Status')
                ->required()
                ->default('pending'),

            Forms\Components\Textarea::make('comment')
                ->label('Comment')
                ->rows(3)
                ->nullable(),

            Forms\Components\Textarea::make('meta')
                ->label('Meta')
                ->json()
                ->nullable(),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('id')->sortable(),

            Tables\Columns\TextColumn::make('order.id')
                ->label('Order ID')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('User')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('approve')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'pending' => 'warning',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('comment')
                ->limit(50)
                ->toggleable()
                ->wrap(),

            Tables\Columns\TextColumn::make('order.total.formatted')
                ->label('Order Total')
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Requested At')
                ->dateTime()
                ->sortable(),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('Last Updated')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            Tables\Filters\SelectFilter::make('approve')
                ->label('Status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            // Tables\Actions\Action::make('process_refund')
            //     ->label('Process')
            //     ->icon('heroicon-o-backward')
            //     ->color('warning')
            //     ->url(fn ($record) => static::getUrl('edit', ['record' => $record]))
            //     ->visible(fn ($record) => $record->approve === 'pending'),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRefundRequests::route('/'),
            'create' => Pages\CreateRefundRequest::route('/create'),
            'view' => Pages\ViewRefundRequest::route('/{record}'),
            'edit' => Pages\EditRefundRequest::route('/{record}/edit'),
        ];
    }
}
