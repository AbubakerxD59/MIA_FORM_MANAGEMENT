<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceRate;
use App\Models\InvoiceSummary;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooterDrawing;

class InvoiceService
{
    /**
     * Get invoices data for DataTables.
     */
    public function getInvoicesForDataTable(Request $request): array
    {
        $query = Invoice::query();

        // Get DataTables parameters
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->get('search')['value'] ?? '';
        $orderColumn = $request->get('order')[0]['column'] ?? 0;
        $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

        // Column mapping (index 0 is row number, 1=client_name, 2=project_name, 3=actions)
        $columns = [null, 'client_name', 'project_name', null];
        $orderBy = $columns[$orderColumn] ?? 'id'; // Default to 'id' for latest first

        // Apply filter parameters
        $clientName = $request->get('client_name');
        $projectName = $request->get('project_name');

        if (!empty($clientName)) {
            $query->where('client_name', 'like', "%{$clientName}%");
        }

        if (!empty($projectName)) {
            $query->where('project_name', 'like', "%{$projectName}%");
        }

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Get total records count
        $totalRecords = Invoice::count();

        // Get filtered records count
        $filteredRecords = $query->count();

        // Apply ordering
        if ($orderBy && in_array($orderBy, ['id', 'client_name', 'project_name', 'created_at'])) {
            $query->orderBy($orderBy, $orderDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Apply pagination
        $invoices = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = $invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'client_name' => $invoice->client_name,
                'project_name' => $invoice->project_name,
                'created_at' => $invoice->created_at?->toISOString(),
            ];
        });

