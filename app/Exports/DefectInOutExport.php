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
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line WHEN output_defect_in_out.output_type = 'finishing_proses' THEN master_plan_finishing_proses.sewing_line ELSE master_plan.sewing_line END) sewing_line,
                output_defect_in_out.output_type,
                output_defect_in_out.kode_numbering,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno WHEN output_defect_in_out.output_type = 'finishing_proses' THEN act_costing_finishing_proses.kpno ELSE act_costing.kpno END) no_ws,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.styleno WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno WHEN output_defect_in_out.output_type = 'finishing_proses' THEN act_costing_finishing_proses.styleno ELSE act_costing.styleno END) style,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.color WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.color WHEN output_defect_in_out.output_type = 'finishing_proses' THEN so_det_finishing_proses.color ELSE so_det.color END) color,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.size WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.size WHEN output_defect_in_out.output_type = 'finishing_proses' THEN so_det_finishing_proses.size ELSE so_det.size END) size,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type WHEN output_defect_in_out.output_type = 'finishing_proses' THEN output_defect_types_finishing_proses.defect_type ELSE output_defect_types.defect_type END) defect_type,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_areas_packing.defect_area WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_areas_finish.defect_area WHEN output_defect_in_out.output_type = 'finishing_proses' THEN output_defect_areas_finishing_proses.defect_area ELSE output_defect_areas.defect_area END) defect_area,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.gambar WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.gambar WHEN output_defect_in_out.output_type = 'finishing_proses' THEN master_plan_finishing_proses.gambar ELSE master_plan.gambar END) gambar,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.defect_area_x WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.defect_area_x WHEN output_defect_in_out.output_type = 'finishing_proses' THEN output_secondary_out_defect.defect_area_x ELSE output_defects.defect_area_x END) defect_area_x,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.defect_area_y WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.defect_area_y WHEN output_defect_in_out.output_type = 'finishing_proses' THEN output_secondary_out_defect.defect_area_y ELSE output_defects.defect_area_y END) defect_area_y,
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
            // Defect Finishing Proses
            leftJoin("output_secondary_out", "output_secondary_out.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_secondary_out_defect", "output_secondary_out_defect.secondary_out_id", "=", "output_secondary_out.id")->
            leftJoin("output_defect_types as output_defect_types_finishing_proses", "output_defect_types_finishing_proses.id", "=", "output_secondary_out_defect.defect_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_finishing_proses", "output_defect_areas_finishing_proses.id", "=", "output_secondary_out_defect.defect_area_id")->
            leftJoin("output_secondary_in", "output_secondary_in.id", "=", "output_secondary_out.secondary_in_id")->
            leftJoin("output_rfts", "output_rfts.id", "=", "output_secondary_in.rft_id")->
            leftJoin("so_det as so_det_finishing_proses", "so_det_finishing_proses.id", "=", "output_rfts.so_det_id")->
            leftJoin("master_plan as master_plan_finishing_proses", "master_plan_finishing_proses.id", "=", "output_rfts.master_plan_id")->
            leftJoin("act_costing as act_costing_finishing_proses", "act_costing_finishing_proses.id", "=", "master_plan_finishing_proses.id_ws")->
            // Conditional
            where("output_defect_in_out.type", strtolower(Auth::user()->Groupp))->
            whereBetween("output_defect_in_out.created_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
            whereRaw("
                (
                    output_defect_in_out.id IS NOT NULL AND 
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'finishing_proses' THEN output_secondary_out.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qc' THEN output_defects.id ELSE NULL END) END) END) END) IS NOT NULL
                )
            ")->
            groupBy("output_defect_in_out.id")->
            get();

        return view('exports.defect-in-out', [
            'defectInOut' => $defectInOutQuery
        ]);
    }
}
