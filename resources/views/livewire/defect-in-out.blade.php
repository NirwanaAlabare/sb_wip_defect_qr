<div>
    <div class="loading-container-fullscreen" wire:loading wire:target="changeMode, preSaveSelectedDefectIn, saveSelectedDefectIn, saveCheckedDefectIn, saveAllDefectIn, preSaveSelectedDefectOut, saveSelectedDefectOut, saveCheckedDefectOut, saveAllDefectOut, submitDefectIn, submitDefectOut, submitDefectOut, refreshComponent, showDefectAreaImage">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loading-defect-in-out">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="row g-3">
        <div class="d-flex justify-content-center gap-1">
            <button type="button" class="btn btn-sm btn-sb-outline {{ $mode == "in-out" ? "active" : "" }}" {{ $mode == "in-out" ? "disabled" : "" }} id="button-in-out">SUM</button>
            <button type="button" class="btn btn-sm btn-defect {{ $mode == "in" ? "active" : "" }}" {{ $mode == "in" ? "disabled" : "" }} id="button-in">IN</button>
            <button type="button" class="btn btn-sm btn-rework {{ $mode == "out" ? "active" : "" }}" {{ $mode == "out" ? "disabled" : "" }} id="button-out">OUT</button>
        </div>

        {{-- Defect IN --}}
        <div class="col-12 col-md-12 {{ $mode != "in" ? 'd-none' : ''}}" wire:poll.30000ms>
            <div class="card">
                <div class="card-header bg-defect">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">{{ Auth::user()->Groupp." " }}DEFECT IN</h5>
                        <div class="d-flex align-items-center">
                            <h5 class="px-3 mb-0 text-light">Total : <b>{{ $totalDefectIn }}</b></h5>
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4" wire:ignore>
                            <input type="text" class="qty-input border h-100" id="scannedItemDefectIn" name="scannedItemDefectIn">
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control form-control-sm" wire:model="defectInSearch" placeholder="Search...">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" name="defectInOutputType" id="defect-in-output-type" wire:model="defectInOutputType">
                                        <option value="qc">QC</option>
                                        <option value="qcf">QC FINISHING</option>
                                        <option value="packing">PACKING</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" name="defectInLine" id="defect-in-line" wire:model="defectInLine">
                                        <option value="" selected>Pilih Line</option>
                                        @foreach ($lines as $line)
                                            <option value="{{ $line->username }}">{{ str_replace("_", " ", $line->username) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <div class="col-md-2">
                                    <select class="form-select form-select-sm" name="defectInShowPage" id="defect-in-show-page" wire:model="defectInShowPage">
                                        <option value="10">Show 10</option>
                                        <option value="25">Show 25</option>
                                        <option value="50">Show 50</option>
                                        <option value="100">Show 100</option>
                                    </select>
                                </div> --}}
                                <div class="col-md-3 d-none">
                                    <button type="button" class="btn btn-sm btn-rework w-100 fw-bold" wire:click="saveAllDefectIn">ALL DEFECT OUT</button>
                                </div>
                            </div>
                            <div class="table-responsive-md" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-bordered w-100">
                                    <thead>
                                        <tr class="text-center align-middle">
                                            <th>No.</th>
                                            <th>Kode</th>
                                            <th>Waktu</th>
                                            <th>Line</th>
                                            <th>Master Plan</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                            <th>Qty</th>
                                            <th>Dept.</th>
                                            {{-- <th>Waktu</th> --}}
                                            {{-- <th><input class="form-check-input" type="checkbox" value="" id="defect-in-select-all" onclick="defectInSelectAll(this)" style="scale: 1.3"></th>
                                            <th>IN</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($defectInList) < 1)
                                            <tr class="text-center align-middle">
                                                <td colspan="9" class="text-center">Data tidak ditemukan</td>
                                            </tr>
                                        @else
                                            @foreach ($defectInList as $defectIn)
                                                @php
                                                    $thisDefectInChecked = null;

                                                    if ($defectInSelectedList) {
                                                        $thisDefectInChecked = $defectInSelectedList->filter(function ($item) use ($defectIn) {
                                                            return $item['master_plan_id'] == $defectIn->master_plan_id && $item['defect_type_id'] == $defectIn->defect_type_id && $item['so_det_id'] == $defectIn->so_det_id;
                                                        });
                                                    }
                                                @endphp
                                                <tr class="text-center align-middle">
                                                    <td>{{ $loop->index+1 }}</td>
                                                    <td>{{ $defectIn->kode_numbering }}</td>
                                                    <td>{{ $defectIn->defect_time }}</td>
                                                    <td>{{ strtoupper(str_replace("_", " ", $defectIn->sewing_line)) }}</td>
                                                    <td>{{ $defectIn->ws }}<br>{{ $defectIn->style }}<br>{{ $defectIn->color }}</td>
                                                    <td>{{ $defectIn->size }}</td>
                                                    <td>{{ $defectIn->defect_type }}</td>
                                                    <td>{{ $defectIn->defect_qty }}</td>
                                                    <td class="fw-bold {{ $defectIn->output_type == 'qc' ? 'text-danger' : ($defectIn->output_type == 'qcf' ? 'text-pink' : 'text-success') }}">{{ strtoupper($defectIn->output_type) }}</td>
                                                    {{-- <td>{{ $defectIn->updated_at }}</td> --}}
                                                    {{-- <td><input class="form-check-input" type="checkbox" value="{{ $defectIn->master_plan_id.'-'.$defectIn->defect_type_id.'-'.$defectIn->so_det_id }}" style="scale: 1.3" {{ $thisDefectInChecked && $thisDefectInChecked->count() > 0 ? "checked" : ""  }} onchange="defectInCheck(this)"></td>
                                                    <td><button class="btn btn-sm btn-defect fw-bold" wire:click='preSaveSelectedDefectIn("{{ $defectIn->master_plan_id.'-'.$defectIn->defect_type_id.'-'.$defectIn->so_det_id }}")'>IN</button></td> --}}
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                {{-- {{ $defectInList->links() }} --}}
                                {{-- <div class="row justify-content-end mt-3">
                                    <div class="col-md-3">
                                        <button class="btn btn-defect btn-sm fw-bold w-100" wire:click='saveCheckedDefectIn()'>CHECKED DEFECT IN</button>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Defect OUT --}}
        <div class="col-12 col-md-12 {{ $mode != "out" ? 'd-none' : ''}}" wire:poll.30000ms>
            <div class="card">
                <div class="card-header bg-rework">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">{{ Auth::user()->Groupp." " }}DEFECT OUT</h5>
                        <div class="d-flex align-items-center">
                            <h5 class="px-3 mb-0 text-light">Total : <b>{{ $totalDefectOut }}</b></h5>
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4" wire:ignore>
                            <input type="text" class="qty-input border h-100" id="scannedItemDefectOut" name="scannedItemDefectOut">
                        </div>
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control form-control-sm" wire:model="defectOutSearch" placeholder="Search...">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" name="defectOutOutputType" id="defect-out-output-type" wire:model="defectOutOutputType">
                                        <option value="qc">QC</option>
                                        <option value="qcf">QC FINISHING</option>
                                        <option value="packing">PACKING</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-sm" name="defectOutLine" id="defect-out-line" wire:model="defectOutLine">
                                        <option value="" selected>Pilih Line</option>
                                        @foreach ($lines as $line)
                                            <option value="{{ $line->username }}">{{ str_replace("_", " ", $line->username) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <div class="col-md-2">
                                    <select class="form-select form-select-sm" name="defectOutShowPage" id="defect-out-show-page" wire:model="defectOutShowPage">
                                        <option value="10">Show 10</option>
                                        <option value="25">Show 25</option>
                                        <option value="50">Show 50</option>
                                        <option value="100">Show 100</option>
                                    </select>
                                </div> --}}
                                <div class="col-md-3 d-none">
                                    <button type="button" class="btn btn-sm btn-rework w-100 fw-bold" wire:click="saveAllDefectOut">ALL DEFECT OUT</button>
                                </div>
                            </div>
                            <div class="table-responsive-md" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-bordered w-100">
                                    <thead>
                                        <tr class="text-center align-middle">
                                            <th>No.</th>
                                            <th>Kode</th>
                                            <th>Waktu</th>
                                            <th>Line</th>
                                            <th>Master Plan</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                            <th>Qty</th>
                                            <th>Dept.</th>
                                            {{-- <th>Waktu</th> --}}
                                            {{-- <th><input class="form-check-input" type="checkbox" value="" id="defect-out-select-all" onchange="defectOutSelectAll(this)" style="scale: 1.3"></th>
                                            <th>IN</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($defectOutList) < 1)
                                            <tr class="text-center align-middle">
                                                <td colspan="9" class="text-center">Data tidak ditemukan</td>
                                            </tr>
                                        @else
                                            @foreach ($defectOutList as $defectOut)
                                                @php
                                                    $thisDefectOutChecked = null;

                                                    if ($defectOutSelectedList) {
                                                        $thisDefectOutChecked = $defectOutSelectedList->filter(function ($item) use ($defectOut) {
                                                            return $item['master_plan_id'] == $defectOut->master_plan_id && $item['defect_type_id'] == $defectOut->defect_type_id && $item['so_det_id'] == $defectOut->so_det_id;
                                                        });
                                                    }
                                                @endphp
                                                <tr class="text-center align-middle">
                                                    <td>{{ $loop->index+1 }}</td>
                                                    <td>{{ $defectOut->kode_numbering }}</td>
                                                    <td>{{ $defectOut->defect_time }}</td>
                                                    <td>{{ strtoupper(str_replace("_", " ", $defectOut->sewing_line)) }}</td>
                                                    <td>{{ $defectOut->ws }}<br>{{ $defectOut->style }}<br>{{ $defectOut->color }}</td>
                                                    <td>{{ $defectOut->size }}</td>
                                                    <td>{{ $defectOut->defect_type }}</td>
                                                    <td>{{ $defectOut->defect_qty }}</td>
                                                    <td class="fw-bold {{ $defectOut->output_type == 'qc' ? 'text-danger' : ($defectOut->output_type == 'qcf' ? 'text-pink' : 'text-success') }}">{{ strtoupper($defectOut->output_type) }}</td>
                                                    {{-- <td>{{ $defectOut->updated_at }}</td> --}}
                                                    {{-- <td><input class="form-check-input" type="checkbox" value="{{ $defectOut->master_plan_id.'-'.$defectOut->defect_type_id.'-'.$defectOut->so_det_id }}" style="scale: 1.3" {{ $thisDefectOutChecked && $thisDefectOutChecked->count() > 0 ? "checked" : ""  }} onchange="defectOutCheck(this)"></td>
                                                    <td><button class="btn btn-sm btn-rework fw-bold" wire:click="preSaveSelectedDefectOut('{{ $defectOut->master_plan_id.'-'.$defectOut->defect_type_id.'-'.$defectOut->so_det_id }}')">OUT</button></td> --}}
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                {{-- {{ $defectOutList->links() }} --}}
                                {{-- <div class="row justify-content-end mt-3">
                                    <div class="col-md-3">
                                        <button class="btn btn-rework btn-sm fw-bold w-100" wire:click='saveCheckedDefectOut()'>CHECKED DEFECT OUT</button>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- All Defect Legacy --}}
        {{-- <div class="col-12 col-md-12 {{ $mode != "in-out" ? 'd-none' : ''}}" wire:poll.30000ms>
            <div class="card">
                <div class="card-header bg-sb">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">{{ Auth::user()->Groupp." " }}Defect In Out Summary</h5>
                        <div class="d-flex align-items-center">
                            <h5 class="px-3 mb-0 text-light">Total : <b>{{ $totalDefectInOut }}</b></h5>
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-10">
                            <input type="text" class="form-control form-control-sm my-3" wire:model="defectInOutSearch" placeholder="Search...">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm my-3" name="defectInOutShowPage" id="defect-in-out-show-page" wire:model="defectInOutShowPage">
                                <option value="10">Show 10</option>
                                <option value="25">Show 25</option>
                                <option value="50">Show 50</option>
                                <option value="100">Show 100</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-none">
                            <button type="button" class="btn btn-sm btn-rework w-100 my-3 fw-bold" wire:click="saveAllDefectIn">ALL DEFECT OUT</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered w-100">
                            <thead>
                                <tr class="text-center align-middle">
                                    <th>No.</th>
                                    <th>Date IN</th>
                                    <th>Time IN</th>
                                    <th>Date OUT</th>
                                    <th>Time OUT</th>
                                    <th>Line</th>
                                    <th>Department</th>
                                    <th>QR</th>
                                    <th>No. WS</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Type</th>
                                    <th>Area</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($defectInOutList->count() < 1)
                                    <tr class="text-center align-middle">
                                        <td colspan="16" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                @else
                                    @foreach ($defectInOutList as $defectInOut)
                                        @php
                                            $show = false;

                                            $currentDefect = null;

                                            if ($defectInOut->output_type == "packing") {
                                                $currentDefect = $defectInOut->defectPacking;
                                            } else {
                                                $currentDefect = $defectInOut->defect;
                                            }

                                            if (
                                                str_contains(strtolower($currentDefect ? $currentDefect->kode_numbering : 0), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower(($currentDefect ? $currentDefect->soDet->so->actCosting->kpno : '')), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower(($currentDefect ? $currentDefect->soDet->so->actCosting->styleno : '')), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower(($currentDefect ? $currentDefect->soDet->color : '')), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower(($currentDefect ? $currentDefect->soDet->size : '')), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower(($currentDefect ? $currentDefect->defectType->defect_type : '')), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower(($currentDefect ? $currentDefect->defectArea->defect_area : '')), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower(str_replace("_", " ", $currentDefect && $currentDefect->masterPlan ? $currentDefect->masterPlan->sewing_line : '')), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower($defectInOut->status == "reworked" ? "DONE" : 'PROCESS'), strtolower($defectInOutSearch)) ||
                                                str_contains(strtolower($defectInOut->output_type), strtolower($defectInOutSearch))
                                            ) {
                                                $show = true;
                                            }
                                        @endphp
                                        @if ($show)
                                            <tr class="text-center align-middle">
                                                <td class="text-nowrap">{{ $defectInOutList->firstItem() + $loop->index }}</td>
                                                <td class="text-nowrap">{{ $defectInOut->created_at ? date('Y-m-d', strtotime($defectInOut->created_at)) : '' }}</td>
                                                <td class="text-nowrap">{{ $defectInOut->created_at ? date('H:i:s', strtotime($defectInOut->created_at)) : '' }}</td>
                                                <td class="text-nowrap">{{ $defectInOut->reworked_at ? date('Y-m-d', strtotime($defectInOut->reworked_at)) : '' }}</td>
                                                <td class="text-nowrap">{{ $defectInOut->reworked_at ? date('H:i:s', strtotime($defectInOut->reworked_at)) : '' }}</td>
                                                <td class="text-nowrap">{{ strtoupper(str_replace("_", " ", $currentDefect ? $currentDefect->masterPlan->sewing_line : null)) }}</td>
                                                <td class="text-nowrap fw-bold {{ $defectInOut->output_type == 'qc' ? 'text-danger' : 'text-success' }}">{{ strtoupper($defectInOut->output_type) }}</td>
                                                <td class="text-nowrap">{{ $currentDefect ? $currentDefect->kode_numbering : null }}</td>
                                                <td class="text-nowrap">{{ $currentDefect ? $currentDefect->soDet->so->actCosting->kpno : null }}</td>
                                                <td class="text-nowrap">{{ $currentDefect ? $currentDefect->soDet->so->actCosting->styleno : null }}</td>
                                                <td class="text-nowrap">{{ $currentDefect ? $currentDefect->soDet->color : null }}</td>
                                                <td class="text-nowrap">{{ $currentDefect ? $currentDefect->soDet->size : null }}</td>
                                                <td class="text-nowrap">{{ $currentDefect ? $currentDefect->defectType->defect_type : null }}</td>
                                                <td class="text-nowrap">{{ $currentDefect ? $currentDefect->defectArea->defect_area : null }}</td>
                                                <td class="text-nowrap"><button class="btn btn-dark" wire:click="showDefectAreaImage('{{$currentDefect && $currentDefect->masterPlan ? $currentDefect->masterPlan->gambar : ''}}', {{$currentDefect ? $currentDefect->defect_area_x : ''}}, {{$currentDefect ? $currentDefect->defect_area_y : ''}})"><i class="fa fa-image"></i></button></td>
                                                <td class="text-nowrap">{{ $defectInOut->status == "reworked" ? "DONE" : ($defectInOut->status == "defect" ? "PROCESS" : '-') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        {{ $defectInOutList->links() }}
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- All Defect --}}
        <div class="col-12 col-md-12 {{ $mode != "in-out" ? 'd-none' : ''}}">
            <div class="card">
                <div class="card-header bg-sb">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-light text-center fw-bold">{{ Auth::user()->Groupp." " }}Defect In Out Summary</h5>
                        <div class="d-flex align-items-center">
                            <h5 class="px-3 mb-0 text-light">Total : <b>{{ $totalDefectInOut }}</b></h5>
                            <button class="btn btn-dark float-end" wire:click="refreshComponent()" onclick="defectInOutReload()">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div>
                        <div class="d-flex align-items-end gap-3 mb-3">
                            <div>
                                <label class="form-label">From</label>
                                <input type="date" class="form-control" value="{{ date("Y-m-d", strtotime("-7 days")) }}" id="dateFrom" wire:model="defectInOutFrom" onchange="defectInOutReload()">
                            </div>
                            <span class="mb-2">-</span>
                            <div>
                                <label class="form-label">To</label>
                                <input type="date" class="form-control" value="{{ date("Y-m-d") }}" id="dateTo" wire:model="defectInOutTo" onchange="defectInOutReload()">
                            </div>
                        </div>
                        <div class="table-responsive" wire:ignore>
                            <table class="table table-bordered w-100" id="defect-in-out-table" >
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Date</th>
                                        <th>Total IN</th>
                                        <th>Total PROCESS</th>
                                        <th>Total OUT</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Show Defect Area --}}
    <div class="show-defect-area" id="show-defect-area" wire:ignore>
        <div class="position-relative d-flex flex-column justify-content-center align-items-center">
            <button type="button" class="btn btn-lg btn-light rounded-0 hide-defect-area-img" onclick="onHideDefectAreaImage()">
                <i class="fa-regular fa-xmark fa-lg"></i>
            </button>
            <div class="defect-area-img-container mx-auto">
                <div class="defect-area-img-point" id="defect-area-img-point-show"></div>
                <img src="" alt="" class="img-fluid defect-area-img" id="defect-area-img-show">
            </div>
        </div>
    </div>

    {{-- Defect In Out Detail Modal --}}
    <div class="modal" tabindex="-1" id="defect-in-out-modal" wire:ignore>
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light fw-bold">
                    <h5 class="modal-title">Defect In Out</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="text" class="form-control" id="defectInOutDetailDate" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Line</label>
                                <select class="form-select select2-defect-in-out-modal" id="defectInOutDetailLine" onchange="defectInOutDetailReload()">
                                    <option value="" selected>All Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->username }}">{{ str_replace("_", " ", $line->username) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select select2-defect-in-out-modal" id="defectInOutDetailDepartment" onchange="defectInOutDetailReload()">
                                    <option value="">All Department</option>
                                    <option value="qc">QC</option>
                                    <option value="qcf">QC FINISHING</option>
                                    <option value="packing">Packing</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Total</label>
                                <input type="text" class="form-control" id="defectInOutDetailQty" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered w-100" id="defect-in-out-detail-table">
                                    <thead>
                                        <tr>
                                            <th>Time IN</th>
                                            <th>Time OUT</th>
                                            <th>Line</th>
                                            <th>Dept.</th>
                                            <th>QR</th>
                                            <th>No. WS</th>
                                            <th>Style</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                            <th>Area</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('datatables/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('datatables/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('datatables/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('datatables/datatables-rowgroup/css/rowGroup.bootstrap4.min.css') }}">

    {{-- DataTables --}}
    <script src="{{ asset('datatables/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('datatables/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('datatables/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('datatables/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", async function () {
            document.getElementById('scannedItemDefectIn').focus();

            $('.select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
            });

            $('.select2-defect-in-out-modal').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#defect-in-out-modal')
            });

            $('#select-defect-in-line').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectInLine = $('#select-defect-in-line').val();

                @this.set('defectInLine', selectedDefectInLine);

                getMasterPlanData();

                getDefectType();
                getDefectArea();
            });

            $('#select-defect-out-line').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectOutLine = $('#select-defect-out-line').val();

                @this.set('defectOutLine', selectedDefectOutLine);

                getMasterPlanData("out");

                getDefectType("out");
                getDefectArea("out");
            });

            $('#select-defect-in-master-plan').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectInMasterPlan = $('#select-defect-in-master-plan').val();

                @this.set('defectInSelectedMasterPlan', selectedDefectInMasterPlan);

                getSizeData();

                getDefectType();
                getDefectArea();
            });

            $('#select-defect-out-master-plan').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectOutMasterPlan = $('#select-defect-out-master-plan').val();

                @this.set('defectOutSelectedMasterPlan', selectedDefectOutMasterPlan);

                getSizeData("out");

                getDefectType("out");
                getDefectArea("out");
            });

            $('#select-defect-in-size').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectInSize = $('#select-defect-in-size').val();

                @this.set('defectInSelectedSize', selectedDefectInSize);

                getDefectType();
                getDefectArea();
            });

            $('#select-defect-out-size').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectOutSize = $('#select-defect-out-size').val();

                @this.set('defectOutSelectedSize', selectedDefectOutSize);

                getDefectType("out");
                getDefectArea("out");
            });

            $('#select-defect-in-type').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectInType = $('#select-defect-in-type').val();

                @this.set('defectInSelectedType', selectedDefectInType);

                getDefectArea();
            });

            $('#select-defect-out-type').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectOutType = $('#select-defect-out-type').val();

                @this.set('defectOutSelectedType', selectedDefectOutType);

                getDefectArea("out");
            });

            $('#select-defect-in-area').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectInType = $('#select-defect-in-area').val();

                @this.set('defectInSelectedArea', selectedDefectInType);

                getDefectType();
            });

            $('#select-defect-out-area').on('change', function (e) {
                Livewire.emit("loadingStart");

                let selectedDefectOutType = $('#select-defect-out-area').val();

                @this.set('defectOutSelectedArea', selectedDefectOutType);

                getDefectType("out");
            });

            $('#button-in').on('click', async function (e) {
                @this.changeMode("in")
            })

            $('#button-out').on('click', async function (e) {
                @this.changeMode("out")
            })

            $('#button-in-out').on('click', async function (e) {
                @this.changeMode("in-out")
            })
        });

        var scannedItemDefectIn = document.getElementById("scannedItemDefectIn");
        scannedItemDefectIn.addEventListener("change", async function () {
            @this.scannedDefectIn = this.value;

            // submit
            @this.submitDefectIn();

            this.value = '';
        });

        var scannedItemDefectOut = document.getElementById("scannedItemDefectOut");
        scannedItemDefectOut.addEventListener("change", async function () {
            @this.scannedDefectOut = this.value;

            // submit
            @this.submitDefectOut();

            this.value = '';
        });

        // init scan
        Livewire.on('qrInputFocus', async (mode) => {
            console.log(mode);

            if (mode == "in") {
                document.getElementById('scannedItemDefectIn').focus();
                document.getElementById('button-out').disabled = false;
            } else if (mode == "out") {
                document.getElementById('scannedItemDefectOut').focus()
                document.getElementById('button-in').disabled = false;
            }
        });

        function getMasterPlanData(type) {
            if (type != "in" && type != "out") {
                type = 'in';
            }
            console.log(type, $("#defect-"+type+"-date").val());
            $.ajax({
                url: "{{ route("get-master-plan") }}",
                method: "GET",
                data: {
                    date: $("#defect-"+type+"-date").val(),
                    line: $("#select-defect-"+type+"-line").val(),
                },
                success: function(res) {
                    document.getElementById("select-defect-"+type+"-master-plan").innerHTML = "";

                    let selectElement = document.getElementById("select-defect-"+type+"-master-plan")

                    let option = document.createElement("option");
                    option.value = "";
                    option.innerText = "All Master Plan";
                    selectElement.appendChild(option);

                    $("#select-defect-"+type+"-master-plan").val("").trigger("change");

                    if (res && res.length > 0) {
                        res.forEach(item => {
                            let option = document.createElement("option");
                            option.value = item.id;
                            option.innerText = item.no_ws+" - "+item.style+" - "+item.color;

                            selectElement.appendChild(option);
                        });
                    }
                }
            });
        }

        function getSizeData(type) {
            if (type != "in" && type != "out") {
                type = 'in';
            }
            $.ajax({
                url: "{{ route("get-size") }}",
                method: "GET",
                data: {
                    master_plan: $("#select-defect-"+type+"-master-plan").val(),
                },
                success: function(res) {
                    document.getElementById("select-defect-"+type+"-size").innerHTML = "";

                    let selectElement = document.getElementById("select-defect-"+type+"-size")

                    let option = document.createElement("option");
                    option.value = "";
                    option.innerText = "Select Size";
                    selectElement.appendChild(option);

                    $("#select-defect-"+type+"-size").val("").trigger("change");

                    if (res && res.length > 0) {
                        res.forEach(item => {
                            let option = document.createElement("option");
                            option.value = item.id;
                            option.innerText = item.size;

                            selectElement.appendChild(option);
                        });
                    }
                }
            });
        }

        function getDefectType(type) {
            if (type != "in" && type != "out") {
                type = 'in';
            }
            $.ajax({
                url: "{{ route("get-defect-type") }}",
                method: "GET",
                data: {
                    date: $("#defect-"+type+"-date").val(),
                    line: $("#select-defect-"+type+"-line").val(),
                    master_plan: $("#select-defect-"+type+"-master-plan").val(),
                    size: $("#select-defect-"+type+"-size").val(),
                    defect_area: $("#select-defect-"+type+"-area").val(),
                },
                success: function(res) {
                    document.getElementById("select-defect-"+type+"-type").innerHTML = "";

                    let selectElement = document.getElementById("select-defect-"+type+"-type")

                    let option = document.createElement("option");
                    option.value = "";
                    option.innerText = "All Defect Type";
                    selectElement.appendChild(option);

                    if (res && res.length > 0) {
                        res.forEach(item => {
                            let option = document.createElement("option");
                            option.value = item.id;
                            option.innerText = item.defect_type+' - '+item.defect_qty;

                            selectElement.appendChild(option);
                        });
                    }
                }
            });
        }

        function getDefectArea(type) {
            if (type != "in" && type != "out") {
                type = 'in';
            }
            $.ajax({
                url: "{{ route("get-defect-area") }}",
                method: "GET",
                data: {
                    date: $("#defect-"+type+"-date").val(),
                    line: $("#select-defect-"+type+"-line").val(),
                    master_plan: $("#select-defect-"+type+"-master-plan").val(),
                    size: $("#select-defect-"+type+"-size").val(),
                    defect_type: $("#select-defect-"+type+"-type").val(),
                },
                success: function(res) {
                    document.getElementById("select-defect-"+type+"-area").innerHTML = "";

                    let selectElement = document.getElementById("select-defect-"+type+"-area");

                    let option = document.createElement("option");
                    option.value = "";
                    option.innerText = "All Defect Area";
                    selectElement.appendChild(option);

                    if (res && res.length > 0) {
                        res.forEach(item => {
                            let option = document.createElement("option");
                            option.value = item.id;
                            option.innerText = item.defect_area+' - '+item.defect_qty;

                            selectElement.appendChild(option);
                        });
                    }
                }
            });
        }

        function defectInSelectAll(element) {
            if (element.checked) {
                Livewire.emit("loadingStart");

                @this.selectAllDefectIn();
            } else {
                Livewire.emit("loadingStart");

                @this.unselectAllDefectIn();
            }
        }

        // $('#defect-in-select-all').on('change', function (e) {
        //     if (this.checked) {
        //         Livewire.emit("loadingStart");

        //         @this.selectAllDefectIn();
        //     } else {
        //         Livewire.emit("loadingStart");

        //         @this.unselectAllDefectIn();
        //     }
        // });

        function defectOutSelectAll(element) {
            if (element.checked) {
                Livewire.emit("loadingStart");

                @this.selectAllDefectOut();
            } else {
                Livewire.emit("loadingStart");

                @this.unselectAllDefectOut();
            }
        }

        // $('#defect-out-select-all').on('change', function (e) {
        //     if (this.checked) {
        //         Livewire.emit("loadingStart");

        //         @this.selectAllDefectOut("out");
        //     } else {
        //         Livewire.emit("loadingStart");

        //         @this.unselectAllDefectOut("out");
        //     }
        // });

        function defectInCheck(element) {
            Livewire.emit("loadingStart");

            if (element.checked) {
                @this.addDefectInSelectedList(element.value);
            } else {
                @this.removeDefectInSelectedList(element.value);
                element.removeAttribute("checked");
            }
        }

        function defectOutCheck(element) {
            Livewire.emit("loadingStart");

            if (element.checked) {
                @this.addDefectOutSelectedList(element.value);
            } else {
                @this.removeDefectOutSelectedList(element.value);
                element.removeAttribute("checked");
            }
        }

        function onShowDefectAreaImage(defectAreaImage, x, y) {
            Livewire.emit('showDefectAreaImage', defectAreaImage, x, y);
        }

        Livewire.on('showDefectAreaImage', async function (defectAreaImage, x, y) {
            await showDefectAreaImage(defectAreaImage);

            let defectAreaImageElement = document.getElementById('defect-area-img-show');
            let defectAreaImagePointElement = document.getElementById('defect-area-img-point-show');

            defectAreaImageElement.style.display = 'block'

            let rect = await defectAreaImageElement.getBoundingClientRect();

            let pointWidth = null;
            if (rect.width == 0) {
                pointWidth = 35;
            } else {
                pointWidth = 0.03 * rect.width;
            }

            defectAreaImagePointElement.style.width = pointWidth+'px';
            defectAreaImagePointElement.style.height = defectAreaImagePointElement.style.width;
            defectAreaImagePointElement.style.left =  'calc('+x+'% - '+0.5 * pointWidth+'px)';
            defectAreaImagePointElement.style.top =  'calc('+y+'% - '+0.5 * pointWidth+'px)';
            defectAreaImagePointElement.style.display = 'block';
        });

        function onHideDefectAreaImage() {
            hideDefectAreaImage();

            Livewire.emit('hideDefectAreaImageClear');
        }

        let defectInOutDatatable = $("#defect-in-out-table").DataTable({
            serverSide: true,
            processing: true,
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{{ route('get-defect-in-out-daily') }}',
                dataType: 'json',
                data: function (d) {
                    d.dateFrom = $("#dateFrom").val();
                    d.dateTo = $("#dateTo").val();
                }
            },
            columns: [
                {
                    data: 'tanggal',
                },
                {
                    data: 'tanggal',
                },
                {
                    data: 'total_in',
                },
                {
                    data: 'total_process',
                },
                {
                    data: 'total_out',
                }
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `<button type='button' class='btn btn-sb-secondary btn-sm' onclick='getDefectInOutDetail("`+data+`")'><i class='fa fa-search'></i></button>`
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ],
        });

        function defectInOutReload() {
            $("#defect-in-out-table").DataTable().ajax.reload();
        }

        let defectInOutDetailDatatable = $("#defect-in-out-detail-table").DataTable({
            serverSide: true,
            processing: true,
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{{ route('get-defect-in-out-detail') }}',
                data: function (d) {
                    d.tanggal = $("#defectInOutDetailDate").val();
                    d.line = $("#defectInOutDetailLine").val();
                    d.departemen = $("#defectInOutDetailDepartment").val();
                },
                dataType: 'json',
            },
            columns: [
                {
                    data: 'time_in',
                },
                {
                    data: 'time_out',
                },
                {
                    data: 'sewing_line',
                },
                {
                    data: 'output_type',
                },
                {
                    data: 'kode_numbering',
                },
                {
                    data: 'no_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'defect_type',
                },
                {
                    data: 'defect_area',
                },
                {
                    data: 'gambar',
                },
                {
                    data: 'status',
                },
            ],
            columnDefs: [
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return data ? data.replace("_", " ").toUpperCase() : '-';
                    }
                },
                {
                    targets: [3],
                    render: (data, type, row, meta) => {
                        let textColor = '';

                        if (data == "packing") {
                            textColor = "text-success";
                        } else {
                            textColor = "text-danger";
                        }

                        return `<span class="`+textColor+` fw-bold">`+(data ? data.toUpperCase() : '-')+`</span>`;
                    }
                },
                {
                    targets: [11],
                    render: (data, type, row, meta) => {
                        return `<button class="btn btn-dark" onclick="onShowDefectAreaImage('`+row.gambar+`', `+row.defect_area_x+`, `+row.defect_area_y+`)"><i class="fa fa-image"></i></button>`
                    }
                },
                {
                    targets: [12],
                    render: (data, type, row, meta) => {
                        let textColor = '';

                        if (data == "reworked") {
                            textColor = "text-rework";
                        } else {
                            textColor = "text-defect";
                        }

                        return `<span class="`+textColor+` fw-bold">`+(data ? data.toUpperCase() : '-')+`</span>`;
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ],
        });

        function defectInOutDetailReload() {
            $("#defect-in-out-detail-table").DataTable().ajax.reload(() => {
                $("#defectInOutDetailQty").val(defectInOutDetailDatatable.page.info().recordsTotal);

                defectInOutReload();
            });
        }

        async function getDefectInOutDetail(tanggal) {
            $("#defectInOutDetailDate").val(tanggal);

            defectInOutDetailReload();

            $("#defect-in-out-modal").modal("show");
        }
    </script>
@endpush
