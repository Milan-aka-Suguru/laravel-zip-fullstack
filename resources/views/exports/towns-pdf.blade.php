<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Towns Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        h1 { text-align: center; color: #2d3748; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #4a5568; color: white; padding: 8px; text-align: left; }
        td { border: 1px solid #cbd5e0; padding: 8px; }
        tr:nth-child(even) { background-color: #f7fafc; }
    </style>
</head>
<body>
    <h1>Towns Export</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Irányítószám</th>
                <th>Megye</th>
                <th>Név</th>
            </tr>
        </thead>
        <tbody>
            @foreach($towns as $town)
            <tr>
                <td>{{ $town->id }}</td>
                <td>{{ $town->zip_code }}</td>
                <td>{{ $town->county?->name ?? '' }}</td>
                <td>{{ $town->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top: 30px; text-align: center; color: #718096; font-size: 8pt;">
        Generated on {{ now()->format('Y-m-d H:i:s') }}
    </p>
</body>
</html>
