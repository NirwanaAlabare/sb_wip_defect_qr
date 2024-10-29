<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\DefectInOut;

class HistoryContent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $dateFrom;
    public $dateTo;
    public $defectInOutSearch;

    public function mount()
    {
        $this->dateFrom = $this->dateFrom ? $this->dateFrom : date('Y-m-d');
        $this->dateTo = $this->dateTo ? $this->dateTo : date('Y-m-d');
    }

    public function updatingDefectInOutSearch()
    {
        $this->resetPage("defectInOutPage");
    }

    public function render()
    {
        $masterPlan = session()->get('orderInfo');
        $this->masterPlan = $masterPlan ? $masterPlan->id : null;

        $defectInOutQuery = DefectInOut::selectRaw("
                COALESCE(output_defect_in_out.reworked_at, output_defect_in_out.updated_at) time,
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_defects.defect_type_id,
                output_defect_types.defect_type,
                output_defects.so_det_id,
                so_det.size,
                COUNT(output_defect_in_out.id) qty,
                output_defect_in_out.status
            ")->
            leftJoin("output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            where("output_defect_in_out.type", Auth::user()->Groupp);
            if ($this->defectInOutSearch) {
                $defectInOutQuery->whereRaw("(
                    COALESCE(output_defect_in_out.reworked_at, output_defect_in_out.updated_at) LIKE '%".$this->defectInOutSearch."%' OR
                    master_plan.tgl_plan LIKE '%".$this->defectInOutSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInOutSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInOutSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInOutSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInOutSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInOutSearch."%' OR
                    output_defect_in_out.status LIKE '%".$this->defectInOutSearch."%' OR
                    so_det.size LIKE '%".$this->defectInOutSearch."%'
                )");
            }
            if ($this->dateFrom) {
                $defectInOutQuery->whereRaw("DATE(COALESCE(output_defect_in_out.reworked_at, output_defect_in_out.updated_at)) >= '".$this->dateFrom."'");
            }
            if ($this->dateTo) {
                $defectInOutQuery->whereRaw("DATE(COALESCE(output_defect_in_out.reworked_at, output_defect_in_out.updated_at)) <= '".$this->dateTo."'");
            }
            $latestDefectInOut = $defectInOutQuery->
                groupByRaw("
                    master_plan.sewing_line,
                    master_plan.id,
                    output_defect_types.id,
                    output_defects.so_det_id,
                    COALESCE(output_defect_in_out.reworked_at, output_defect_in_out.updated_at)
                ")->
                orderBy("output_defect_in_out.updated_at", "desc")->
                orderBy("output_defect_in_out.reworked_at", "desc")->
                paginate(10, ['*'], 'lastDefectOut');

        return view('livewire.history-content', [
            'latestDefectInOut' => $latestDefectInOut,
        ]);
    }
}