        return [
            'draw' => intval($request->get('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

    /**
     * Store a new invoice.
     */
    public function storeInvoice(array $data): Invoice
    {
        return Invoice::create($data);
    }

    /**
     * Update an invoice.
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);
        return $invoice;
    }

    /**
     * Delete an invoice.
     */
    public function deleteInvoice(Invoice $invoice): bool
    {
        return $invoice->delete();
    }

    /**
     * Get invoice summaries for a specific item.
     */
    public function getInvoiceSummaries(Invoice $invoice, int $itemId): array
    {
        $summaries = InvoiceSummary::where('invoice_id', $invoice->id)
            ->where('item_id', $itemId)
            ->with(['invoiceRate', 'invoiceItem'])
            ->get();

        return $summaries->map(function ($summary) {
            return [
                'id' => $summary->id,
                'sno' => null, // Will be set on frontend
                'description' => $summary->invoiceRate->name ?? '',
                'unit' => $summary->invoiceRate->unit ?? '',
                'qty' => $summary->quantity,
                'rate' => $summary->invoiceRate->rate ?? 0,
                'amount' => $summary->amount,
                'remarks' => $summary->remarks ?? '',
                'rate_id' => $summary->rate_id,
            ];
        })->toArray();
    }

    /**
     * Create a new invoice item.
     */
    public function createInvoiceItem(array $data): InvoiceItem
    {
        return InvoiceItem::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'invoice_id' => $data['invoice_id'],
            'notes' => $data['notes'] ?? [],
        ]);
    }

    /**
     * Update an existing invoice item.
     */
    public function updateInvoiceItem(InvoiceItem $item, array $data): InvoiceItem
    {
        $item->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'invoice_id' => $data['invoice_id'],
            'notes' => $data['notes'] ?? [],
        ]);
        return $item;
    }

    /**
     * Delete an invoice item.
     */
    public function deleteInvoiceItem(InvoiceItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Create a new invoice rate.
     */
    public function createInvoiceRate(array $data): InvoiceRate
    {
        return InvoiceRate::create([
            'name' => $data['name'],
            'unit' => $data['unit'] ?? null,
            'rate' => isset($data['rate']) ? (int)$data['rate'] : null,
        ]);
    }

    /**
     * Update an existing invoice rate.
     */
    public function updateInvoiceRate(InvoiceRate $rate, array $data): InvoiceRate
    {
        $rate->update([
            'name' => $data['name'],
            'unit' => $data['unit'] ?? null,
            'rate' => isset($data['rate']) ? (int)$data['rate'] : null,
        ]);
        return $rate;
    }

    /**
     * Delete an invoice rate.
     */
    public function deleteInvoiceRate(InvoiceRate $rate): bool
    {
        return $rate->delete();
    }

    /**
     * Get invoice rates for autocomplete.
     */
    public function getInvoiceRates(string $query): array
    {
        $rates = InvoiceRate::where('name', 'like', "%{$query}%")
            ->orderBy('name', 'asc')
            ->limit(20)
            ->get();

        return $rates->map(function ($rate) {
            return [
                'id' => $rate->id,
                'label' => $rate->name,
                'value' => $rate->name,
                'unit' => $rate->unit ?? '',
                'rate' => $rate->rate ?? 0,
            ];
        })->toArray();
    }

    /**
     * Save invoice summaries for a specific invoice item.
     */
    public function saveInvoiceSummaries(Invoice $invoice, int $itemId, array $summaries): int
    {
        // Delete existing summaries for this invoice and item that are not in the new data
        $existingIds = collect($summaries)->pluck('id')->filter()->toArray();
        InvoiceSummary::where('invoice_id', $invoice->id)
            ->where('item_id', $itemId)
            ->whereNotIn('id', $existingIds)
            ->delete();

        $savedCount = 0;
        foreach ($summaries as $summaryData) {
            if (isset($summaryData['id']) && $summaryData['id']) {
                // Update existing summary
                $summary = InvoiceSummary::find($summaryData['id']);
                if ($summary && $summary->invoice_id == $invoice->id && $summary->item_id == $itemId) {
                    $summary->update([
                        'rate_id' => $summaryData['rate_id'],
                        'quantity' => (int)($summaryData['quantity'] ?? 0),
                        'amount' => (int)($summaryData['amount'] ?? 0),
                        'remarks' => $summaryData['remarks'] ?? null,
                    ]);
                    $savedCount++;
                }
            } else {
                // Create new summary
                InvoiceSummary::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $itemId,
                    'rate_id' => $summaryData['rate_id'],
                    'quantity' => (int)($summaryData['quantity'] ?? 0),
                    'amount' => (int)($summaryData['amount'] ?? 0),
                    'remarks' => $summaryData['remarks'] ?? null,
                ]);
                $savedCount++;
            }
        }

        return $savedCount;
    }

    /**
     * Export invoice as Excel spreadsheet.
     */
    public function exportInvoice(Invoice $invoice): Spreadsheet
    {
        // Get all invoice items for this invoice
        $invoiceItems = InvoiceItem::where('invoice_id', $invoice->id)->get();

        // Get all summaries grouped by item
        $summariesByItem = [];
        foreach ($invoiceItems as $item) {
            $summaries = InvoiceSummary::where('invoice_id', $invoice->id)
                ->where('item_id', $item->id)
                ->with(['invoiceRate', 'invoiceItem'])
                ->get();

            if ($summaries->isNotEmpty()) {
                $summariesByItem[] = [
                    'item' => $item,
                    'summaries' => $summaries
                ];
            }
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // Create a sheet for each item
        foreach ($summariesByItem as $index => $itemData) {
            // Sanitize sheet title - Excel doesn't allow: : \ / ? * [ ]
            $rawTitle = $itemData['item']->name ?? 'Sheet' . ($index + 1);
            $sanitizedTitle = preg_replace('/[:\/\\?*\[\]]/', '-', $rawTitle);
            $sheetTitle = substr($sanitizedTitle, 0, 31);

            $sheet = new Worksheet($spreadsheet, $sheetTitle);
            $spreadsheet->addSheet($sheet, $index);
            $sheet->setTitle($sheetTitle);

            $this->generateInvoiceSheet($sheet, $invoice, $itemData);
        }

        // Set active sheet to first one
        if ($spreadsheet->getSheetCount() > 0) {
            $spreadsheet->setActiveSheetIndex(0);
        }

        return $spreadsheet;
    }

    /**
     * Generate invoice sheet for Excel export.
     */
    private function generateInvoiceSheet(Worksheet $sheet, Invoice $invoice, array $itemData): void
    {
        $row = 1;

        // Add blank row above MIA CONSTRUCTION
        $sheet->setCellValue('A' . $row, '');
        $sheet->getRowDimension($row)->setRowHeight(10);
        $row++;
        // Header Section
        $header = !empty($itemData['item']->type) ? $itemData['item']->type : 'Invoice';
        // Merge cells for "INVOICE" (B:H with A empty)
        $sheet->mergeCells('B' . $row . ':H' . $row);
        $sheet->setCellValue('B' . $row, strtoupper($header));
        $sheet->getRowDimension($row)->setRowHeight(30);

        $headerDesignStyleBottom = [
            'font' => ['bold' => true, 'size' => 20, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ],
                'left' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ],
                'right' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ],
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('B' . $row . ':H' . $row)->applyFromArray($headerDesignStyleBottom);
        $row++;

        // Add colored row below header (light orange/peach) (B:H with A empty)
        $sheet->mergeCells('B' . $row . ':H' . $row);
        $sheet->setCellValue('B' . $row, '');
        $sheet->getRowDimension($row)->setRowHeight(5);
        $coloredRowStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFE5CC']
            ]
        ];
        $sheet->getStyle('B' . $row . ':H' . $row)->applyFromArray($coloredRowStyle);
        $row++;

        // Add two blank rows above CLIENT
        for ($i = 0; $i < 1; $i++) {
            $sheet->setCellValue('A' . $row, '');
            $sheet->getRowDimension($row)->setRowHeight(25);
            $row++;
        }

        // Header Section
        // CLIENT on left, REF on right (A empty, shifted right)
        $clientRow = $row;
        $sheet->setCellValue('B' . $row, 'CLIENT:');
        $sheet->setCellValue('C' . $row, $invoice->client_name);
        $sheet->setCellValue('G' . $row, 'REF:');
        $sheet->setCellValue('H' . $row, '');
        $sheet->getRowDimension($row)->setRowHeight(25);
        $row++;

        // PROJECT
        $projectRow = $row;
        $sheet->setCellValue('B' . $row, 'PROJECT:');
        $sheet->setCellValue('C' . $row, $invoice->project_name);
        $sheet->getRowDimension($row)->setRowHeight(25);
        $row++;

        // ITEM
        $itemRow = $row;
        $sheet->setCellValue('B' . $row, 'ITEM:');
        $sheet->setCellValue('C' . $row, $itemData['item']->name);
        $sheet->setCellValue('G' . $row, 'DATE:');
        $sheet->setCellValue('H' . $row, date('d/m/Y'));
        $sheet->getRowDimension($row)->setRowHeight(25);
        $row++;

        // Add two blank rows below ITEM
        for ($i = 0; $i < 1; $i++) {
            $sheet->setCellValue('A' . $row, '');
            $sheet->getRowDimension($row)->setRowHeight(25);
            $row++;
        }

        // Style header rows
        $headerLabelStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $headerValueStyle = [
            'font' => ['bold' => false, 'size' => 11, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        // Style CLIENT and DATE row
        $sheet->getStyle('B' . $clientRow)->applyFromArray($headerLabelStyle);
        $sheet->getStyle('C' . $clientRow)->applyFromArray($headerValueStyle);
        $sheet->getStyle('G' . $clientRow)->applyFromArray($headerLabelStyle);
        $sheet->getStyle('H' . $clientRow)->applyFromArray($headerValueStyle);

        // Style PROJECT row
        $sheet->getStyle('B' . $projectRow)->applyFromArray($headerLabelStyle);
        $sheet->getStyle('C' . $projectRow)->applyFromArray($headerValueStyle);

        // Style ITEM row
        $sheet->getStyle('B' . $itemRow)->applyFromArray($headerLabelStyle);
        $sheet->getStyle('C' . $itemRow)->applyFromArray($headerValueStyle);

        // Table Header (A empty, shifted right)
        $tableHeaderRow = $row;
        $sheet->setCellValue('B' . $tableHeaderRow, 'S.NO');
        $sheet->setCellValue('C' . $tableHeaderRow, 'DESCRIRTION');
        $sheet->setCellValue('D' . $tableHeaderRow, 'UNIT');
        $sheet->setCellValue('E' . $tableHeaderRow, 'QTY');
        $sheet->setCellValue('F' . $tableHeaderRow, 'RATE');
        $sheet->setCellValue('G' . $tableHeaderRow, 'AMOUNT');
        $sheet->setCellValue('H' . $tableHeaderRow, 'REMARKS');

        // Style table header
        $tableHeaderStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'f0f0f0']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('B' . $tableHeaderRow . ':H' . $tableHeaderRow)->applyFromArray($tableHeaderStyle);
        $sheet->getStyle('C' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($tableHeaderRow)->setRowHeight(30);

        // Table Data (A empty, shifted right)
        $dataRow = $tableHeaderRow + 1;
        $totalAmount = 0;
        foreach ($itemData['summaries'] as $index => $summary) {
            $sheet->setCellValue('B' . $dataRow, $index + 1);
            $sheet->setCellValue('C' . $dataRow, $summary->invoiceRate->name ?? '');
            $sheet->setCellValue('D' . $dataRow, $summary->invoiceRate->unit ?? '');
            $sheet->setCellValue('E' . $dataRow, number_format($summary->quantity, 0));
            $sheet->setCellValue('F' . $dataRow, number_format($summary->invoiceRate->rate ?? 0, 0));
            $sheet->setCellValue('G' . $dataRow, number_format($summary->amount, 0));
            $sheet->setCellValue('H' . $dataRow, $summary->remarks ?? '');

            // Accumulate total amount
            $totalAmount += $summary->amount;

            // Style data rows
            $dataStyle = [
                'font' => ['size' => 11, 'color' => ['rgb' => '000000']],
                'borders' => [
                    'vertical' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ],
                    'left' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            $sheet->getStyle('B' . $dataRow . ':H' . $dataRow)->applyFromArray($dataStyle);
            $sheet->getStyle('C' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Format QTY, RATE, and AMOUNT as integers
            $sheet->getStyle('E' . $dataRow)->getNumberFormat()->setFormatCode('0');
            $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G' . $dataRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getRowDimension($dataRow)->setRowHeight(25);

            $dataRow++;
        }

        // Add 3-4 blank rows after last item details (A empty, shifted right)
        $blankRowsCount = 10 - count($itemData['summaries']) + 5;
        if ($blankRowsCount > 0) {
            for ($i = 0; $i < $blankRowsCount; $i++) {
                // Style blank rows with borders to match table style
                $blankRowStyle = [
                    'borders' => [
                        'vertical' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ],
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ];
                $sheet->getStyle('B' . $dataRow . ':H' . $dataRow)->applyFromArray($blankRowStyle);
                $sheet->getRowDimension($dataRow)->setRowHeight(25);
                $dataRow++;
            }
        }

        // Add TOTAL AMOUNT row (A empty, shifted right)
        $totalRow = $dataRow;
        $sheet->setCellValue('C' . $totalRow, 'TOTAL AMOUNT');
        $sheet->setCellValue('F' . $totalRow, 'RS');
        $sheet->setCellValue('G' . $totalRow, number_format($totalAmount, 0));

        // Style total row
        $totalRowStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '000000']],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('B' . $totalRow . ':H' . $totalRow)->applyFromArray($totalRowStyle);
        $sheet->getStyle('C' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getRowDimension($totalRow)->setRowHeight(30);
        $dataRow++;

        // Add Notes Section
        $notes = $itemData['item']->notes;
        if (!empty($notes) && is_array($notes)) {
            // Leave 2 blank rows
            $dataRow += 2;

            // Label "Notes:" in Cell C
            $sheet->setCellValue('B' . $dataRow, 'Notes:');
            $sheet->getStyle('B' . $dataRow)->getFont()->setBold(true);
            $sheet->getStyle('B' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Notes in Cell D as ordered list
            foreach ($notes as $index => $note) {
                $sheet->setCellValue('C' . $dataRow, ($index + 1) . '. ' . $note);
                $sheet->mergeCells('C' . $dataRow . ':H' . $dataRow);
                $sheet->getStyle('C' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $dataRow++;
            }
        }

        for ($i = 1; $i <= 2; $i++) {
            $sheet->getRowDimension($dataRow)->setRowHeight(25);
            $dataRow++;
        }
        
        // Add Monogram Image
        if (file_exists(public_path('images/monogram.jpeg'))) {
            $drawing = new Drawing();
            $drawing->setName('Monogram');
            $drawing->setDescription('Monogram');
            $drawing->setPath(public_path('images/monogram.jpeg'));
            $drawing->setHeight(60);
            $drawing->setOffsetX(20);
            $drawing->setCoordinates('F' . $dataRow);
            $drawing->setWorksheet($sheet);
        }

        $sheet->setCellValue('G' . $dataRow, 'MIA CONSTRUCTION');
        $sheet->getStyle('G' . $dataRow)->getFont()->setBold(true);
        $sheet->getStyle('G' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $dataRow++;
        $sheet->setCellValue('G' . $dataRow, 'MUHAMMAD IMRAN');
        $sheet->getStyle('G' . $dataRow)->getFont()->setBold(true);
        $sheet->getStyle('G' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($dataRow)->setRowHeight(30);
        $dataRow++;

        // Set column widths (A empty, shifted right)
        $sheet->getColumnDimension('A')->setWidth(2); // Empty column - very narrow
        $sheet->getColumnDimension('B')->setWidth(8); // S.NO
        $sheet->getColumnDimension('C')->setWidth(30); // DESCRIPTION
        $sheet->getColumnDimension('D')->setWidth(12); // UNIT
        $sheet->getColumnDimension('E')->setWidth(10); // QTY
        $sheet->getColumnDimension('F')->setWidth(12); // RATE
        $sheet->getColumnDimension('G')->setWidth(15); // AMOUNT
        $sheet->getColumnDimension('H')->setWidth(25); // REMARKS

        // Set page setup
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Calculate last row (dataRow was incremented after blank rows, so subtract 1)
        $lastRow = $dataRow - 1;
        $sheet->getPageSetup()->setPrintArea('A1:H' . $lastRow);

        // Set margins
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setBottom(1.0);
        $sheet->getPageMargins()->setLeft(0.5);

        // Set page footer
        $footerText = "&C&12____________________________________________________________________________________________________\n";
        $footerText .= "&B MIA CONSTRUCTION\n";
        $footerText .= "\n&C&10 Consultant - Designer - Estimator - Contractor\n";
        $footerText .= "&C&10 - 03218600259 -";
        $sheet->getHeaderFooter()->setOddFooter($footerText);
        $sheet->getHeaderFooter()->setEvenFooter($footerText);

        // Add Background Watermark (Centered behind table)
        if (file_exists(public_path('images/bg_monogram.jpeg'))) {
            $drawing = new Drawing();
            $drawing->setName('Watermark');
            $drawing->setDescription('Watermark');
            $drawing->setPath(public_path('images/bg_monogram.jpeg'));
            $drawing->setWidth(500);
            $drawing->setOpacity(35000); // 25% Opacity

            // Position in the middle of the table
            // $tableHeaderRow is defined earlier in this scope
            $watermarkRow = isset($tableHeaderRow) ? $tableHeaderRow + 2 : 15;
            $drawing->setCoordinates('C' . $watermarkRow);
            $drawing->setOffsetX(100); // Fine tune horizontal position

            $drawing->setWorksheet($sheet);
        }
    }
}
