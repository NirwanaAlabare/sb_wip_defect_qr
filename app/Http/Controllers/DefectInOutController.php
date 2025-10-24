<?php

namespace App\Http\Controllers;

use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use App\Models\SignalBit\OutputFinishing;
use App\Models\SignalBit\DefectInOut;
use App\Exports\DefectInOutExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class DefectInOutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index($id)
    // {
    //     $orderInfo = MasterPlan::selectRaw("
    //             master_plan.id as id,
    //             master_plan.tgl_plan as tgl_plan,
    //             REPLACE(master_plan.sewing_line, '_', ' ') as sewing_line,
    //             act_costing.kpno as ws_number,
    //             act_costing.styleno as style_name,
    //             mastersupplier.supplier as buyer_name,
    //             so_det.styleno_prod as reff_number,
    //             master_plan.color as color,
    //             so_det.size as size,
    //             so.qty as qty_order,
    //             CONCAT(masterproduct.product_group, ' - ', masterproduct.product_item) as product_type
    //         ")
    //         ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
    //         ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
    //         ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
    //         ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
    //         ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
    //         ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
    //         ->where('so_det.cancel', 'N')
    //         ->where('master_plan.cancel', 'N')
    //         ->where('master_plan.id', $id)
    //         ->first();

    //     $orderWsDetailsSql = MasterPlan::selectRaw("
    //             master_plan.id as id,
    //             master_plan.tgl_plan as tgl_plan,
    //             master_plan.color as color,
    //             mastersupplier.supplier as buyer_name,
    //             act_costing.styleno as style_name,
    //             mastersupplier.supplier as buyer_name
    //         ")
    //         ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
    //         ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
    //         ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
    //         ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
    //         ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
    //         ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
    //         ->where('so_det.cancel', 'N')
    //         ->where('master_plan.cancel', 'N');
    //         if (Auth::user()->Groupp != "ALLSEWING") {
    //             $orderWsDetailsSql->where('master_plan.sewing_line', Auth::user()->username);
    //         }
    //     $orderWsDetails = $orderWsDetailsSql->where('act_costing.kpno', $orderInfo->ws_number)
    //         ->where('master_plan.tgl_plan', $orderInfo->tgl_plan)
    //         ->groupBy(
    //             'master_plan.id',
    //             'master_plan.tgl_plan',
    //             'master_plan.color',
    //             'mastersupplier.supplier',
    //             'act_costing.styleno',
    //             'mastersupplier.supplier'
    //         )->get();

    //     return view('production-panel', ['orderInfo' => $orderInfo, 'orderWsDetails' => $orderWsDetails]);
    // }

    public function getMasterPlan(Request $request) {
        $additionalQuery = "";
        if ($request->date) {
            $additionalQuery .= " AND master_plan.tgl_plan = '".$request->date."' ";
        }
        if ($request->line) {
            $additionalQuery .= " AND master_plan.sewing_line = '".$request->line."' ";
        }

        $masterPlans = MasterPlan::selectRaw('
                master_plan.id,
                master_plan.tgl_plan as tanggal,
                master_plan.id_ws as id_ws,
                act_costing.kpno as no_ws,
                act_costing.styleno as style,
                master_plan.color as color
            ')->
            leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')->
            whereRaw('
                master_plan.cancel != "Y"
                '.$additionalQuery.'
            ')->
            get();

        return $masterPlans;
    }

    public function getSize(Request $request) {
        if ($request->master_plan) {
            $sizes = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.id = '".$request->master_plan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            orderBy("so_det.id")->
            get();

            return $sizes;
        }

        return null;
    }

    public function getDefectType(Request $request) {
        $additionalQuery = "";
        if ($request->date) {
            $additionalQuery .= " AND master_plan.tgl_plan = '".$request->date."' ";
        }
        if ($request->line) {
            $additionalQuery .= " AND master_plan.sewing_line = '".$request->line."' ";
        }
        if ($request->master_plan) {
            $additionalQuery .= " AND master_plan.id = '".$request->master_plan."' ";
        }
        if ($request->size) {
            $additionalQuery .= " AND output_defects.so_det_id = '".$request->size."' ";
        }
        if ($request->defect_area) {
            $additionalQuery .= " AND output_defects.defect_area_id = '".$request->defect_area."' ";
        }

        $defects = DB::table("output_defects")->selectRaw("
                output_defects.defect_type_id as id,
                output_defect_types.defect_type,
                COUNT(output_defects.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            whereRaw("
                output_defects.defect_status = 'defect'
                and output_defect_types.allocation = '".Auth::user()->Groupp."'
                ".$additionalQuery."
            ")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("output_defects.defect_type_id")->
            orderBy("output_defect_types.defect_type")->
            get();

        return $defects;
    }

    public function getDefectArea(Request $request) {
        $additionalQuery = "";
        if ($request->date) {
            $additionalQuery .= " AND master_plan.tgl_plan = '".$request->date."' ";
        }
        if ($request->line) {
            $additionalQuery .= " AND master_plan.sewing_line = '".$request->line."' ";
        }
        if ($request->master_plan) {
            $additionalQuery .= " AND master_plan.id = '".$request->master_plan."' ";
        }
        if ($request->size) {
            $additionalQuery .= " AND output_defects.so_det_id = '".$request->size."' ";
        }
        if ($request->defect_type) {
            $additionalQuery .= " AND output_defects.defect_type_id = '".$request->defect_type."' ";
        }

        $defects = DB::table("output_defects")->selectRaw("
                output_defects.defect_area_id as id,
                output_defect_areas.defect_area,
                COUNT(output_defects.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
            whereRaw("
                output_defects.defect_status = 'defect'
                and output_defect_types.allocation = '".Auth::user()->Groupp."'
                ".$additionalQuery."
            ")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("output_defects.defect_area_id")->
            orderBy("output_defect_areas.defect_area")->
            get();

        return $defects;
    }

    public function getDefectInOutDaily(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        $defectInOutDaily = DefectInOut::selectRaw("
                DATE(output_defect_in_out.created_at) tanggal,
                SUM(CASE WHEN (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_defects.id END) END) IS NOT NULL THEN 1 ELSE 0 END) total_in,
                SUM(CASE WHEN (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_defects.id END) END) IS NOT NULL AND output_defect_in_out.status = 'defect' THEN 1 ELSE 0 END) total_process,
                SUM(CASE WHEN (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_defects.id END) END) IS NOT NULL AND output_defect_in_out.status = 'reworked' THEN 1 ELSE 0 END) total_out
            ")->
            leftJoin("output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defects_packing", "output_defects_packing.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_defect_in_out.defect_id")->
            where("output_defect_in_out.type", strtolower(Auth::user()->Groupp))->
            whereBetween("output_defect_in_out.created_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            groupByRaw("DATE(output_defect_in_out.created_at)")->
            get();

        return DataTables::of($defectInOutDaily)->toJson();
    }

    public function getDefectInOutDetail(Request $request) {
        $defectInOutQuery = DefectInOut::selectRaw("
                output_defect_in_out.created_at time_in,
                output_defect_in_out.reworked_at time_out,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) sewing_line,
                output_defect_in_out.output_type,
                output_defect_in_out.kode_numbering,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) no_ws,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) style,
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
            whereBetween("output_defect_in_out.created_at", [$request->tanggal." 00:00:00", $request->tanggal." 23:59:59"])->
            whereRaw("
                (
                    output_defect_in_out.id IS NOT NULL AND
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qc' THEN output_defects.id ELSE null END) END) END) IS NOT NULL
                    ".($request->line ? "AND (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qc' THEN master_plan.sewing_line ELSE null END) END) END) LIKE '%".$request->line."%'" : "")."
                    ".($request->departemen && $request->departemen != "all" ? "AND output_defect_in_out.output_type = '".$request->departemen."'" : "")."
                )
            ")->
            groupBy("output_defect_in_out.id")->
            get();

            return DataTables::of($defectInOutQuery)->toJson();
    }

    public function getDefectInOutDetailTotal(Request $request) {
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
            whereBetween("output_defect_in_out.created_at", [$request->tanggal." 00:00:00", $request->tanggal." 23:59:59"])->
            whereRaw("
                (
                    output_defect_in_out.id IS NOT NULL AND
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qc' THEN output_defects.id ELSE null END) END) END) IS NOT NULL
                    ".($request->line ? "AND (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qc' THEN master_plan.sewing_line ELSE null END) END) END) LIKE '%".$request->line."%'" : "")."
                    ".($request->departemen && $request->departemen != "all" ? "AND output_defect_in_out.output_type = '".$request->departemen."'" : "")."
                )
            ")->
            groupBy("output_defect_in_out.id")->
            get();

        return array("defectIn" => $defectInOutQuery->count(), "defectProcess" => $defectInOutQuery->where("status", "defect")->count(), "defectOut" => $defectInOutQuery->where("status", "reworked")->count());
    }

    public function getDefectInList(Request $request)
    {
        $defectInSearch = "";
        if ($request->defectInSearch) {
            $defectInSearch = "
                AND (
                    master_plan.tgl_plan LIKE '%".$request->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$request->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$request->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$request->defectInSearch."%' OR
                    master_plan.color LIKE '%".$request->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$request->defectInSearch."%' OR
                    so_det.size LIKE '%".$request->defectInSearch."%' OR
                    output_defects.kode_numbering LIKE '%".$request->defectInSearch."%'
                )
            ";
        }

        $defectInOutputType = "";
        if ($request->defectInOutputType != "all") {
            $defectInOutputType = "
                AND output_type = '".$request->defectInOutputType."'
            ";
        }

        $defectInLine = "";
        if ($request->defectInLine) {
            $defectInLine = "
                AND master_plan.sewing_line = '".$request->defectInLine."'
            ";
        }

        $defectInSize = "";
        $defectInSizePacking = "";
        $defectInSizeFinishing = "";
        if ($request->defectInSize) {
            $defectInSize = "
                AND output_defects.so_det_id = '".$request->defectInSelectedSize."'
            ";
            $defectInSizePacking = "
                AND output_defects_packing.so_det_id = '".$request->defectInSelectedSize."'
            ";
            $defectInSizeFinishing = "
                AND output_check_finishing.so_det_id = '".$request->defectInSelectedSize."'
            ";
        }

        $defectInType = "";
        $defectInTypePacking = "";
        $defectInTypeFinishing = "";
        if ($request->defectInSelectedType) {
            $defectInType = "
                AND output_defects.defect_type_id = '".$request->defectInSelectedType."'
            ";
            $defectInTypePacking = "
                AND output_defects_packing.defect_type_id = '".$request->defectInSelectedType."'
            ";
            $defectInTypeFinishing = "
                AND output_check_finishing.defect_type_id = '".$request->defectInSelectedType."'
            ";
        }

        $defectInMasterPlan = "";
        if ($request->defectInSelectedMasterPlan) {
            $defectInMasterPlan = "
                AND master_plan.id = '".$request->defectInSelectedMasterPlan."'
            ";
        }

        $defectInFilterKode = "";
        if ($request->defectInFilterKode) {
            $defectInFilterKode = " AND kode_numbering LIKE '%".$request->defectInFilterKode."%' ";
        }

        $defectInFilterWaktu = "";
        if ($request->defectInFilterWaktu) {
            $defectInFilterWaktu = " AND defect_time LIKE '%".$request->defectInFilterWaktu."%' ";
        }

        $defectInFilterLine = "";
        if ($request->defectInFilterLine) {
            $defectInFilterLine = " AND sewing_line LIKE '%".str_replace(" ", "_", $request->defectInFilterLine)."%' ";
        }

        $defectInFilterSize = "";
        if ($request->defectInFilterSize) {
            $defectInFilterSize = " AND size LIKE '%".$request->defectInFilterSize."%' ";
        }

        $defectInFilterType = "";
        if ($request->defectInFilterType) {
            $defectInFilterType = " AND defect_type LIKE '%".$request->defectInFilterType."%' ";
        }

        $defectInFilterMasterPlan = "";
        if ($request->defectInFilterMasterPlan) {
            $defectInFilterMasterPlan = "
                AND (
                    ws LIKE '%".$request->defectInFilterMasterPlan."%' OR
                    style LIKE '%".$request->defectInFilterMasterPlan."%' OR
                    color LIKE '%".$request->defectInFilterMasterPlan."%'
                )
            ";
        }

        $defectInList = collect(
            DB::select("
                SELECT * FROM (
                    SELECT
                        output_defects.id,
                        output_defects.updated_at defect_time,
                        userpassword.username sewing_line,
                        output_defects.kode_numbering,
                        output_defects.so_det_id,
                        output_defect_types.defect_type,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size,
                        userpassword.username,
                        output_defect_in_out.id defect_in_id,
                        'qc' output_type,
                        COUNT(output_defects.id) as defect_qty
                    FROM
                        `output_defects`
                        LEFT JOIN `user_sb_wip` ON `user_sb_wip`.`id` = `output_defects`.`created_by`
                        LEFT JOIN `userpassword` ON `userpassword`.`line_id` = `user_sb_wip`.`line_id`
                        LEFT JOIN `so_det` ON `so_det`.`id` = `output_defects`.`so_det_id`
                        LEFT JOIN `master_plan` ON `master_plan`.`id` = `output_defects`.`master_plan_id`
                        LEFT JOIN `act_costing` ON `act_costing`.`id` = `master_plan`.`id_ws`
                        LEFT JOIN `output_defect_types` ON `output_defect_types`.`id` = `output_defects`.`defect_type_id`
                        LEFT JOIN `output_defect_in_out` ON `output_defect_in_out`.`defect_id` = `output_defects`.`id`
                        AND `output_defect_in_out`.`output_type` = 'qc'
                    WHERE
                        `output_defects`.`id` IS NOT NULL
                        AND output_defects.master_plan_id is not null
                        AND `output_defects`.`defect_status` = 'defect'
                        AND `output_defect_types`.`allocation` = '".Auth::user()->Groupp."'
                        AND output_defects.updated_at >= '2025-10-01 00:00:00'
                        AND output_defect_in_out.id is null
                        ".$defectInSearch."
                        ".$defectInLine."
                        ".$defectInSize."
                        ".$defectInType."
                        ".$defectInMasterPlan."
                    GROUP BY
                        output_defects.id
                    UNION ALL
                    SELECT
                        output_defects_packing.id,
                        output_defects_packing.updated_at defect_time,
                        userpassword.username sewing_line,
                        output_defects_packing.kode_numbering,
                        output_defects_packing.so_det_id,
                        output_defect_types.defect_type,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size,
                        userpassword.username,
                        output_defect_in_out.id defect_in_id,
                        'packing' output_type,
                        COUNT(output_defects_packing.id) as defect_qty
                    FROM
                        `output_defects_packing`
                        LEFT JOIN `user_sb_wip` ON `user_sb_wip`.`id` = `output_defects_packing`.`created_by`
                        LEFT JOIN `userpassword` ON `userpassword`.`line_id` = `user_sb_wip`.`line_id`
                        LEFT JOIN `so_det` ON `so_det`.`id` = `output_defects_packing`.`so_det_id`
                        LEFT JOIN `master_plan` ON `master_plan`.`id` = `output_defects_packing`.`master_plan_id`
                        LEFT JOIN `act_costing` ON `act_costing`.`id` = `master_plan`.`id_ws`
                        LEFT JOIN `output_defect_types` ON `output_defect_types`.`id` = `output_defects_packing`.`defect_type_id`
                        LEFT JOIN `output_defect_in_out` ON `output_defect_in_out`.`defect_id` = `output_defects_packing`.`id`
                        AND `output_defect_in_out`.`output_type` = 'packing'
                    WHERE
                        `output_defects_packing`.`id` IS NOT NULL
                        AND output_defects_packing.master_plan_id is not null
                        AND `output_defects_packing`.`defect_status` = 'defect'
                        AND `output_defect_types`.`allocation` = '".Auth::user()->Groupp."'
                        AND output_defects_packing.updated_at >= '2025-10-01 00:00:00'
                        AND output_defect_in_out.id is null
                        ".$defectInSearch."
                        ".$defectInLine."
                        ".$defectInSizePacking."
                        ".$defectInTypePacking."
                        ".$defectInMasterPlan."
                    GROUP BY
                        output_defects_packing.id
                    UNION ALL
                    SELECT
                        output_check_finishing.id,
                        output_check_finishing.updated_at defect_time,
                        userpassword.username sewing_line,
                        output_check_finishing.kode_numbering,
                        output_check_finishing.so_det_id,
                        output_defect_types.defect_type,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size,
                        userpassword.username,
                        output_defect_in_out.id defect_in_id,
                        'qcf' output_type,
                        COUNT(output_check_finishing.id) as defect_qty
                    FROM
                        `output_check_finishing`
                        LEFT JOIN `user_sb_wip` ON `user_sb_wip`.`id` = `output_check_finishing`.`created_by`
                        LEFT JOIN `userpassword` ON `userpassword`.`line_id` = `user_sb_wip`.`line_id`
                        LEFT JOIN `so_det` ON `so_det`.`id` = `output_check_finishing`.`so_det_id`
                        LEFT JOIN `master_plan` ON `master_plan`.`id` = `output_check_finishing`.`master_plan_id`
                        LEFT JOIN `act_costing` ON `act_costing`.`id` = `master_plan`.`id_ws`
                        LEFT JOIN `output_defect_types` ON `output_defect_types`.`id` = `output_check_finishing`.`defect_type_id`
                        LEFT JOIN `output_defect_in_out` ON `output_defect_in_out`.`defect_id` = `output_check_finishing`.`id`
                        AND `output_defect_in_out`.`output_type` = 'qcf'
                    WHERE
                        `output_check_finishing`.`status` = 'defect'
                        AND `output_defect_types`.`allocation` = '".Auth::user()->Groupp."'
                        AND output_check_finishing.updated_at >= '2025-10-01 00:00:00'
                        AND output_defect_in_out.id is null
                        ".$defectInSearch."
                        ".$defectInLine."
                        ".$defectInSizeFinishing."
                        ".$defectInTypeFinishing."
                        ".$defectInMasterPlan."
                    GROUP BY
                        output_check_finishing.id
                ) all_defect
                where
                    id is not null
                    ".$defectInOutputType."
                    ".$defectInFilterKode."
                    ".$defectInFilterWaktu."
                    ".$defectInFilterLine."
                    ".$defectInFilterMasterPlan."
                    ".$defectInFilterSize."
                    ".$defectInFilterType."
            ")
        );

        return Datatables::of($defectInList)->toJson();
    }

    public function submitDefectIn(Request $request)
    {
        $status = "";
        $message = "";

        if ($request->scannedDefectIn) {
            $scannedDefect = null;

            if ($request->defectInOutputType == "all") {
                $scannedDefect = collect(DB::select("
                    SELECT * FROM (
                        SELECT
                            output_defects.id,
                            output_defects.kode_numbering,
                            output_defects.so_det_id,
                            output_defect_types.defect_type,
                            act_costing.kpno ws,
                            act_costing.styleno style,
                            so_det.color,
                            so_det.size,
                            userpassword.username,
                            output_defect_in_out.id defect_in_id,
                            'qc' output_type
                        FROM
                            `output_defects`
                            LEFT JOIN `user_sb_wip` ON `user_sb_wip`.`id` = `output_defects`.`created_by`
                            LEFT JOIN `userpassword` ON `userpassword`.`line_id` = `user_sb_wip`.`line_id`
                            LEFT JOIN `so_det` ON `so_det`.`id` = `output_defects`.`so_det_id`
                            LEFT JOIN `master_plan` ON `master_plan`.`id` = `output_defects`.`master_plan_id`
                            LEFT JOIN `act_costing` ON `act_costing`.`id` = `master_plan`.`id_ws`
                            LEFT JOIN `output_defect_types` ON `output_defect_types`.`id` = `output_defects`.`defect_type_id`
                            LEFT JOIN `output_defect_in_out` ON `output_defect_in_out`.`defect_id` = `output_defects`.`id`
                            AND `output_defect_in_out`.`output_type` = 'qc'
                        WHERE
                            `output_defects`.`id` IS NOT NULL
                            AND `output_defects`.`defect_status` = 'defect'
                            AND `output_defect_types`.`allocation` = '".Auth::user()->Groupp."'
                            AND `output_defects`.`kode_numbering` = '".$request->scannedDefectIn."'
                        UNION ALL
                        SELECT
                            output_defects_packing.id,
                            output_defects_packing.kode_numbering,
                            output_defects_packing.so_det_id,
                            output_defect_types.defect_type,
                            act_costing.kpno ws,
                            act_costing.styleno style,
                            so_det.color,
                            so_det.size,
                            userpassword.username,
                            output_defect_in_out.id defect_in_id,
                            'packing' output_type
                        FROM
                            `output_defects_packing`
                            LEFT JOIN `user_sb_wip` ON `user_sb_wip`.`id` = `output_defects_packing`.`created_by`
                            LEFT JOIN `userpassword` ON `userpassword`.`line_id` = `user_sb_wip`.`line_id`
                            LEFT JOIN `so_det` ON `so_det`.`id` = `output_defects_packing`.`so_det_id`
                            LEFT JOIN `master_plan` ON `master_plan`.`id` = `output_defects_packing`.`master_plan_id`
                            LEFT JOIN `act_costing` ON `act_costing`.`id` = `master_plan`.`id_ws`
                            LEFT JOIN `output_defect_types` ON `output_defect_types`.`id` = `output_defects_packing`.`defect_type_id`
                            LEFT JOIN `output_defect_in_out` ON `output_defect_in_out`.`defect_id` = `output_defects_packing`.`id`
                            AND `output_defect_in_out`.`output_type` = 'packing'
                        WHERE
                            `output_defects_packing`.`id` IS NOT NULL
                            AND `output_defects_packing`.`defect_status` = 'defect'
                            AND `output_defect_types`.`allocation` = '".Auth::user()->Groupp."'
                            AND `output_defects_packing`.`kode_numbering` = '".$request->scannedDefectIn."'
                        UNION ALL
                        SELECT
                            output_check_finishing.id,
                            output_check_finishing.kode_numbering,
                            output_check_finishing.so_det_id,
                            output_defect_types.defect_type,
                            act_costing.kpno ws,
                            act_costing.styleno style,
                            so_det.color,
                            so_det.size,
                            userpassword.username,
                            output_defect_in_out.id defect_in_id,
                            'qcf' output_type
                        FROM
                            `output_check_finishing`
                            LEFT JOIN `user_sb_wip` ON `user_sb_wip`.`id` = `output_check_finishing`.`created_by`
                            LEFT JOIN `userpassword` ON `userpassword`.`line_id` = `user_sb_wip`.`line_id`
                            LEFT JOIN `so_det` ON `so_det`.`id` = `output_check_finishing`.`so_det_id`
                            LEFT JOIN `master_plan` ON `master_plan`.`id` = `output_check_finishing`.`master_plan_id`
                            LEFT JOIN `act_costing` ON `act_costing`.`id` = `master_plan`.`id_ws`
                            LEFT JOIN `output_defect_types` ON `output_defect_types`.`id` = `output_check_finishing`.`defect_type_id`
                            LEFT JOIN `output_defect_in_out` ON `output_defect_in_out`.`defect_id` = `output_check_finishing`.`id`
                            AND `output_defect_in_out`.`output_type` = 'qcf'
                        WHERE
                            output_check_finishing.id IS NOT NULL
                            AND `output_check_finishing`.`status` = 'defect'
                            AND `output_defect_types`.`allocation` = '".Auth::user()->Groupp."'
                            AND `output_check_finishing`.`kode_numbering` = '".$request->scannedDefectIn."'
                    ) all_defect
                "))->first();
            } else if ($request->defectInOutputType == "packing") {
                $scannedDefect = DB::table("output_defects_packing")->selectRaw("
                    output_defects_packing.id,
                    output_defects_packing.kode_numbering,
                    output_defects_packing.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_defect_in_out.id defect_in_id,
                    'packing' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects_packing.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
                leftJoin("output_defect_in_out", function ($join) {
                    $join->on("output_defect_in_out.id", "=", "output_defects_packing.id");
                    $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
                })->
                whereNotNull("output_defects_packing.id")->
                where("output_defects_packing.defect_status", "defect")->
                where("output_defect_types.allocation", Auth::user()->Groupp)->
                where("output_defects_packing.kode_numbering", $request->scannedDefectIn)->
                first();
            } else if ($request->defectInOutputType == "qcf") {
                $scannedDefect = DB::table("output_finishing")->selectRaw("
                    output_check_finishing.id,
                    output_check_finishing.kode_numbering,
                    output_check_finishing.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_defect_in_out.id defect_in_id,
                    'qcf' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                leftJoin("output_defect_in_out", function ($join) {
                    $join->on("output_defect_in_out.id", "=", "output_check_finishing.id");
                    $join->on("output_defect_in_out.output_type", "=", DB::raw("'qcf'"));
                })->
                where("output_check_finishing.status", "defect")->
                where("output_defect_types.allocation", Auth::user()->Groupp)->
                where("output_check_finishing.kode_numbering", $request->scannedDefectIn)->
                first();
            } else {
                $scannedDefect = DB::table("output_defects")->selectRaw("
                    output_defects.id,
                    output_defects.kode_numbering,
                    output_defects.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_defect_in_out.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
                leftJoin("output_defect_in_out", function ($join) {
                    $join->on("output_defect_in_out.id", "=", "output_defects.id");
                    $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
                })->
                whereNotNull("output_defects.id")->
                where("output_defects.defect_status", "defect")->
                where("output_defect_types.allocation", Auth::user()->Groupp)->
                where("output_defects.kode_numbering", $request->scannedDefectIn)->
                first();
            }

            if ($scannedDefect) {
                $defectInOut = DefectInOut::where("defect_id", $scannedDefect->id)->where("output_type", $scannedDefect->output_type)->first();

                if (!$defectInOut) {
                    $createDefectInOut = DefectInOut::create([
                        "defect_id" => $scannedDefect->id,
                        "kode_numbering" => $scannedDefect->kode_numbering,
                        "status" => "defect",
                        "type" => Auth::user()->Groupp,
                        "output_type" => $scannedDefect->output_type,
                        "created_by" => Auth::user()->id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                        "reworked_at" => null
                    ]);

                    if ($createDefectInOut) {

                        $status = "success";
                        $message = "DEFECT '".$scannedDefect->defect_type."' dengan KODE '".$request->scannedDefectIn."' berhasil masuk ke '".Auth::user()->Groupp."'";
                    } else {
                        $status = "error";
                        $message = "Terjadi Kesalahan.";
                    }
                } else {
                    $status = "warning";
                    $message = "QR sudah discan.";
                }
            } else {
                $status = "error";
                $message = "Defect dengan QR '".$request->scannedDefectIn."' tidak ditemukan di '".Auth::user()->Groupp."'";
            }
        } else {
            $status = "error";
            $message = "QR tidak sesuai";
        }

        return array(
            "status" => $status,
            "message" => $message
        );
    }

    public function getDefectOutList(Request $request)
    {
        if ($request->defectOutOutputType == "all" ) {
            $defectOutQuery = DefectInOut::selectRaw("
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.id ELSE master_plan.id END) END) master_plan_id,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.id_ws ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.id_ws ELSE master_plan.id_ws END) END) id_ws,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) sewing_line,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) as ws,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) style,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.color ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.color ELSE master_plan.color END) END) color,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.defect_type_id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.defect_type_id ELSE output_defects.defect_type_id END) END) defect_type_id,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) defect_type,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.so_det_id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.so_det_id ELSE output_defects.so_det_id END) END) so_det_id,
                output_defect_in_out.kode_numbering,
                output_defect_in_out.output_type,
                output_defect_in_out.updated_at defect_time,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) size,
                (CASE WHEN output_defect_in_out.output_type = 'packing' THEN COUNT(output_defects_packing.id) ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN COUNT(output_check_finishing.id) ELSE COUNT(output_defects.id) END) END) defect_qty
            ")->
            // Defect
            leftJoin("output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
            leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
            // Defect Packing
            leftJoin("output_defects_packing", "output_defects_packing.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defect_types as output_defect_types_packing", "output_defect_types_packing.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_packing", "output_defect_areas_packing.id", "=", "output_defects_packing.defect_area_id")->
            leftJoin("so_det as so_det_packing", "so_det_packing.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("so as so_packing", "so_packing.id", "=", "so_det_packing.id_so")->
            leftJoin("act_costing as act_costing_packing", "act_costing_packing.id", "=", "so_packing.id_cost")->
            leftJoin("master_plan as master_plan_packing", "master_plan_packing.id", "=", "output_defects_packing.master_plan_id")->
            leftJoin("userpassword as userpassword_packing", "userpassword.username", "=", "output_defects_packing.created_by")->
            // Defect Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defect_types as output_defect_types_finish", "output_defect_types_finish.id", "=", "output_check_finishing.defect_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_finish", "output_defect_areas_finish.id", "=", "output_check_finishing.defect_area_id")->
            leftJoin("so_det as so_det_finish", "so_det_finish.id", "=", "output_check_finishing.so_det_id")->
            leftJoin("so as so_finish", "so_finish.id", "=", "so_det_finish.id_so")->
            leftJoin("act_costing as act_costing_finish", "act_costing_finish.id", "=", "so_finish.id_cost")->
            leftJoin("master_plan as master_plan_finish", "master_plan_finish.id", "=", "output_check_finishing.master_plan_id")->
            leftJoin("userpassword as userpassword_finish", "userpassword.username", "=", "output_check_finishing.created_by")->
            // Conditional
            whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_defects.id END) END) IS NOT NULL ")->
            whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.allocation ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.allocation ELSE output_defect_types.allocation END) END) = '".Auth::user()->Groupp."' ")->
            where("output_defect_in_out.status", "defect")->
            where("output_defect_in_out.type", Auth::user()->Groupp)->
            whereRaw("YEAR(output_defect_in_out.created_at) = '".date("Y")."'");
            if ($request->defectOutSearch) {
                $defectOutQuery->whereRaw("(
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.tgl_plan ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.tgl_plan ELSE master_plan.tgl_plan END) END) LIKE '%".$request->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) LIKE '%".$request->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) LIKE '%".$request->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) LIKE '%".$request->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.color ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.color ELSE master_plan.color END) END) LIKE '%".$request->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) LIKE '%".$request->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) LIKE '%".$request->defectOutSearch."%' OR
                    output_defect_in_out.kode_numbering LIKE '%".$request->defectOutSearch."%'
                )");
            }
            // if ($request->defectOutDate) {
            //     $defectOutQuery->whereBetween("output_defect_in_out.updated_at", [$request->defectOutDate." 00:00:00", $request->defectOutDate." 23:59:59"]);
            // }
            if ($request->defectOutLine) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) = '".$request->defectOutLine."'");
            }
            if ($request->defectOutSelectedMasterPlan) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.id ELSE master_plan.id END) END) = '".$request->defectOutSelectedMasterPlan."'");
            }
            if ($request->defectOutSelectedSize) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.so_det_id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.so_det_id ELSE output_defects.so_det_id END) END) = '".$request->defectOutSelectedSize."'");
            }
            if ($request->defectOutSelectedType) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.defect_type_id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.defect_type_id ELSE output_defects.defect_type_id END) END) = '".$request->defectOutSelectedType."'");
            };
            if ($request->defectOutFilterKode) {
                $defectOutQuery->whereRaw("output_defect_in_out.kode_numbering LIKE '%".$request->defectOutFilterKode."%'");
            }
            if ($request->defectOutFilterWaktu) {
                $defectOutQuery->whereRaw("output_defect_in_out.updated_at LIKE '%".$request->defectOutFilterWaktu."%'");
            }
            if ($request->defectOutFilterLine) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) LIKE '%".str_replace(" ", "_", $request->defectOutFilterLine)."%'");
            }
            if ($request->defectOutFilterMasterPlan) {
                $defectOutQuery->whereRaw("(
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) LIKE '%".$request->defectOutFilterMasterPlan."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.kpno END) END) LIKE '%".$request->defectOutFilterMasterPlan."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.color ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.color ELSE so_det.color END) END) LIKE '%".$request->defectOutFilterMasterPlan."%'
                )");
            }
            if ($request->defectOutFilterSize) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) LIKE '%".$request->defectOutFilterSize."%'");
            }
            if ($request->defectOutFilterType) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) LIKE '%".$request->defectOutFilterType."%'");
            }
        } else {
            $defectOutQuery = DefectInOut::selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_defects.defect_type_id,
                output_defect_types.defect_type,
                output_defects.so_det_id,
                output_defect_in_out.kode_numbering,
                output_defect_in_out.output_type,
                output_defect_in_out.updated_at as defect_time,
                so_det.size,
                COUNT(output_defect_in_out.id) defect_qty
            ")->
            leftJoin(($request->defectOutOutputType == 'packing' ? 'output_defects_packing' : ($request->defectOutOutputType == 'qcf' ? 'output_check_finishing' : 'output_defects'))." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            whereNotNull("output_defects.id")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            where("output_defect_in_out.status", "defect")->
            where("output_defect_in_out.output_type", $request->defectOutOutputType)->
            where("output_defect_in_out.type", Auth::user()->Groupp)->
            whereRaw("YEAR(output_defect_in_out.created_at) = '".date("Y")."'");
            if ($request->defectOutSearch) {
                $defectOutQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$request->defectOutSearch."%' OR
                    master_plan.sewing_line LIKE '%".$request->defectOutSearch."%' OR
                    act_costing.kpno LIKE '%".$request->defectOutSearch."%' OR
                    act_costing.styleno LIKE '%".$request->defectOutSearch."%' OR
                    master_plan.color LIKE '%".$request->defectOutSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$request->defectOutSearch."%' OR
                    so_det.size LIKE '%".$request->defectOutSearch."%' OR
                    output_defect_in_out.kode_numbering LIKE '%".$request->defectOutSearch."%'
                )");
            }
            // if ($request->defectOutDate) {
            //     $defectOutQuery->whereBetween("output_defect_in_out.updated_at", [$request->defectOutDate." 00:00:00", $request->defectOutDate." 23:59:59"]);
            // }
            if ($request->defectOutLine) {
                $defectOutQuery->where("master_plan.sewing_line", $request->defectOutLine);
            }
            if ($request->defectOutSelectedMasterPlan) {
                $defectOutQuery->where("master_plan.id", $request->defectOutSelectedMasterPlan);
            }
            if ($request->defectOutSelectedSize) {
                $defectOutQuery->where("output_defects.so_det_id", $request->defectOutSelectedSize);
            }
            if ($request->defectOutSelectedType) {
                $defectOutQuery->where("output_defects.defect_type_id", $request->defectOutSelectedType);
            };
            if ($request->defectOutFilterKode) {
                $defectOutQuery->whereRaw("output_defect_in_out.kode_numbering LIKE '%".$request->defectOutFilterKode."%'");
            }
            if ($request->defectOutFilterWaktu) {
                $defectOutQuery->whereRaw("output_defect_in_out.updated_at LIKE '%".$request->defectOutFilterWaktu."%");
            }
            if ($request->defectOutFilterLine) {
                $defectOutQuery->whereRaw("master_plan.sewing_line LIKE '%".str_replace(" ", "_", $request->defectOutFilterLine)."%'");
            }
            if ($request->defectOutFilterMasterPlan) {
                $defectOutQuery->whereRaw("(
                    act_costing.kpno LIKE '%".$request->defectOutFilterMasterPlan."%' OR
                    act_costing.styleno LIKE '%".$request->defectOutFilterMasterPlan."%' OR
                    so_det.color LIKE '%".$request->defectOutFilterMasterPlan."%'
                )");
            }
            if ($request->defectOutFilterSize) {
                $defectOutQuery->whereRaw("so_det.size LIKE '%".$request->defectOutFilterSize."%'");
            }
            if ($request->defectOutFilterType) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) LIKE '%".$request->defectOutFilterType."%'");
            }
        }

        $defectOutList = $defectOutQuery->
            groupBy("output_defect_in_out.id")->
            orderBy("output_defect_in_out.updated_at", "desc")->
            get();

        return Datatables::of($defectOutList)->toJson();
    }

    public function submitDefectOut(Request $request)
    {
        if ($request->scannedDefectOut) {
            if ($request->defectOutOutputType == "all" ) {
                $scannedDefect = DefectInOut::selectRaw("
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_defects.id END) END) id,
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.kode_numbering ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.kode_numbering ELSE output_defects.kode_numbering END) END) kode_numbering,
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.so_det_id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.so_det_id ELSE output_defects.so_det_id END) END) so_det_id,
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) defect_type,
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) ws,
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) style,
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.color ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.color ELSE so_det.color END) END) color,
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) size,
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN userpassword_packing.username ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN userpassword_finish.username ELSE userpassword.username END) END) username,
                    output_defect_in_out.output_type
                ")->
                // Defect
                leftJoin("output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
                leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                // Defect Packing
                leftJoin("output_defects_packing", "output_defects_packing.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("output_defect_types as output_defect_types_packing", "output_defect_types_packing.id", "=", "output_defects_packing.defect_type_id")->
                leftJoin("output_defect_areas as output_defect_areas_packing", "output_defect_areas_packing.id", "=", "output_defects_packing.defect_area_id")->
                leftJoin("so_det as so_det_packing", "so_det_packing.id", "=", "output_defects_packing.so_det_id")->
                leftJoin("so as so_packing", "so_packing.id", "=", "so_det_packing.id_so")->
                leftJoin("act_costing as act_costing_packing", "act_costing_packing.id", "=", "so_packing.id_cost")->
                leftJoin("master_plan as master_plan_packing", "master_plan_packing.id", "=", "output_defects_packing.master_plan_id")->
                leftJoin("userpassword as userpassword_packing", "userpassword.username", "=", "output_defects_packing.created_by")->
                // Defect Finishing
                leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("output_defect_types as output_defect_types_finish", "output_defect_types_finish.id", "=", "output_check_finishing.defect_type_id")->
                leftJoin("output_defect_areas as output_defect_areas_finish", "output_defect_areas_finish.id", "=", "output_check_finishing.defect_area_id")->
                leftJoin("so_det as so_det_finish", "so_det_finish.id", "=", "output_check_finishing.so_det_id")->
                leftJoin("so as so_finish", "so_finish.id", "=", "so_det_finish.id_so")->
                leftJoin("act_costing as act_costing_finish", "act_costing_finish.id", "=", "so_finish.id_cost")->
                leftJoin("master_plan as master_plan_finish", "master_plan_finish.id", "=", "output_check_finishing.master_plan_id")->
                leftJoin("userpassword as userpassword_finish", "userpassword.username", "=", "output_check_finishing.created_by")->
                // Conditional
                where("output_defect_in_out.status", "defect")->
                where("output_defect_in_out.type", Auth::user()->Groupp)->
                whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.kode_numbering ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.kode_numbering ELSE output_defects.kode_numbering END) END) = '".$request->scannedDefectOut."'")->
                first();
            } else {
                $scannedDefect = DefectInOut::selectRaw("
                    output_defects.id,
                    output_defects.kode_numbering,
                    output_defects.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_defect_in_out.output_type
                ")->
                leftJoin(($request->defectOutOutputType == 'packing' ? 'output_defects_packing' : ($request->defectOutOutputType == 'qcf' ? 'output_check_finishing' : 'output_defects'))." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
                where("output_defect_in_out.status", "defect")->
                where("output_defect_in_out.type", Auth::user()->Groupp)->
                where("output_defect_in_out.output_type", $request->defectOutOutputType)->
                where("output_defects.kode_numbering", $request->scannedDefectOut)->
                first();
            }

            if ($scannedDefect) {
                $defectInOut = DefectInOut::where("defect_id", $scannedDefect->id)->where("output_type", $scannedDefect->output_type)->first();

                if ($defectInOut) {
                    if ($defectInOut->status == "defect") {
                        $updateDefectInOut = DefectInOut::where("defect_id", $scannedDefect->id)->update([
                            "status" => "reworked",
                            "created_by" => Auth::user()->username,
                            "updated_at" => Carbon::now(),
                            "reworked_at" => Carbon::now()
                        ]);

                        if ($updateDefectInOut) {
                            $status = 'success';
                            $message = "DEFECT '".$scannedDefect->defect_type."' dengan KODE '".$request->scannedDefectOut."' berhasil dikeluarkan dari '".Auth::user()->Groupp."'";
                        } else {
                            $status = 'error';
                            $message = "Terjadi kesalahan.";
                        }
                    } else {
                        $status = 'warning';
                        $message = "QR sudah discan di OUT.";
                    }
                } else {
                    $status = 'error';
                    $message = "DEFECT '".$scannedDefect->defect_type."' dengan QR '".$request->scannedDefectOut."' tidak/belum masuk '".Auth::user()->Groupp."'.";
                }
            } else {
                $status = 'error';
                $message = "DEFECT dengan QR '".$request->scannedDefectOut."' tidak ditemukan di '".Auth::user()->Groupp."'.";
            }
        } else {
            $status = 'error';
            $message = "QR tidak sesuai.";
        }

        return array(
            "status" => $status,
            "message" => $message
        );
    }

    public function exportDefectInOut(Request $request) {
        return Excel::download(new DefectInOutExport($request->dateFrom, $request->dateTo), 'Report Defect In Out.xlsx');
    }
}
