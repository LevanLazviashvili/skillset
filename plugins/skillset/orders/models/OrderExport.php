<?php namespace skillset\Orders\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use skillset\details\Models\LegalType;

class OrderExport extends \Backend\Models\ExportModel
{
    public $statuses = [
        0 => 'გაუქმებულია',
        1 => 'სამუშაოები დაიწყო',
        2 => 'მიღება ჩაბარება გაფორმდა',
        3 => 'დამკვეთმა მიიღო სამუშაოები (გადაუხდელია)',
        4 => 'შეკვეთა გადახდილია',
    ];
    public $payment_types = [
            0 => 'ნაღდი ანგარიშსწორებით',
            1 => 'ბარათით'
    ];

    public $defaultLegalType = [
        0 => 'კერძო პირი'
    ];

    public function exportData($columns, $sessionKey = null)
    {
        $filters = $this->getCurrentFilters();
        $OrderQuery = Order::with('Client', 'Worker');
        if ($startDate = Arr::get($filters, '0.scope-start_date')) {
            if ($startDateFrom = Arr::get($startDate, 0)) {
                $OrderQuery->where('start_date', '>=', $startDateFrom->toDateTimeString());
//                $OrderQuery->where('start_date', '>=', $startDateFrom->toDateString().' 00:00:01');
            }
            if ($startDateTo = Arr::get($startDate, 1)) {
                $OrderQuery->where('start_date', '<=', $startDateTo->toDateTimeString());
            }
        }
        if ($endDate = Arr::get($filters, '0.scope-end_date')) {
            if ($endDateFrom = Arr::get($endDate, 0)) {
//                $OrderQuery->where('end_date', '>=', $endDateFrom->toDateString().' 00:00:01');
                $OrderQuery->where('end_date', '>=', $endDateFrom->toDateString());
            }
            if ($endDateTo = Arr::get($endDate, 1)) {
                $OrderQuery->where('end_date', '<=', $endDateTo->toDateTimeString());
            }
        }

        $Orders = $OrderQuery->get();
        $LegalTypes = $this->getLegalTypes();
        $Orders->each(function($Order) use ($columns, $LegalTypes) {
            $Order->addVisible($columns);
            if (in_array('client_id', $columns) && $Order->Client) {
                $Order->client_id = $Order->Client->name.' '.$Order->Client->surname;
            }
            if (in_array('client_id_number', $columns) && $Order->Client) {
                $Order->client_id_number = $Order->Client->id_number;
            }
            if (in_array('client_legal_status', $columns) && $Order->Client) {
                $Order->client_legal_status = Arr::get($LegalTypes, ($Order->Client->org_legal_type_id ?? 0));
            }
            if (in_array('worker_id', $columns) && $Order->Worker) {
                $Order->worker_id = $Order->Worker->name.' '.$Order->Worker->surname;
            }
            if (in_array('worker_id_number', $columns) && $Order->Worker) {
                $Order->worker_id_number = $Order->Worker->id_number;
            }
            if (in_array('worker_legal_status', $columns) && $Order->Worker) {
                $Order->worker_legal_status = Arr::get($LegalTypes, ($Order->Worker->org_legal_type_id ?? 0));
            }
            if (in_array('payment_type', $columns)) {
                $Order->payment_type = Arr::get($this->payment_types, $Order->payment_type);
            }
            if (in_array('status_id', $columns)) {
                $Order->status_id = Arr::get($this->statuses, $Order->status_id);
            }

        });
        return $Orders->toArray();
    }

    public function getLegalTypes() {
        return (new LegalType)->get()->pluck('title', 'id')->toArray();
    }

    public function getCurrentFilters()
    {
        $filters = [];
        foreach (\Session::get('widget', []) as $name => $item) {
            if (str_contains($name, 'Filter')) {
                $filter = @unserialize(@base64_decode($item));
                if ($filter) {
                    $filters[] = $filter;
                }
            }
        }

        return $filters;
    }


}