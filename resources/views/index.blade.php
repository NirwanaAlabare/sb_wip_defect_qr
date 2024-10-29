@extends('layouts.index')

@section('content')
    <livewire:defect-in-out/>
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
    </script>
@endsection
