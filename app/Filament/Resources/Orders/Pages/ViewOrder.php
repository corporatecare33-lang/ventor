<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Order '.$this->record->order_number;
    }

    protected function getHeaderActions(): array
    {
        return [
            OrderResource::changeStatusAction()->after(fn () => $this->refreshFormData(['status'])),
            OrderResource::paymentStatusAction(),
            OrderResource::invoiceAction(),
            EditAction::make()->label('Edit address / courier')->color('gray'),
        ];
    }
}
