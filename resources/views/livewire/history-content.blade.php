<div>
    {{-- Latest Output --}}
    <div class="mt-1" wire:poll.visible.5000ms>
        <div class="d-flex justify-content-center align-items-center">
            <div class="mb-3">
                <input type="date" class="form-control" name="date-from" id="date-from" value="{{ date('Y-m-d') }}" wire:model='dateFrom'>
            </div>
            <span class="mx-3 mb-3"> - </span>
            <div class="mb-3">
                <input type="date" class="form-control" name="date-to" id="date-to" value="{{ date('Y-m-d') }}" wire:model='dateTo'>
            </div>
        </div>
        <div class="loading-container" wire:loading wire:target="dateFrom, dateTo">
            <div class="loading-container">
                <div class="loading"></div>
            </div>
        </div>
        <div class="loading-container hidden" id="loading-history">
            <div class="loading mx-auto"></div>
        </div>
        <div class="row" id="content-history" wire:loading.remove wire:target="dateFrom, dateTo">
            <div class="col-md-12 table-responsive">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-sm" placeholder="Search..." wire:model="defectInOutSearch">
                </div>
                <table class="table table-bordered w-100 mx-auto">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Line</th>
                            <th>Master Plan</th>
                            <th>Size</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($latestDefectInOut) < 1)
                            <tr>
                                <td colspan="7" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($latestDefectInOut as $latestDefect)
                                <tr>
                                    <td>{{ $latestDefect->time }}</td>
                                    <td>{{ $latestDefect->sewing_line }}</td>
                                    <td>{{ $latestDefect->ws." - ".$latestDefect->style." - ".$latestDefect->color }}</td>
                                    <td>{{ $latestDefect->size }}</td>
                                    <td>{{ $latestDefect->defect_type }}</td>
                                    <td>{{ $latestDefect->qty }}</td>
                                    <td class="fw-bold {{ $latestDefect->status == "defect" ? "text-defect" : "text-rework" }}">{{ strtoupper($latestDefect->status) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                {{ $latestDefectInOut->links() }}
            </div>
        </div>
    </div>
</div>
