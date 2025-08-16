<?php

namespace App\Filament\Resources\AffiliatePayoutResource\Pages;

use App\Filament\Resources\AffiliatePayoutResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditAffiliatePayout extends EditRecord
{
    protected static string $resource = AffiliatePayoutResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name')
                    ->disabled()
                    ->dehydrated(),
                
                Forms\Components\TextInput::make('amount')
                    ->label('Payout Amount')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\Select::make('method')
                    ->label('Payout Method')
                    ->options([
                        'paypal' => 'PayPal',
                        'bank_transfer' => 'Bank Transfer',
                        'store_credit' => 'Store Credit',
                        'check' => 'Check',
                    ])
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'completed' && !$this->record->paid_at) {
                            $set('paid_at', now());
                        }
                    }),
                
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('Paid At')
                    ->visible(fn (callable $get) => $get('status') === 'completed'),
                
                Forms\Components\TextInput::make('transaction_id')
                    ->label('Transaction ID')
                    ->helperText('External payment processor transaction ID'),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
