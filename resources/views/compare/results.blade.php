<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comparison Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <style>
        .badge.bg-success, .badge.bg-danger { color: #fff; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Comparison Results</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('compare.form') }}" class="btn btn-secondary">Compare Another</a>
        </div>
    </div>

    @if(isset($data['pdf_header']))
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <strong>PDF Header Information</strong>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>PO Number:</strong> {{ $data['pdf_header']['po_number'] ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Confirmation Date:</strong> {{ $data['pdf_header']['confirm_date'] ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Customer:</strong> {{ $data['pdf_header']['customer'] ?? 'N/A' }}</p>
            </div>
        </div>
    @endif

    @forelse($data['match_results'] as $match)
        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <strong>PDF Reference:</strong> {{ $match['pdf_reference'] ?? '—' }}
                <span class="badge text-bg-info float-end ms-2">{{ $match['match_type'] ?? '—' }}</span>
                @if(!empty($match['price_match']))
                    <span class="badge bg-success float-end">Price Match</span>
                @else
                    <span class="badge bg-danger float-end">Price Mismatch</span>
                @endif
            </div>
            <div class="card-body">
                <p><strong>Confidence Score:</strong>
                    {{ isset($match['confidence_score']) ? number_format($match['confidence_score'] * 100, 2) : '—' }}%
                </p>

                @if(!empty($match['excel_items']))
                    <h5 class="mt-3">Matched Excel Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Line ID</th>
                                    <th>Description</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($match['excel_items'] as $item)
                                    <tr>
                                        <td class="mono">{{ $item['order_id'] ?? '—' }}</td>
                                        <td class="mono">{{ $item['line_id']  ?? '—' }}</td>
                                        <td>{{ $item['description'] ?? '—' }}</td>
                                        <td class="text-end">{{ $item['qty'] ?? '—' }}</td>
                                        <td class="text-end">
                                            {{ isset($item['unit_price']) ? number_format($item['unit_price'], 2) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-warning">
            <strong>No Matches Found.</strong> The API did not find any matches between the provided PDF and Excel files.
        </div>
    @endforelse

    {{-- (Optional) Raw JSON toggle for debugging --}}
    <details class="mt-4">
        <summary class="mb-2">Show raw JSON</summary>
        <pre class="bg-dark text-light p-3 rounded small">{{ json_encode($raw ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </details>
</div>
</body>
</html>