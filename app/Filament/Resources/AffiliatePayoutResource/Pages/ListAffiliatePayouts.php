<?php

namespace App\Filament\Resources\AffiliatePayoutResource\Pages;

use App\Filament\Resources\AffiliatePayoutResource;
use App\Models\Affiliate;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAffiliatePayouts extends ListRecords
{
    protected static string $resource = AffiliatePayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export_activity')
                ->label('Export Activity Report')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('affiliate_id')
                        ->label('Affiliate')
                        ->options([
                            'all' => 'All Affiliates',
                            ...Affiliate::pluck('name', 'id')->toArray()
                        ])
                        ->default('all')
                        ->required(),
                    
                    Forms\Components\Select::make('duration')
                        ->label('Duration')
                        ->options([
                            'last_month' => 'Last Month',
                            'last_week' => 'Last Week',
                            'custom' => 'Custom Range',
                        ])
                        ->default('last_month')
                        ->required()
                        ->reactive(),
                    
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->visible(fn (callable $get) => $get('duration') === 'custom')
                        ->required(fn (callable $get) => $get('duration') === 'custom'),
                    
                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date')
                        ->visible(fn (callable $get) => $get('duration') === 'custom')
                        ->required(fn (callable $get) => $get('duration') === 'custom')
                        ->after('start_date'),
                ])
                ->action(function (array $data) {
                    $queryParams = http_build_query($data);
                    $url = route('affiliate.export.activity') . '?' . $queryParams;
                    
                    Notification::make()
                        ->title('Export Started')
                        ->body('Your activity report is being generated. The download will start shortly.')
                        ->success()
                        ->send();
                    
                    $this->js("window.open('$url', '_blank')");
                }),
        ];
    }


}
