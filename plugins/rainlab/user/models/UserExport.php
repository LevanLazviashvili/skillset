<?php namespace RainLab\User\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use skillset\details\Models\LegalType;

class UserExport extends \Backend\Models\ExportModel
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

    public $userType = [
        0 => 'დამკვეთი',
        1 => 'შემსრულებელი'
    ];



    public function exportData($columns, $sessionKey = null)
    {
        $UserQuery = User::get();
        $LegalTypes = $this->getLegalTypes();
        $UserQuery->each(function($User) use ($columns, $LegalTypes) {

            $User->addVisible($columns);
            if (in_array('full_name', $columns)) {
                $User->full_name = $User->name.' '.$User->surname;
            }
            if (in_array('user_type', $columns)) {
                $User->user_type = Arr::get($this->userType, $User->user_type, '');
            }
            if (in_array('org_legal_type_id', $columns)) {
                $User->org_legal_type_id = Arr::get($LegalTypes, $User->org_legal_type_id ?? 0, '');
            }
            if (in_array('is_unactive', $columns)) {
                $User->is_unactive = $User->is_unactive ? 'არააქტიური' : 'აქტიური';
            }
        });
        return $UserQuery->toArray();
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