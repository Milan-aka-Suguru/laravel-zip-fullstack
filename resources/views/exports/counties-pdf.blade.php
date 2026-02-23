<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Counties Export</title>
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
    <h1>Counties Export</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Név</th>
            </tr>
        </thead>
        <tbody>
            @foreach($counties as $county)
            <tr>
                <td>{{ $county->id }}</td>
                <td>{{ $county->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="margin-top: 30px; text-align: center; color: #718096; font-size: 8pt;">
        Generated on {{ now()->format('Y-m-d H:i:s') }}
    </p>
</body>
</html>
