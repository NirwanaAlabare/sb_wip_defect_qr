<?php

namespace App\Exports;

use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectInOut;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DefectInOutExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $dateFrom, $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function view(): View
    {
        $defectInOutQuery = DefectInOut::selectRaw("
                output_defect_in_out.created_at time_in,
                output_defect_in_out.reworked_at time_out,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) sewing_line,
                output_defect_in_out.output_type,
                output_defect_in_out.kode_numbering,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) no_ws,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) style,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.color ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.color ELSE so_det.color END) END) color,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) size,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) defect_type,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_areas_packing.defect_area ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_areas_finish.defect_area ELSE output_defect_areas.defect_area END) END) defect_area,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.gambar ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.gambar ELSE master_plan.gambar END) END) gambar,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.defect_area_x ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.defect_area_x ELSE output_defects.defect_area_x END) END) defect_area_x,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.defect_area_y ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.defect_area_y ELSE output_defects.defect_area_y END) END) defect_area_y,
                output_defect_in_out.status
            ")->
            // Defect
            leftJoin("output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            // Defect Packing
            leftJoin("output_defects_packing", "output_defects_packing.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defect_types as output_defect_types_packing", "output_defect_types_packing.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_packing", "output_defect_areas_packing.id", "=", "output_defects_packing.defect_area_id")->
            leftJoin("so_det as so_det_packing", "so_det_packing.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("so as so_packing", "so_packing.id", "=", "so_det_packing.id_so")->
            leftJoin("act_costing as act_costing_packing", "act_costing_packing.id", "=", "so_packing.id_cost")->
            leftJoin("master_plan as master_plan_packing", "master_plan_packing.id", "=", "output_defects_packing.master_plan_id")->
            // Defect Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defect_types as output_defect_types_finish", "output_defect_types_finish.id", "=", "output_check_finishing.defect_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_finish", "output_defect_areas_finish.id", "=", "output_check_finishing.defect_area_id")->
            leftJoin("so_det as so_det_finish", "so_det_finish.id", "=", "output_check_finishing.so_det_id")->
            leftJoin("so as so_finish", "so_finish.id", "=", "so_det_finish.id_so")->
            leftJoin("act_costing as act_costing_finish", "act_costing_finish.id", "=", "so_finish.id_cost")->
            leftJoin("master_plan as master_plan_finish", "master_plan_finish.id", "=", "output_check_finishing.master_plan_id")->
            // Conditional
            where("output_defect_in_out.type", strtolower(Auth::user()->Groupp))->
            whereBetween("output_defect_in_out.created_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
            whereRaw("
                (
                    output_defect_in_out.id IS NOT NULL AND
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qc' THEN output_defects.id ELSE null END) END) END) IS NOT NULL
                )
            ")->
            groupBy("output_defect_in_out.id")->
            get();

        return view('exports.defect-in-out', [
            'defectInOut' => $defectInOutQuery
        ]);
    }
}
