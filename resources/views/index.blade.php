@extends('layouts.index')

@section('custom-link')
    <style>
        div.dataTables_wrapper div.dataTables_processing {
            top: 5%;
            left: 5%;
        }
    </style>
@endsection

@section('content')
    @livewire('defect-in-out')
@endsection

@section('custom-script')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            $('.select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
            });
        });

        Livewire.on('loadingStart', () => {
            if (document.getElementById('loading-defect-in-out')) {
                $('#loading-defect-in-out').removeClass('hidden');
            }
        });

        Livewire.on('alert', (type, message) => {
            showNotification(type, message);
        });

        Livewire.on('showModal', (type) => {
            if (type == "defectIn") {
                showDefectInModal();
            }
            if (type == "defectOut") {
                showDefectOutModal();
            }
        });

        Livewire.on('hideModal', (type) => {
            if (type == "defectIn") {
                hideDefectInModal();
            }
            if (type == "defectOut") {
                hideDefectOutModal();
            }
        });

        async function initDefectInScan(onScanSuccess) {
            if (html5QrcodeScannerDefectIn) {
                if ((html5QrcodeScannerDefectIn.getState() && html5QrcodeScannerDefectIn.getState() != 2)) {
                    const rftScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                    // Start Camera
                    await html5QrcodeScannerDefectIn.start({ facingMode: "environment" }, rftScanConfig, onScanSuccess);
                }
            }
        }

        async function clearDefectInScan() {
            console.log(html5QrcodeScannerDefectIn.getState());
            if (html5QrcodeScannerDefectIn) {
                if (html5QrcodeScannerDefectIn.getState() && html5QrcodeScannerDefectIn.getState() != 1) {
                    await html5QrcodeScannerDefectIn.stop();
                    await html5QrcodeScannerDefectIn.clear();
                }
            }
        }

        async function refreshDefectInScan(onScanSuccess) {
            await clearDefectInScan();
            await initDefectInScan(onScanSuccess);
        }

        // Scan QR Defect In
        if (document.getElementById('defect-in-reader')) {
            var html5QrcodeScannerDefectIn = new Html5Qrcode("defect-in-reader");
        }

        async function initDefectOutScan(onScanSuccess) {
            if (html5QrcodeScannerDefectOut) {
                if ((html5QrcodeScannerDefectOut.getState() && html5QrcodeScannerDefectOut.getState() != 2)) {
                    const rftScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                    // Start Camera
                    await html5QrcodeScannerDefectOut.start({ facingMode: "environment" }, rftScanConfig, onScanSuccess);
                }
            }
        }

        async function clearDefectOutScan() {
            console.log(html5QrcodeScannerDefectOut.getState());
            if (html5QrcodeScannerDefectOut) {
                if (html5QrcodeScannerDefectOut.getState() && html5QrcodeScannerDefectOut.getState() != 1) {
                    await html5QrcodeScannerDefectOut.stop();
                    await html5QrcodeScannerDefectOut.clear();
                }
            }
        }

        async function refreshDefectOutScan(onScanSuccess) {
            await clearDefectOutScan();
            await initDefectOutScan(onScanSuccess);
        }

        // Scan QR Defect Out
        if (document.getElementById('defect-out-reader')) {
            var html5QrcodeScannerDefectOut = new Html5Qrcode("defect-out-reader");
        }
    </script>
@endsection
