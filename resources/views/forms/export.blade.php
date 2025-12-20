<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Export - {{ $form->client_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            padding: 20px;
        }

        .export-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-section {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .logo-cell {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
            padding-right: 10px;
        }

        .logo-cell img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .info-cell {
            display: table-cell;
            vertical-align: middle;
            padding-right: 20px;
        }

        .info-label {
            font-weight: normal;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: normal;
        }

        .right-info {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .table-section {
            margin-top: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th {
            background-color: #808080;
            color: #000;
            font-weight: bold;
            padding: 8px;
            text-align: center;
            border: 1px solid #000;
            font-size: 11pt;
        }

        .data-table td {
            padding: 8px;
            border: 1px solid #000;
            text-align: center;
            font-size: 11pt;
        }

        .data-table td.description {
            text-align: left;
        }

        .footer-section {
            margin-top: 20px;
            text-align: center;
        }

        .footer-row {
            padding: 8px;
            background-color: #808080;
            color: #000;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer-text {
            padding: 5px;
            color: #000;
        }
    </style>
</head>

<body>
    <div class="export-container">
        <!-- Header Section (Rows 1-3) -->
        <div class="header-section">
            <div class="logo-cell">
                <img src="{{ public_path('images/monogram.jpeg') }}" alt="Logo">
            </div>
            <div class="info-cell">
                <div class="info-label">Client Name</div>
                <div class="info-value">{{ $form->client_name }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Project Name</div>
                <div class="info-value">{{ $form->project_name }}</div>
            </div>
            <div class="info-cell">
                <div class="info-label">Date</div>
                <div class="info-value">{{ $formattedDate }}</div>
            </div>
            <div class="right-info">
                <div class="info-label">Item</div>
                <div class="info-label" style="margin-top: 20px;">T.Qty</div>
            </div>
            <div class="right-info">
                <div class="info-label">new field in forms_table</div>
                <div class="info-value" style="margin-top: 20px;">{{ $sumOfTotal }}</div>
            </div>
        </div>

        <!-- Table Section (Row 5 onwards) -->
        <div class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S. NO</th>
                        <th>DESCRIPTION</th>
                        <th>QTY</th>
                        <th>L</th>
                        <th>W</th>
                        <th>H</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fields as $index => $field)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="description">{{ $field->description ?? '' }}</td>
                            <td>{{ $field->quantity ?? '' }}</td>
                            <td>{{ $field->length ?? '' }}</td>
                            <td>{{ $field->width ?? '' }}</td>
                            <td>{{ $field->height ?? '' }}</td>
                            <td>{{ $field->product ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center;">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Section (Rows 10-12) -->
        <div class="footer-section">
            <div class="footer-row">MIA CONSTRUCTION</div>
            <div class="footer-text">Consultant - Designer - Estimator - Contractor</div>
            <div class="footer-text">3218600259</div>
        </div>
    </div>
</body>

</html>
