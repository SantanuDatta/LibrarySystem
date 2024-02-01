<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\BorrowedStatus;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Borrowed Book Status';

    protected static ?string $pollingInterval = '300s';

    protected function getData(): array
    {
        $data = $this->getTransaction();

        return [
            'datasets' => [
                [
                    'label' => 'Transactions made',
                    'data' => $data['transactions'],
                ],
            ],
            'labels' => BorrowedStatus::cases(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getTransaction(): array
    {
        $transactions = Transaction::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'transactions' => $transactions,
        ];
    }
}
