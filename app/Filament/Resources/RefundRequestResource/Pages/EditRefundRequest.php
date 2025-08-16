<?php

namespace App\Filament\Resources\RefundRequestResource\Pages;

use App\Filament\Resources\RefundRequestResource;
use Awcodes\Shout\Components\Shout;
use Closure;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Facades\FilamentIcon;
use Livewire\Attributes\Computed;
use Lunar\Models\Transaction;

class EditRefundRequest extends EditRecord
{
    protected static string $resource = RefundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getProcessRefundAction(),
            $this->getRejectRefundAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getProcessRefundAction(): Actions\Action
    {
        return Actions\Action::make('process_refund')
            ->label('Process Refund')
            ->modalSubmitActionLabel('Process Refund')
            ->icon('heroicon-o-backward')
            ->color('warning')
            ->form(fn () => [
                Forms\Components\Select::make('transaction')
                    ->label('Transaction')
                    ->required()
                    ->default(fn () => $this->charges->first()?->id)
                    ->options(fn () => $this->charges->mapWithKeys(fn ($charge) => [
                        $charge->id => "{$charge->amount->formatted} - {$charge->driver} // {$charge->reference}",
                    ]))
                    ->live(),

                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->label('Refund Amount')
                    ->suffix(fn () => $this->record->order->currency->code)
                    ->default(fn () => number_format(
                        $this->availableToRefund / $this->record->order->currency->factor, 
                        $this->record->order->currency->decimal_places, 
                        '.', 
                        ''
                    ))
                    ->live()
                    ->autocomplete(false)
                    ->minValue(fn () => 1 / $this->record->order->currency->factor)
                    ->numeric(),

                Forms\Components\Textarea::make('notes')
                    ->label('Refund Notes')
                    ->autocomplete(false)
                    ->maxLength(255)
                    ->default('Refund processed for refund request #' . $this->record->id),

                Forms\Components\Toggle::make('confirm')
                    ->label('Confirm Refund Processing')
                    ->helperText('I confirm that I want to process this refund. This action cannot be undone.')
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if ($value !== true) {
                                    $fail('You must confirm the refund processing to continue.');
                                }
                            };
                        },
                    ]),
            ])
            ->action(function ($data, Actions\Action $action) {
                $transaction = Transaction::findOrFail($data['transaction']);
                $response = $transaction->refund(
                    bcmul($data['amount'], $this->record->order->currency->factor), 
                    $data['notes']
                );

                if (!$response->success) {
                    $action->failureNotification(
                        fn () => Notification::make('refund_failure')
                            ->color('danger')
                            ->title('Refund Failed')
                            ->body($response->message)
                    );
                    $action->failure();
                    $action->halt();
                    return;
                }

                // Update refund request status to approved
                $this->record->update([
                    'approve' => 'approved',
                    'comment' => ($this->record->comment ? $this->record->comment . "\n\n" : '') . 
                                "Refund processed: {$data['amount']} {$this->record->order->currency->code}. Notes: {$data['notes']}"
                ]);

                $action->success();
            })
            ->successNotificationTitle('Refund Processed Successfully')
            ->failureNotificationTitle('Refund Processing Failed')
            ->visible(fn () => $this->record->approve === 'pending' && $this->charges->count() && $this->canBeRefunded);
    }

    protected function getRejectRefundAction(): Actions\Action
    {
        return Actions\Action::make('reject_refund')
            ->label('Reject Refund')
            ->modalSubmitActionLabel('Reject Refund')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->form(fn () => [
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->required()
                    ->maxLength(500)
                    ->placeholder('Please provide a reason for rejecting this refund request...'),

                Forms\Components\Toggle::make('confirm_rejection')
                    ->label('Confirm Rejection')
                    ->helperText('I confirm that I want to reject this refund request.')
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if ($value !== true) {
                                    $fail('You must confirm the rejection to continue.');
                                }
                            };
                        },
                    ]),
            ])
            ->action(function ($data, Actions\Action $action) {
                // Update refund request status to rejected
                $this->record->update([
                    'approve' => 'rejected',
                    'comment' => ($this->record->comment ? $this->record->comment . "\n\n" : '') . 
                                "Refund rejected: {$data['rejection_reason']}"
                ]);

                $action->success();
            })
            ->successNotificationTitle('Refund Request Rejected')
            ->visible(fn () => $this->record->approve === 'pending');
    }

    #[Computed]
    public function charges(): \Illuminate\Support\Collection
    {
        return $this->record->order->transactions()
            ->whereType('capture')
            ->whereSuccess(true)
            ->get();
    }

    #[Computed]
    public function refunds(): \Illuminate\Support\Collection
    {
        return $this->record->order->transactions()
            ->whereType('refund')
            ->whereSuccess(true)
            ->get();
    }

    #[Computed]
    public function availableToRefund(): float
    {
        return $this->charges->sum('amount.value') - $this->refunds->sum('amount.value');
    }

    #[Computed]
    public function canBeRefunded(): bool
    {
        return $this->availableToRefund > 0;
    }
}
