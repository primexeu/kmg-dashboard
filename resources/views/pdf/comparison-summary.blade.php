<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Vergleichsanalyse - {{ $comparison->order_number ?? $comparison->id }}</title>
    <style>
        @page {
            margin: 15mm 20mm;
            size: A4;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1a1a1a;
            background: #ffffff;
        }
        /* Professional Header */
        .document-header {
            border-bottom: 4px solid #1e3a8a;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .document-header-top {
            margin-bottom: 15px;
        }
        .header-left {
            float: left;
            width: 60%;
        }
        .header-right {
            float: right;
            width: 35%;
            text-align: right;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 18pt;
            font-weight: 700;
            color: #1e3a8a;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }
        .company-subtitle {
            font-size: 9pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .document-meta {
            text-align: right;
            font-size: 8pt;
            color: #64748b;
            line-height: 1.6;
        }
        .document-title {
            font-size: 22pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        .document-ref {
            font-size: 9pt;
            color: #475569;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 10px;
        }
        .document-ref-item {
            display: inline-block;
            margin-right: 20px;
        }
        .document-ref-label {
            font-weight: 600;
            color: #64748b;
        }
        /* Sections */
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-header {
            background: linear-gradient(to right, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 12px 15px;
            margin: 0 -15px 20px -15px;
            font-size: 12pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        /* Statistics Cards */
        .stats-container {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
            display: table;
        }
        .stat-card {
            display: table-cell;
            width: 33.33%;
            padding: 20px 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
            border-right: none;
            background: #f8fafc;
            vertical-align: middle;
        }
        .stat-card:last-child {
            border-right: 1px solid #e2e8f0;
        }
        .stat-value {
            font-size: 32pt;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 9pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .stat-matched { color: #059669; }
        .stat-mismatched { color: #dc2626; }
        .stat-total { color: #1e3a8a; }
        /* Progress Bars */
        .progress-section {
            margin: 20px 0;
        }
        .progress-item {
            margin-bottom: 15px;
        }
        .progress-label-row {
            margin-bottom: 6px;
            font-size: 9pt;
        }
        .progress-label {
            float: left;
        }
        .progress-value-right {
            float: right;
        }
        .progress-label-row::after {
            content: "";
            display: table;
            clear: both;
        }
        .progress-label {
            font-weight: 600;
            color: #334155;
        }
        .progress-value {
            font-weight: 700;
            font-size: 10pt;
        }
        .progress-bar {
            height: 24px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
        }
        .progress-fill {
            height: 100%;
            display: block;
        }
        .progress-matched { background: #059669; }
        .progress-mismatched { background: #dc2626; }
        .progress-review { background: #d97706; }
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 9pt;
            background: #ffffff;
        }
        table thead {
            background: #1e3a8a;
            color: #ffffff;
        }
        table th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        table th:last-child {
            border-right: none;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
        }
        table td:last-child {
            border-right: none;
        }
        table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        table tbody tr:hover {
            background: #f1f5f9;
        }
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        /* Status Box */
        .status-box {
            padding: 15px;
            border-left: 4px solid;
            background: #f8fafc;
            margin: 20px 0;
            border-radius: 4px;
        }
        .status-box-success {
            border-color: #059669;
            background: #ecfdf5;
        }
        .status-box-danger {
            border-color: #dc2626;
            background: #fef2f2;
        }
        .status-box-warning {
            border-color: #d97706;
            background: #fffbeb;
        }
        .status-box-title {
            font-weight: 700;
            font-size: 10pt;
            margin-bottom: 5px;
        }
        /* Footer */
        .document-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 2px solid #e2e8f0;
            padding: 10px 20mm;
            background: #ffffff;
            font-size: 7pt;
            color: #64748b;
            text-align: center;
        }
        .footer-content {
            width: 100%;
        }
        .footer-left {
            float: left;
            width: 33%;
        }
        .footer-center {
            float: left;
            width: 34%;
            text-align: center;
        }
        .footer-right {
            float: right;
            width: 33%;
            text-align: right;
        }
        .footer-content::after {
            content: "";
            display: table;
            clear: both;
        }
        .page-number {
            font-weight: 600;
        }
        /* Utility */
        .text-center { text-align: center; }
        .mb-10 { margin-bottom: 10px; }
        .mb-15 { margin-bottom: 15px; }
        .mt-20 { margin-top: 20px; }
        .font-bold { font-weight: 700; }
        .text-sm { font-size: 9pt; }
        .text-xs { font-size: 8pt; }
        .text-gray { color: #64748b; }
    </style>
</head>
<body>
    <!-- Document Header -->
    <div class="document-header">
        <div class="document-header-top clearfix">
            <div class="header-left">
                <div class="company-name">KMG Küchenabgleich</div>
                <div class="company-subtitle">Vergleichsanalyse & Dokumentation</div>
            </div>
            <div class="header-right document-meta">
                <div><strong>Dokument:</strong> Vergleichsanalyse</div>
                <div><strong>Datum:</strong> {{ now()->format('d.m.Y') }}</div>
                <div><strong>Uhrzeit:</strong> {{ now()->format('H:i') }} Uhr</div>
            </div>
        </div>
        <div class="document-title">Vergleichsanalyse: Bestellung vs. Auftragsbestätigung</div>
        <div class="document-ref">
            <span class="document-ref-item">
                <span class="document-ref-label">Vergleichs-ID:</span> {{ $comparison->id }}
            </span>
            @if($comparison->order_number && $comparison->order_number !== 'Unknown')
            <span class="document-ref-item">
                <span class="document-ref-label">Bestellnummer:</span> {{ $comparison->order_number }}
            </span>
            @endif
            @if($comparison->ab_number && $comparison->ab_number !== 'Unknown')
            <span class="document-ref-item">
                <span class="document-ref-label">AB-Nummer:</span> {{ $comparison->ab_number }}
            </span>
            @endif
            @if($comparison->processed_at)
            <span class="document-ref-item">
                <span class="document-ref-label">Erstellt:</span> {{ $comparison->processed_at->format('d.m.Y H:i') }} Uhr
            </span>
            @endif
        </div>
    </div>

    <!-- Executive Summary -->
    <div class="section">
        <div class="section-header">Zusammenfassung</div>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value stat-total">{{ $totalItems }}</div>
                <div class="stat-label">Gesamtpositionen</div>
            </div>
            <div class="stat-card">
                <div class="stat-value stat-matched">{{ $matched }}</div>
                <div class="stat-label">Übereinstimmungen</div>
            </div>
            <div class="stat-card">
                <div class="stat-value stat-mismatched">{{ $mismatched }}</div>
                <div class="stat-label">Abweichungen</div>
            </div>
        </div>

        @if($totalItemsComparison > 0)
        <div class="progress-section">
            <div class="progress-item">
                <div class="progress-label-row clearfix">
                    <span class="progress-label">Übereinstimmungen</span>
                    <span class="progress-value-right" style="color: #059669; font-weight: 700;">{{ $matched }} ({{ round(($matched / $totalItemsComparison) * 100, 1) }}%)</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill progress-matched" style="width: {{ ($matched / $totalItemsComparison) * 100 }}%"></div>
                </div>
            </div>

            <div class="progress-item">
                <div class="progress-label-row clearfix">
                    <span class="progress-label">Keine Übereinstimmung</span>
                    <span class="progress-value-right" style="color: #dc2626; font-weight: 700;">{{ $mismatched }} ({{ round(($mismatched / $totalItemsComparison) * 100, 1) }}%)</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill progress-mismatched" style="width: {{ ($mismatched / $totalItemsComparison) * 100 }}%"></div>
                </div>
            </div>

            @if($review > 0)
            <div class="progress-item">
                <div class="progress-label-row clearfix">
                    <span class="progress-label">Prüfung erforderlich</span>
                    <span class="progress-value-right" style="color: #d97706; font-weight: 700;">{{ $review }} ({{ round(($review / $totalItemsComparison) * 100, 1) }}%)</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill progress-review" style="width: {{ ($review / $totalItemsComparison) * 100 }}%"></div>
                </div>
            </div>
            @endif
        </div>

        <div class="text-sm text-gray mb-10">
            <strong>Hinweis:</strong> {{ $totalItemsComparison }} von {{ $totalItems }} Positionen wurden erfolgreich geprüft.
        </div>

        @if($missingOrder > 0 || $missingAB > 0)
        <div class="status-box status-box-warning">
            <div class="status-box-title">Fehlende Positionen</div>
            <div class="text-sm">
                @if($missingOrder > 0)
                    <div>• <strong>Fehlt in Bestellung:</strong> {{ $missingOrder }} Position(en)</div>
                @endif
                @if($missingAB > 0)
                    <div>• <strong>Fehlt in Auftragsbestätigung:</strong> {{ $missingAB }} Position(en)</div>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>

    <!-- Header Comparison -->
    @if($headerTotal > 0)
    <div class="section">
        <div class="section-header">Kopfvergleich</div>
        
        <div class="stats-container">
            <div class="stat-card" style="background: #ecfdf5; border-color: #059669;">
                <div class="stat-value" style="color: #065f46;">{{ $headerMatched }}</div>
                <div class="stat-label" style="color: #065f46;">Übereinstimmungen</div>
            </div>
            <div class="stat-card" style="background: #fef2f2; border-color: #dc2626;">
                <div class="stat-value" style="color: #991b1b;">{{ $headerMismatched }}</div>
                <div class="stat-label" style="color: #991b1b;">Abweichungen</div>
            </div>
            <div class="stat-card" style="background: #fffbeb; border-color: #d97706;">
                <div class="stat-value" style="color: #92400e;">{{ $headerReview }}</div>
                <div class="stat-label" style="color: #92400e;">Prüfung nötig</div>
            </div>
        </div>

        <div class="text-sm text-gray mb-15">
            <strong>Verglichen:</strong> {{ $headerTotal }} Feld(er)
        </div>

        @php
            $headerOverall = $headerComparison['overall']['verdict'] ?? null;
        @endphp
        @if($headerOverall)
        <div class="status-box status-box-{{ $headerOverall === 'Übereinstimmung' ? 'success' : ($headerOverall === 'Keine Übereinstimmung' ? 'danger' : 'warning') }}">
            <div class="status-box-title">Gesamturteil</div>
            <div class="font-bold" style="font-size: 11pt;">{{ $headerOverall }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- Order Header Information -->
    @if(!empty($orderHeader))
    <div class="section">
        <div class="section-header">Bestellkopf-Informationen</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 35%;">Feld</th>
                    <th>Wert</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderHeader as $key => $value)
                <tr>
                    <td class="font-bold">{{ str_replace('_', ' ', ucwords($key, '_')) }}</td>
                    <td>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : ($value ?? 'N/A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- AB Header Information -->
    @if(!empty($abHeader))
    <div class="section">
        <div class="section-header">AB-Kopf-Informationen</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 35%;">Feld</th>
                    <th>Wert</th>
                </tr>
            </thead>
            <tbody>
                @foreach($abHeader as $key => $value)
                <tr>
                    <td class="font-bold">{{ str_replace('_', ' ', ucwords($key, '_')) }}</td>
                    <td>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : ($value ?? 'N/A') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Items Comparison Summary -->
    @if(!empty($itemsComparison) && count($itemsComparison) > 0)
    <div class="section">
        <div class="section-header">Artikelvergleich (Auszug: Top {{ min(10, count($itemsComparison)) }})</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Position</th>
                    <th style="width: 18%;">Typ-Nr.</th>
                    <th style="width: 20%;">Urteil</th>
                    <th>Grund</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($itemsComparison, 0, 10) as $item)
                <tr>
                    <td>
                        <div><strong>Order:</strong> {{ $item['pos_f1'] ?? $item['Pos. (Bestellung)'] ?? 'N/A' }}</div>
                        <div class="text-xs text-gray">AB: {{ $item['pos_f2'] ?? $item['Pos. (AB)'] ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <div><strong>Order:</strong> {{ $item['typ_nr_f1'] ?? $item['Typ-Nr. (Bestellung)'] ?? 'N/A' }}</div>
                        <div class="text-xs text-gray">AB: {{ $item['typ_nr_f2'] ?? $item['Typ-Nr. (AB)'] ?? 'N/A' }}</div>
                    </td>
                    <td>
                        @php
                            $verdict = $item['verdict'] ?? 'N/A';
                            $badgeClass = match($verdict) {
                                'Übereinstimmung' => 'badge-success',
                                'Keine Übereinstimmung' => 'badge-danger',
                                'Prüfung erforderlich' => 'badge-warning',
                                default => 'badge'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $verdict }}</span>
                    </td>
                    <td class="text-xs">{{ $item['reason'] ?? $item['Begründung'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(count($itemsComparison) > 10)
        <div class="text-center text-sm text-gray mt-20">
            <em>... und {{ count($itemsComparison) - 10 }} weitere Position(en) im System dokumentiert</em>
        </div>
        @endif
    </div>
    @endif

    <!-- Document Footer -->
    <div class="document-footer">
        <div class="footer-content clearfix">
            <div class="footer-left">KMG Küchenabgleich System</div>
            <div class="footer-center page-number">Seite 1</div>
            <div class="footer-right">Generiert: {{ now()->format('d.m.Y H:i') }} Uhr | ID: {{ $comparison->id }}</div>
        </div>
    </div>
</body>
</html>
