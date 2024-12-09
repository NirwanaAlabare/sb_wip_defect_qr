<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\UserPassword;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
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

    public $defectInMasterPlanOutput;
    public $defectOutMasterPlanOutput;

    public $defectInSelectedMasterPlan;
    public $defectInSelectedSize;
    public $defectInSelectedType;
    public $defectInSelectedArea;

    public $defectOutSelectedMasterPlan;
    public $defectOutSelectedSize;
    public $defectOutSelectedType;
    public $defectOutSelectedArea;

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
        $this->defectInOutputType = 'qc';
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

        // Defect Out init value
        $this->defectOutOutputType = 'qc';
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

        $this->scannedDefectIn = null;
        $this->scannedDefectOut = null;

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

    public function updatingDefectInSearch()
    {
        $this->resetPage("defectInPage");
    }

    public function updatingDefectOutSearch()
    {
        $this->resetPage("defectOutPage");
    }

    public function updatedPaginators($page, $pageName) {
        if ($this->defectInListAllChecked == true) {
            $this->selectAllDefectIn();
        }

        if ($this->defectOutListAllChecked == true) {
            $this->selectAllDefectOut();
        }
    }

    public function submitDefectIn()
    {
        $this->emit('clearDefectInScan');

        if ($this->scannedDefectIn) {
            if ($this->defectInOutputType == "packing") {
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
                leftJoin("output_defect_in_out", "output_defect_in_out.defect_id", "=", "output_defects_packing.id")->
                where("output_defects_packing.defect_status", "defect")->
                where("output_defect_types.allocation", Auth::user()->Groupp)->
                where("output_defects_packing.kode_numbering", $this->scannedDefectIn)->
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
                leftJoin("output_defect_in_out", "output_defect_in_out.defect_id", "=", "output_defects.id")->
                where("output_defects.defect_status", "defect")->
                where("output_defect_types.allocation", Auth::user()->Groupp)->
                where("output_defects.kode_numbering", $this->scannedDefectIn)->
                first();
            }

            if ($scannedDefect) {
                $defectInOut = DefectInOutModel::where("defect_id", $scannedDefect->id)->first();

                if (!$defectInOut) {
                    $createDefectInOut = DefectInOutModel::create([
                        "defect_id" => $scannedDefect->id,
                        "kode_numbering" => $scannedDefect->kode_numbering,
                        "status" => "defect",
                        "type" => Auth::user()->Groupp,
                        "output_type" => $scannedDefect->output_type,
                        "created_by" => Auth::user()->username
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
            $scannedDefect = DefectInOutModel::selectRaw("
                    output_defects.id,
                    output_defects.kode_numbering,
                    output_defects.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username
                ")->
                leftJoin("output_defects".($this->defectOutOutputType == "packing" ? "_packing" : "" )." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
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

            if ($scannedDefect) {
                $defectInOut = DefectInOutModel::where("defect_id", $scannedDefect->id)->first();

                if ($defectInOut) {
                    if ($defectInOut->status == "defect") {
                        $updateDefectInOut = DefectInOutModel::where("defect_id", $scannedDefect->id)->update([
                            "status" => "reworked",
                            "reworked_at" => Carbon::now(),
                            "created_by" => Auth::user()->username
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

        if ($this->defectInOutputType == 'packing') {
            $defectInQuery = DefectPacking::selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_defects_packing.defect_type_id,
                output_defects_packing.kode_numbering,
                output_defect_types.defect_type,
                output_defects_packing.so_det_id,
                so_det.size,
                output_defects_packing.updated_at,
                'packing' output_type,
                COUNT(output_defects_packing.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            where("output_defects_packing.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            if ($this->defectInDate) {
                $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects_packing.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects_packing.defect_type_id", $this->defectInSelectedType);
            }
            $defectIn = $defectInQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects_packing.so_det_id", "output_defects_packing.kode_numbering");
        } else {
            $defectInQuery = Defect::selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_defects.defect_type_id,
                output_defects.kode_numbering,
                output_defect_types.defect_type,
                output_defects.so_det_id,
                so_det.size,
                output_defects.updated_at,
                'qc' output_type,
                COUNT(output_defects.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            where("output_defects.defect_status", "defect")->
            where("output_defect_types.allocation", Auth::user()->Groupp)->
            whereNull("output_defect_in_out.id");
            if ($this->defectInSearch) {
                $defectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->defectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->defectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->defectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->defectInSearch."%' OR
                    master_plan.color LIKE '%".$this->defectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->defectInSearch."%' OR
                    so_det.size LIKE '%".$this->defectInSearch."%'
                )");
            }
            if ($this->defectInDate) {
                $defectInQuery->where("master_plan.tgl_plan", $this->defectInDate);
            }
            if ($this->defectInLine) {
                $defectInQuery->where("master_plan.sewing_line", $this->defectInLine);
            }
            if ($this->defectInSelectedMasterPlan) {
                $defectInQuery->where("master_plan.id", $this->defectInSelectedMasterPlan);
            }
            if ($this->defectInSelectedSize) {
                $defectInQuery->where("output_defects.so_det_id", $this->defectInSelectedSize);
            }
            if ($this->defectInSelectedType) {
                $defectInQuery->where("output_defects.defect_type_id", $this->defectInSelectedType);
            }
            $defectIn = $defectInQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects.so_det_id", "output_defects.kode_numbering");
        }

        $defectInList = $defectIn->orderBy("sewing_line")->
            orderBy("id_ws")->
            orderBy("color")->
            orderBy("defect_type")->
            orderBy("so_det_id")->
            orderBy("output_type")->
            paginate(10, ['*'], 'defectInPage');

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
            output_defect_in_out.output_type,
            output_defects.kode_numbering,
            so_det.size,
            output_defect_in_out.updated_at,
            COUNT(output_defect_in_out.id) defect_qty
        ")->
        leftJoin("output_defects".($this->defectOutOutputType == 'packing' ? '_packing' : '')." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
        where("output_defect_types.allocation", Auth::user()->Groupp)->
        where("output_defect_in_out.status", "defect")->
        where("output_defect_in_out.output_type", $this->defectOutOutputType)->
        where("output_defect_in_out.type", Auth::user()->Groupp);
        if ($this->defectOutSearch) {
            $defectOutQuery->whereRaw("(
                master_plan.tgl_plan LIKE '%".$this->defectOutSearch."%' OR
                master_plan.sewing_line LIKE '%".$this->defectOutSearch."%' OR
                act_costing.kpno LIKE '%".$this->defectOutSearch."%' OR
                act_costing.styleno LIKE '%".$this->defectOutSearch."%' OR
                master_plan.color LIKE '%".$this->defectOutSearch."%' OR
                output_defect_types.defect_type LIKE '%".$this->defectOutSearch."%' OR
                so_det.size LIKE '%".$this->defectOutSearch."%'
            )");
        }
        if ($this->defectOutDate) {
            $defectOutQuery->where("master_plan.tgl_plan", $this->defectOutDate);
        }
        if ($this->defectOutLine) {
            $defectInQuery->where("master_plan.sewing_line", $this->defectOutLine);
        }
        if ($this->defectOutSelectedMasterPlan) {
            $defectOutQuery->where("master_plan.id", $this->defectOutSelectedMasterPlan);
        }
        if ($this->defectOutSelectedSize) {
            $defectOutQuery->where("output_defects.so_det_id", $this->defectOutSelectedSize);
        }
        if ($this->defectOutSelectedType) {
            $defectOutQuery->where("output_defects.defect_type_id", $this->defectOutSelectedType);
        }
        $defectOutList = $defectOutQuery->
            groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_defects.so_det_id", "output_defect_in_out.output_type", "output_defect_in_out.kode_numbering",)->
            orderBy("master_plan.sewing_line")->
            orderBy("master_plan.id_ws")->
            orderBy("master_plan.color")->
            orderBy("output_defect_types.defect_type")->
            orderBy("output_defects.so_det_id")->
            paginate(10, ['*'], 'defectOutPage');

        // All Defect
        $defectInOutQuery = DefectInOutModel::selectRaw("
                DATE(output_defect_in_out.created_at) date_in,
                TIME(output_defect_in_out.created_at) time_in,
                DATE(output_defect_in_out.reworked_at) date_out,
                TIME(output_defect_in_out.reworked_at) time_out,
                COALESCE(output_defects.sewing_line, output_defects_packing.sewing_line) sewing_line,
                COALESCE(output_defects.kode_numbering, output_defects_packing.kode_numbering) kode_numbering,
                COALESCE(output_defects.kpno, output_defects_packing.kpno) as ws,
                COALESCE(output_defects.styleno, output_defects_packing.styleno) as style,
                COALESCE(output_defects.color, output_defects_packing.color) as color,
                COALESCE(output_defects.size, output_defects_packing.size) size,
                COALESCE(output_defects.defect_type_id, output_defects_packing.defect_type_id) defect_type_id,
                COALESCE(output_defects.defect_type, output_defects_packing.defect_type) defect_type,
                COALESCE(output_defects.defect_area, output_defects_packing.defect_area) defect_area,
                output_defect_in_out.status,
                output_defect_in_out.output_type,
                COALESCE(output_defects.gambar, output_defects_packing.gambar) gambar,
                COALESCE(output_defects.defect_area_x, output_defects_packing.defect_area_x) defect_area_x,
                COALESCE(output_defects.defect_area_y, output_defects_packing.defect_area_y) defect_area_y
            ")->
            leftJoin(DB::raw("
                (
                    select
                        output_defects.id,
                        output_defects.so_det_id,
                        output_defects.kode_numbering,
                        output_defects.defect_type_id,
                        output_defects.defect_area_x,
                        output_defects.defect_area_y,
                        output_defect_types.defect_type,
                        output_defect_areas.defect_area,
                        master_plan.id_ws,
                        master_plan.sewing_line,
                        act_costing.kpno,
                        act_costing.styleno,
                        master_plan.color,
                        master_plan.gambar,
                        so_det.size
                    from
                        output_defects as output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join act_costing on act_costing.id = master_plan.id_ws
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                        left join output_defect_areas on output_defect_areas.id = output_defects.defect_area_id
                    where
                        output_defects.updated_at between '".date('Y-m-d'.strtotime($this->date." -7 days"))." 00:00:00' and '".$this->date." 23:59:59'
                        and
                        output_defect_types.allocation = '".Auth::user()->Groupp."'
                ) output_defects
            "), function($join){
                $join->on("output_defects.id", "=", "output_defect_in_out.defect_id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            leftJoin(DB::raw("
                (
                    select
                        output_defects_packing.id,
                        output_defects_packing.so_det_id,
                        output_defects_packing.kode_numbering,
                        output_defects_packing.defect_type_id,
                        output_defects_packing.defect_area_x,
                        output_defects_packing.defect_area_y,
                        output_defect_types.defect_type,
                        output_defect_areas.defect_area,
                        master_plan.id_ws,
                        master_plan.sewing_line,
                        act_costing.kpno,
                        act_costing.styleno,
                        master_plan.color,
                        master_plan.gambar,
                        so_det.size
                    from
                        output_defects_packing
                        left join master_plan on master_plan.id = output_defects_packing.master_plan_id
                        left join act_costing on act_costing.id = master_plan.id_ws
                        left join so_det on so_det.id = output_defects_packing.so_det_id
                        left join output_defect_types on output_defect_types.id = output_defects_packing.defect_type_id
                        left join output_defect_areas on output_defect_areas.id = output_defects_packing.defect_area_id
                    where
                        output_defects_packing.updated_at between '".date('Y-m-d'.strtotime($this->date." -7 days"))." 00:00:00' and '".$this->date." 23:59:59'
                        and
                        output_defect_types.allocation = '".Auth::user()->Groupp."'
                ) output_defects_packing
            "), function($join){
                $join->on("output_defects_packing.id", "=", "output_defect_in_out.defect_id");
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            whereRaw("(
                output_defect_in_out.created_at between '".$this->date." 00:00:00' and '".$this->date." 23:59:59' OR
                output_defect_in_out.updated_at between '".$this->date." 00:00:00' and '".$this->date." 23:59:59' OR
                output_defect_in_out.reworked_at between '".$this->date." 00:00:00' and '".$this->date." 23:59:59'
            )")->
            where("output_defect_in_out.type", Auth::user()->Groupp);

            if ($this->defectInOutSearch) {
                $defectInOutQuery->whereRaw("(
                    output_defects.sewing_line LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects_packing.sewing_line LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects.kpno LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects_packing.kpno LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects.styleno LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects_packing.styleno LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects.color LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects_packing.color LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects.kode_numbering LIKE '%".$this->defectInSearch."%' OR
                    output_defects_packing.kode_numbering LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects.defect_type LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects_packing.defect_type LIKE '%".$this->defectInOutSearch."%' OR
                    output_defect_in_out.updated_at LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects.size LIKE '%".$this->defectInOutSearch."%' OR
                    output_defects_packing.size LIKE '%".$this->defectInOutSearch."%'
                )");
            }

            $defectInOutList = $defectInOutQuery->
                groupBy("output_defect_in_out.id")->
                orderBy("output_defects.sewing_line")->
                orderBy("output_defects_packing.sewing_line")->
                orderBy("output_defects.id_ws")->
                orderBy("output_defects_packing.id_ws")->
                orderBy("output_defects.color")->
                orderBy("output_defects_packing.color")->
                orderBy("output_defects.defect_type")->
                orderBy("output_defects_packing.defect_type")->
                orderBy("output_defects.so_det_id")->
                orderBy("output_defects_packing.so_det_id")->
                paginate(10, ['*'], 'defectInOutPage');

        return view('livewire.defect-in-out', ["defectInList" => $defectInList, "defectOutList" => $defectOutList, "totalDefectIn" => $defectInList->count(), "totalDefectOut" => $defectOutList->count(), "defectInOutList" => $defectInOutList, "totalDefectInOut" => $defectInOutList->count()]);
    }

    public function refreshComponent()
    {
        $this->emit('$refresh');
    }
}
