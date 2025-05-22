<table>
    <thead>
        <tr>
            <th style="border: 1px solid black; font-weight: 800;">Time IN</th>
            <th style="border: 1px solid black; font-weight: 800;">Time OUT</th>
            <th style="border: 1px solid black; font-weight: 800;">Line</th>
            <th style="border: 1px solid black; font-weight: 800;">Dept.</th>
            <th style="border: 1px solid black; font-weight: 800;">QR</th>
            <th style="border: 1px solid black; font-weight: 800;">No. WS</th>
            <th style="border: 1px solid black; font-weight: 800;">Style</th>
            <th style="border: 1px solid black; font-weight: 800;">Color</th>
            <th style="border: 1px solid black; font-weight: 800;">Size</th>
            <th style="border: 1px solid black; font-weight: 800;">Type</th>
            <th style="border: 1px solid black; font-weight: 800;">Area</th>
            <th style="border: 1px solid black; font-weight: 800;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($defectInOut as $defect)
            <tr>
                <td style="border: 1px solid black;">{{ $defect->time_in }}</td>
                <td style="border: 1px solid black;">{{ $defect->time_out }}</td>
                <td style="border: 1px solid black;">{{ $defect->sewing_line }}</td>
                <td style="border: 1px solid black;">{{ $defect->output_type }}</td>
                <td style="border: 1px solid black;">{{ $defect->kode_numbering }}</td>
                <td style="border: 1px solid black;">{{ $defect->no_ws }}</td>
                <td style="border: 1px solid black;">{{ $defect->style }}</td>
                <td style="border: 1px solid black;">{{ $defect->color }}</td>
                <td style="border: 1px solid black;">{{ $defect->size }}</td>
                <td style="border: 1px solid black;">{{ $defect->defect_type }}</td>
                <td style="border: 1px solid black;">{{ $defect->defect_area }}</td>
                <td style="border: 1px solid black;">{{ $defect->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
