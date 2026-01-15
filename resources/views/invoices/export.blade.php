<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Export - {{ $invoice->client_name }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            position: relative;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.08);
            z-index: -1;
            white-space: nowrap;
            font-weight: bold;
            letter-spacing: 5px;
        }

        .header {
            margin-bottom: 20px;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 11px;
        }

        .header-label {
            font-weight: bold;
            width: 80px;
        }

        .header-value {
            flex: 1;
            border-bottom: 1px solid #000;
            margin-left: 10px;
            padding-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        table td:first-child {
            text-align: center;
            width: 40px;
        }

        table td:nth-child(2) {
            text-align: left;
        }

        table td:nth-child(3),
        table td:nth-child(4),
        table td:nth-child(5),
        table td:nth-child(6),
        table td:nth-child(7) {
            text-align: center;
        }

        .footer {
            position: fixed;
            bottom: 20mm;
            right: 20mm;
            text-align: right;
            font-size: 10px;
        }

        .footer-company {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer-bottom {
            position: fixed;
            bottom: 10mm;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 9px;
            width: 100%;
        }

        .item-section {
            page-break-inside: avoid;
            margin-bottom: 30px;
        }

        .item-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">MIA CONSTRUCTION</div>

    @foreach($summariesByItem as $itemData)
        <div class="item-section">
            <!-- Header -->
            <div class="header">
                <div class="header-row">
                    <span class="header-label">REF:</span>
                    <span class="header-value"></span>
                </div>
                <div class="header-row">
                    <span class="header-label">CLIENT:</span>
                    <span class="header-value">{{ $invoice->client_name }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">PROJECT:</span>
                    <span class="header-value">{{ $invoice->project_name }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">ITEM:</span>
                    <span class="header-value">{{ $itemData['item']->name }}</span>
                </div>
                <div class="header-row">
                    <span class="header-label">DATE:</span>
                    <span class="header-value">{{ date('d/m/Y') }}</span>
                </div>
            </div>

            <!-- Table -->
            <table>
                <thead>
                    <tr>
                        <th>S.NO</th>
                        <th>DESCRIRTION</th>
                        <th>UNIT</th>
                        <th>QTY</th>
                        <th>RATE</th>
                        <th>AMOUNT</th>
                        <th>REMARKS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itemData['summaries'] as $index => $summary)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $summary->invoiceRate->name ?? '' }}</td>
                            <td>{{ $summary->invoiceRate->unit ?? '' }}</td>
                            <td>{{ number_format($summary->quantity, 0) }}</td>
                            <td>{{ number_format($summary->invoiceRate->rate ?? 0, 2) }}</td>
                            <td>{{ number_format($summary->amount, 2) }}</td>
                            <td>{{ $summary->remarks ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <div class="footer-company">mia construction</div>
        <div>0321-8600259</div>
        <div>Muhammad Imran</div>
    </div>

    <!-- Bottom Footer -->
    <div class="footer-bottom">
        Consultant - Designer - Estimator - Contractor<br>
        - 03218600259 -
    </div>
</body>
</html>

