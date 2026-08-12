<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #131B33; font-size: 11px; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .sub { color: #777; font-size: 10px; margin-bottom: 16px; }
        .cards { width: 100%; margin-bottom: 16px; }
        .cards td { width: 33%; padding: 10px; border: 1px solid #E3E6EF; border-radius: 6px; }
        .card-label { font-size: 9px; color: #777; text-transform: uppercase; }
        .card-value { font-size: 15px; font-weight: bold; margin-top: 3px; }
        .charts { width: 100%; margin-bottom: 16px; }
        .charts td { width: 50%; text-align: center; vertical-align: top; padding: 6px; }
        .charts img { max-width: 100%; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th { background: #F5F6F9; text-align: left; padding: 6px 8px; font-size: 9.5px; text-transform: uppercase; color: #777; }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #EEE; font-size: 10px; }
        .section-title { font-size: 13px; font-weight: bold; margin: 14px 0 6px; }
        .tag { padding: 1px 6px; border-radius: 8px; background: #E9F6FC; color: #2AA9E0; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Report Pipeline Opty</h1>
    <div class="sub">
        PT Alinea Terra Harmoni · Digenerate {{ $generatedAt->format('d M Y H:i') }}
        @if (array_filter($filters))
            · Filter: {{ collect($filters)->filter()->map(fn($v,$k) => "$k: $v")->implode(' | ') }}
        @endif
    </div>

    <table class="cards">
        <tr>
            <td>
                <div class="card-label">Total Number Opty</div>
                <div class="card-value">{{ $totalCount }}</div>
            </td>
            <td>
                <div class="card-label">Total TCV</div>
                <div class="card-value">Rp {{ number_format($totalTcv, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="card-label">Total GP Nominal</div>
                <div class="card-value">Rp {{ number_format($totalGpNominal, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Grafik</div>
    <table class="charts">
        <tr>
            <td>
                <div style="font-size:10px; margin-bottom:4px;">Jumlah Opty per Kategori</div>
                <img src="{{ $categoryChartUrl }}">
            </td>
            <td>
                <div style="font-size:10px; margin-bottom:4px;">Distribusi per Stage</div>
                <img src="{{ $stageChartUrl }}">
            </td>
        </tr>
    </table>

    <div class="section-title">Rincian per Kategori</div>
    <table class="data">
        <thead>
            <tr><th>Kategori</th><th>Jumlah</th><th>Total TCV</th></tr>
        </thead>
        <tbody>
            @foreach ($byCategory as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['count'] }}</td>
                    <td>Rp {{ number_format($row['tcv'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Detail Opty ({{ $opportunities->count() }})</div>
    <table class="data">
        <thead>
            <tr>
                <th>Judul</th><th>Customer</th><th>Kategori</th><th>TCV</th><th>GP</th>
                <th>Rating</th><th>Stage</th><th>Sales</th><th>Presales</th><th>Engineer</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($opportunities as $o)
                <tr>
                    <td>{{ $o->title }}</td>
                    <td>{{ $o->customer_name }}</td>
                    <td>{{ $o->category_label }}</td>
                    <td>Rp {{ number_format($o->tcv, 0, ',', '.') }}</td>
                    <td>{{ rtrim(rtrim(number_format($o->gp_percentage, 1), '0'), '.') }}%
                        (Rp {{ number_format($o->gp_nominal, 0, ',', '.') }})</td>
                    <td>{{ $o->rating_label }}</td>
                    <td>{{ $o->stage_label }}</td>
                    <td>{{ $o->sales?->name ?? '-' }}</td>
                    <td>{{ $o->presales?->name ?? '-' }}</td>
                    <td>{{ $o->engineers->pluck('name')->implode(', ') ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
