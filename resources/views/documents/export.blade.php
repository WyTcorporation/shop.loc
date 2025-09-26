<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Document export</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2933;
            margin: 24px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: #f8fafc;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 11px;
        }

        .metadata {
            margin-bottom: 20px;
        }

        .metadata span {
            display: inline-block;
            margin-right: 16px;
        }
    </style>
</head>
<body>
    <h1>Документ @if(isset($payload['number'])) № {{ $payload['number'] }} @endif</h1>

    <div class="metadata">
        @if(isset($payload['date']))
            <span><strong>Дата:</strong> {{ $payload['date'] }}</span>
        @endif
        @if(isset($payload['currency']))
            <span><strong>Валюта:</strong> {{ $payload['currency'] }}</span>
        @endif
        @if(isset($payload['total']))
            <span><strong>Сума:</strong> {{ $payload['total'] }}</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Поле</th>
                <th>Значення</th>
            </tr>
        </thead>
        <tbody>
        @foreach($flattened as $field => $value)
            <tr>
                <td>{{ \Illuminate\Support\Str::headline($field) }}</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
