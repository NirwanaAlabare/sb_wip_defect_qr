<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\UserPassword;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use App\Models\SignalBit\OutputFinishing;
use App\Models\SignalBit\DefectInOut as DefectInOutModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;
use DB;

class DefectInOut extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $date;

    public $lines;
    public $orders;

    public $defectInShowPage;
    public $defectInDate;
    public $defectInLine;
    public $defectInQty;
    public $defectInOutputType;

    public $defectInDateModal;
    public $defectInOutputModal;
    public $defectInLineModal;
    public $defectInMasterPlanModal;
    public $defectInSizeModal;
    public $defectInTypeModal;
    public $defectInAreaModal;
    public $defectInQtyModal;

    // Filter
    public $defectInFilterKode;
    public $defectInFilterWaktu;
    public $defectInFilterLine;
    public $defectInFilterMasterPlan;
    public $defectInFilterSize;
    public $defectInFilterType;

    public $defectOutShowPage;
    public $defectOutDate;
    public $defectOutLine;
    public $defectOutQty;
    public $defectOutOutputType;

    public $defectOutDateModal;
    public $defectOutOutputModal;
    public $defectOutLineModal;
    public $defectOutMasterPlanModal;
    public $defectOutSizeModal;
    public $defectOutTypeModal;
    public $defectOutAreaModal;
    public $defectOutQtyModal;

    // Filter
    public $defectOutFilterKode;
    public $defectOutFilterWaktu;
    public $defectOutFilterLine;
    public $defectOutFilterMasterPlan;
    public $defectOutFilterSize;
    public $defectOutFilterType;

    public $defectInMasterPlanOutput;
    public $defectOutMasterPlanOutput;

    public $defectInOutShowPage;
    public $defectInSelectedMasterPlan;
    public $defectInSelectedSize;
    public $defectInSelectedType;
    public $defectInSelectedArea;

    public $defectOutSelectedMasterPlan;
    public $defectOutSelectedSize;
    public $defectOutSelectedType;
    public $defectOutSelectedArea;

    public $defectInOutFrom;
    public $defectInOutTo;
    public $defectInOutSearch;
    public $defectInOutOutputType;

    public $scannedDefectIn;
    public $scannedDefectOut;

    public $mode;

    public $productTypeImage;
    public $defectPositionX;
    public $defectPositionY;

    public $loadingMasterPlan;

    public $baseUrl;

    public $listeners = [
        'setDate' => 'setDate',
        'hideDefectAreaImageClear' => 'hideDefectAreaImage'
    ];

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->mode = 'in';
        $this->lines = null;
        $this->orders = null;

        // Defect In init value
        $this->defectInList = null;
        $this->defectInShowPage = 10;
        $this->defectInOutputType = 'all';
        $this->defectInDate = date('Y-m-d');
        $this->defectInLine = null;
        $this->defectInMasterPlan = null;
        $this->defectInSelectedMasterPlan = null;
        $this->defectInSelectedSize = null;
        $this->defectInSelectedType = null;
        $this->defectInSelectedArea = null;
        $this->defectInMasterPlanOutput = null;
        $this->defectInSelectedList = [];
        $this->defectInSearch = null;
        $this->defectInListAllChecked = null;

        $this->defectInFilterKode = null;
        $this->defectInFilterWaktu = null;
        $this->defectInFilterLine = null;
        $this->defectInFilterMasterPlan = null;
        $this->defectInFilterSize = null;
        $this->defectInFilterType = null;

        // Defect Out init value
        $this->defectOutList = null;
        $this->defectOutShowPage = 10;
        $this->defectOutOutputType = 'all';
        $this->defectOutDate = date('Y-m-d');
        $this->defectOutLine = null;
        $this->defectOutMasterPlan = null;
        $this->defectOutSelectedMasterPlan = null;
        $this->defectOutSelectedSize = null;
        $this->defectOutSelectedType = null;
        $this->defectOutSelectedArea = null;
        $this->defectOutMasterPlanOutput = null;
        $this->defectOutSelectedList = [];
        $this->defectOutSearch = null;
        $this->defectOutListAllChecked = false;

        $this->defectOutFilterKode = null;
        $this->defectOutFilterWaktu = null;
        $this->defectOutFilterLine = null;
        $this->defectOutFilterMasterPlan = null;
        $this->defectOutFilterSize = null;
        $this->defectOutFilterType = null;

        $this->scannedDefectIn = null;
        $this->scannedDefectOut = null;

        $this->defectInOutShowPage = 10;
        $this->defectInOutFrom = date("Y-m-d", strtotime("-7 days"));
        $this->defectInOutTo = date("Y-m-d");

        $this->productTypeImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;

        $this->loadingMasterPlan = false;
        $this->baseUrl = url('/');

        $this->emit("qrInputFocus", "in");
    }

    public function changeMode($mode)
    {
        $this->mode = $mode;

        $this->emit('qrInputFocus', $mode);
    }

    // public function updatingDefectInSearch()
    // {
    //     $this->resetPage("defectInPage");
    // }

    // public function updatingDefectOutSearch()
    // {
    //     $this->resetPage("defectOutPage");
    // }

    // public function updatedPaginators($page, $pageName) {
    //     if ($this->defectInListAllChecked == true) {
    //         $this->selectAllDefectIn();
    //     }

    //     if ($this->defectOutListAllChecked == true) {
    //         $this->selectAllDefectOut();
    //     }
    // }

    public function submitDefectIn()
    {
        if ($this->scannedDefectIn) {
            $scannedDefect = null;

            if ($this->defectInOutputType == "all") {
                $scannedDefectQc = Defect::selectRaw("
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
                where("output_defects.kode_numbering", $this->scannedDefectIn)->
                first();

                if ($scannedDefectQc) {
                    $scannedDefect = $scannedDefectQc;
                } else {
                    $scannedDefectQcf = OutputFinishing::selectRaw("
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
                    where("output_check_finishing.kode_numbering", $this->scannedDefectIn)->
                    first();

                    if ($scannedDefectQcf) {
                        $scannedDefect = $scannedDefectQcf;
                    } else {
                        $scannedDefectPacking = DefectPacking::selectRaw("
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
                        where("output_defects_packing.kode_numbering", $this->scannedDefectIn)->
                        first();

                        if ($scannedDefectPacking) {
                            $scannedDefect = $scannedDefectPacking;
                        }
                    }
                }
            } else if ($this->defectInOutputType == "packing") {
                $scannedDefect = DefectPacking::selectRaw("
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
                where("output_defects_packing.kode_numbering", $this->scannedDefectIn)->
                first();
            } else if ($this->defectInOutputType == "qcf") {
                $scannedDefect = OutputFinishing::selectRaw("
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
                where("output_check_finishing.kode_numbering", $this->scannedDefectIn)->
                first();
            } else {
                $scannedDefect = Defect::selectRaw("
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
                where("output_defects.kode_numbering", $this->scannedDefectIn)->
                first();
            }

            if ($scannedDefect) {
                $defectInOut = DefectInOutModel::where("defect_id", $scannedDefect->id)->where("output_type", $scannedDefect->output_type)->first();

                if (!$defectInOut) {
                    $createDefectInOut = DefectInOutModel::create([
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
                        $this->emit('alert', 'success', "DEFECT '".$scannedDefect->defect_type."' dengan KODE '".$this->scannedDefectIn."' berhasil masuk ke '".Auth::user()->Groupp."'");
                    } else {
                        $this->emit('alert', 'error', "Terjadi kesalahan.");
                    }
                } else {
                    $this->emit('alert', 'warning', "QR sudah discan.");
                }
            } else {
                $this->emit('alert', 'error', "Defect dengan QR '".$this->scannedDefectIn."' tidak ditemukan di '".Auth::user()->Groupp."'.");
            }
        } else {
            $this->emit('alert', 'error', "QR tidak sesuai.");
        }

        $this->scannedDefectIn = null;
        $this->emit('qrInputFocus', $this->mode);
    }

    public function submitDefectOut()
    {
        if ($this->scannedDefectOut) {
            if ($this->defectOutOutputType == "all" ) {
                $scannedDefect = DefectInOutModel::selectRaw("
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
                whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.kode_numbering ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.kode_numbering ELSE output_defects.kode_numbering END) END) = '".$this->scannedDefectOut."'")->
                first();
            } else {
                $scannedDefect = DefectInOutModel::selectRaw("
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
                leftJoin(($this->defectOutOutputType == 'packing' ? 'output_defects_packing' : ($this->defectOutOutputType == 'qcf' ? 'output_check_finishing' : 'output_defects'))." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
                where("output_defect_in_out.status", "defect")->
                where("output_defect_in_out.type", Auth::user()->Groupp)->
                where("output_defect_in_out.output_type", $this->defectOutOutputType)->
                where("output_defects.kode_numbering", $this->scannedDefectOut)->
                first();
            }

            if ($scannedDefect) {
                $defectInOut = DefectInOutModel::where("defect_id", $scannedDefect->id)->where("output_type", $scannedDefect->output_type)->first();

                if ($defectInOut) {
                    if ($defectInOut->status == "defect") {
                        $updateDefectInOut = DefectInOutModel::where("defect_id", $scannedDefect->id)->update([
                            "status" => "reworked",
                            "created_by" => Auth::user()->username,
                            "updated_at" => Carbon::now(),
                            "reworked_at" => Carbon::now()
                        ]);

                        if ($updateDefectInOut) {
                            $this->emit('alert', 'success', "DEFECT '".$scannedDefect->defect_type."' dengan KODE '".$this->scannedDefectOut."' berhasil dikeluarkan dari '".Auth::user()->Groupp."'");
                        } else {
                            $this->emit('alert', 'error', "Terjadi kesalahan.");
                        }
                    } else {
                        $this->emit('alert', 'warning', "QR sudah discan di OUT.");
                    }
                } else {
                    $this->emit('alert', 'error', "DEFECT '".$scannedDefect->defect_type."' dengan QR '".$this->scannedDefectOut."' tidak/belum masuk '".Auth::user()->Groupp."'.");
                }
            } else {
                $this->emit('alert', 'error', "DEFECT dengan QR '".$this->scannedDefectOut."' tidak ditemukan di '".Auth::user()->Groupp."'.");
            }
        } else {
            $this->emit('alert', 'error', "QR tidak sesuai.");
        }

        $this->scannedDefectOut = null;
        $this->emit('qrInputFocus', $this->mode);
    }

    public function showDefectAreaImage($productTypeImage, $x, $y)
    {
        $this->productTypeImage = $productTypeImage;
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;

        $this->emit('showDefectAreaImage', $this->productTypeImage, $this->defectPositionX, $this->defectPositionY);
    }

    public function hideDefectAreaImage()
    {
        $this->productTypeImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;
    }

    public function render()
    {
        $this->loadingMasterPlan = false;

        $this->lines = UserPassword::where("Groupp", "SEWING")->orderBy("line_id", "asc")->get();

        if ($this->defectOutOutputType == "all" ) {
            $defectOutQuery = DefectInOutModel::selectRaw("
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
            whereRaw("DATE(output_defect_in_out.created_at) >= '2025-10-01'");
            if ($this->defectOutSearch) {
                $defectOutQuery->whereRaw("(
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.tgl_plan ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.tgl_plan ELSE master_plan.tgl_plan END) END) LIKE '%".$this->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) LIKE '%".$this->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) LIKE '%".$this->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) LIKE '%".$this->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.color ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.color ELSE master_plan.color END) END) LIKE '%".$this->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) LIKE '%".$this->defectOutSearch."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) LIKE '%".$this->defectOutSearch."%' OR
                    output_defect_in_out.kode_numbering LIKE '%".$this->defectOutSearch."%'
                )");
            }
            // if ($this->defectOutDate) {
            //     $defectOutQuery->whereBetween("output_defect_in_out.updated_at", [$this->defectOutDate." 00:00:00", $this->defectOutDate." 23:59:59"]);
            // }
            if ($this->defectOutLine) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) = '".$this->defectOutLine."'");
            }
            if ($this->defectOutSelectedMasterPlan) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.id ELSE master_plan.id END) END) = '".$this->defectOutSelectedMasterPlan."'");
            }
            if ($this->defectOutSelectedSize) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.so_det_id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.so_det_id ELSE output_defects.so_det_id END) END) = '".$this->defectOutSelectedSize."'");
            }
            if ($this->defectOutSelectedType) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.defect_type_id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.defect_type_id ELSE output_defects.defect_type_id END) END) = '".$this->defectOutSelectedType."'");
            };
            if ($this->defectOutFilterKode) {
                $defectOutQuery->whereRaw("output_defect_in_out.kode_numbering LIKE '%".$this->defectOutFilterKode."%'");
            }
            if ($this->defectOutFilterWaktu) {
                $defectOutQuery->whereRaw("output_defect_in_out.updated_at LIKE '%".$this->defectOutFilterWaktu."%'");
            }
            if ($this->defectOutFilterLine) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) LIKE '%".str_replace(" ", "_", $this->defectOutFilterLine)."%'");
            }
            if ($this->defectOutFilterMasterPlan) {
                $defectOutQuery->whereRaw("(
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) LIKE '%".$this->defectOutFilterMasterPlan."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.kpno END) END) LIKE '%".$this->defectOutFilterMasterPlan."%' OR
                    (CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.color ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.color ELSE so_det.color END) END) LIKE '%".$this->defectOutFilterMasterPlan."%'
                )");
            }
            if ($this->defectOutFilterSize) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) LIKE '%".$this->defectOutFilterSize."%'");
            }
            if ($this->defectOutFilterType) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) LIKE '%".$this->defectOutFilterType."%'");
            }
        } else {
            $defectOutQuery = DefectInOutModel::selectRaw("
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
            leftJoin(($this->defectOutOutputType == 'packing' ? 'output_defects_packing' : ($this->defectOutOutputType == 'qcf' ? 'output_check_finishing' : 'output_defects'))." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            whereNotNull("output_defects.id")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            where("output_defect_in_out.status", "defect")->
            where("output_defect_in_out.output_type", $this->defectOutOutputType)->
            where("output_defect_in_out.type", Auth::user()->Groupp)->
            whereRaw("DATE(output_defect_in_out.created_at) >= '2025-10-01'");
            if ($this->defectOutSearch) {
                $defectOutQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectOutSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectOutSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectOutSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectOutSearch."%' OR
                    master_plan.color LIKE '%".$this->defectOutSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectOutSearch."%' OR
                    so_det.size LIKE '%".$this->defectOutSearch."%' OR
                    output_defect_in_out.kode_numbering LIKE '%".$this->defectOutSearch."%'
                )");
            }
            // if ($this->defectOutDate) {
            //     $defectOutQuery->whereBetween("output_defect_in_out.updated_at", [$this->defectOutDate." 00:00:00", $this->defectOutDate." 23:59:59"]);
            // }
            if ($this->defectOutLine) {
                $defectOutQuery->where("master_plan.sewing_line", $this->defectOutLine);
            }
            if ($this->defectOutSelectedMasterPlan) {
                $defectOutQuery->where("master_plan.id", $this->defectOutSelectedMasterPlan);
            }
            if ($this->defectOutSelectedSize) {
                $defectOutQuery->where("output_defects.so_det_id", $this->defectOutSelectedSize);
            }
            if ($this->defectOutSelectedType) {
                $defectOutQuery->where("output_defects.defect_type_id", $this->defectOutSelectedType);
            };
            if ($this->defectOutFilterKode) {
                $defectOutQuery->whereRaw("output_defect_in_out.kode_numbering LIKE '%".$this->defectOutFilterKode."%'");
            }
            if ($this->defectOutFilterWaktu) {
                $defectOutQuery->whereRaw("output_defect_in_out.updated_at LIKE '%".$this->defectOutFilterWaktu."%");
            }
            if ($this->defectOutFilterLine) {
                $defectOutQuery->whereRaw("master_plan.sewing_line LIKE '%".str_replace(" ", "_", $this->defectOutFilterLine)."%'");
            }
            if ($this->defectOutFilterMasterPlan) {
                $defectOutQuery->whereRaw("(
                    act_costing.kpno LIKE '%".$this->defectOutFilterMasterPlan."%' OR
                    act_costing.styleno LIKE '%".$this->defectOutFilterMasterPlan."%' OR
                    so_det.color LIKE '%".$this->defectOutFilterMasterPlan."%'
                )");
            }
            if ($this->defectOutFilterSize) {
                $defectOutQuery->whereRaw("so_det.size LIKE '%".$this->defectOutFilterSize."%'");
            }
            if ($this->defectOutFilterType) {
                $defectOutQuery->whereRaw("(CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) LIKE '%".$this->defectOutFilterType."%'");
            }
        }

        $this->defectOutList = $defectOutQuery->
            groupBy("output_defect_in_out.id")->
            orderBy("output_defect_in_out.updated_at", "desc")->
            get();

        // All Defect
        $defectInOutDaily = DefectInOutModel::selectRaw("
                DATE(output_defect_in_out.created_at) tanggal,
                SUM(CASE WHEN (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_defects.id END) END) IS NOT NULL THEN 1 ELSE 0 END) total_in,
                SUM(CASE WHEN (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_defects.id END) END) IS NOT NULL AND output_defect_in_out.status = 'defect' THEN 1 ELSE 0 END) total_process,
                SUM(CASE WHEN (CASE WHEN output_defect_in_out.output_type = 'packing' THEN output_defects_packing.id ELSE (CASE WHEN output_defect_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_defects.id END) END) IS NOT NULL AND output_defect_in_out.status = 'reworked' THEN 1 ELSE 0 END) total_out
            ")->
            leftJoin("output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defects_packing", "output_defects_packing.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_defect_in_out.defect_id")->
            where("output_defect_in_out.type", strtolower(Auth::user()->Groupp))->
            whereBetween("output_defect_in_out.created_at", [$this->defectInOutFrom." 00:00:00", $this->defectInOutTo." 23:59:59"])->
            groupByRaw("DATE(output_defect_in_out.created_at)")->
            get();

        $defectInOutTotal = $defectInOutDaily ? $defectInOutDaily->sum("total_in") : 0;

        return view('livewire.defect-in-out', [
            "totalDefectIn" => $this->defectInList ? $this->defectInList->sum("defect_qty") : 0,
            "totalDefectOut" => $this->defectOutList ? $this->defectOutList->sum("defect_qty") : 0,
            "totalDefectInOut" => $defectInOutTotal
        ]);
    }

    public function refreshComponent()
    {
        $this->emit('$refresh');
    }
}
