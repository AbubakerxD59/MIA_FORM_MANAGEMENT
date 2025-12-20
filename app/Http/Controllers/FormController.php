<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormController extends Controller
{
    /**
     * Display a listing of the forms.
     */
    public function index(Request $request): View|JsonResponse
    {
        // Check if this is a DataTables request
        if ($request->ajax() || $request->wantsJson()) {
            $query = Form::query();

            // Get DataTables parameters
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';
            $orderColumn = $request->get('order')[0]['column'] ?? 0;
            $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

            // Column mapping (index 0 is row number, 1=item_name, 2=client_name, 3=project_name, 4=created_at, 5=actions)
            $columns = [null, 'item_name', 'client_name', 'project_name', 'created_at', null];
            $orderBy = $columns[$orderColumn] ?? 'id';

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            // Get total records
            $totalRecords = Form::count();
            $filteredRecords = $query->count();

            // Apply ordering and pagination
            $forms = $query->orderBy($orderBy, $orderDir)
                ->skip($start)
                ->take($length)
                ->get();

            // Format data for DataTables
            $data = $forms->map(function ($form) {
                return [
                    'id' => $form->id,
                    'item_name' => $form->item_name,
                    'client_name' => $form->client_name,
                    'project_name' => $form->project_name,
                    'created_at' => $form->created_at?->toISOString(),
                ];
            });

            return response()->json([
                'draw' => intval($request->get('draw', 1)),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('forms.index');
    }

    /**
     * Show the form for creating a new form.
     */
    public function create(): View
    {
        return view('forms.create');
    }

    /**
     * Store a newly created form in storage.
     */
    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        // Basic validation only (client-side validation handles the rest)
        $validated = $request->validate([
            'item_name' => 'nullable|string|max:255',
            'client_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'fields' => 'nullable|array',
            'fields.*.description' => 'nullable|string',
            'fields.*.quantity' => 'nullable|numeric|min:0',
            'fields.*.length' => 'nullable|numeric|min:0',
            'fields.*.width' => 'nullable|numeric|min:0',
            'fields.*.height' => 'nullable|numeric|min:0',
            'fields.*.product' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $form = Form::create([
                'item_name' => $validated['item_name'] ?? null,
                'client_name' => $validated['client_name'],
                'project_name' => $validated['project_name'],
            ]);

            if (isset($validated['fields']) && is_array($validated['fields'])) {
                foreach ($validated['fields'] as $fieldData) {
                    // Only create field if all required fields are present
                    if (
                        !empty($fieldData['description']) &&
                        isset($fieldData['quantity']) &&
                        isset($fieldData['length']) &&
                        isset($fieldData['width']) &&
                        isset($fieldData['height']) &&
                        isset($fieldData['product'])
                    ) {
                        $form->fields()->create($fieldData);
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                $form->load('fields');
                return response()->json($form, 201);
            }

            return redirect()->route('forms.index')
                ->with('success', 'Form created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create form'], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to create form. Please try again.');
        }
    }

    /**
     * Display the specified form.
     */
    public function show(Form $form): View|JsonResponse
    {
        $form->load('fields');

        if (request()->wantsJson()) {
            return response()->json($form);
        }

        return view('forms.show', compact('form'));
    }

    /**
     * Show the form for editing the specified form.
     */
    public function edit(Form $form): View
    {
        $form->load('fields');
        // Ensure we have at least 30 rows for editing
        $fields = $form->fields;
        $fieldCount = $fields->count();
        if ($fieldCount < 30) {
            // Add empty field objects to reach 30 rows
            for ($i = $fieldCount; $i < 30; $i++) {
                $field = new Field();
                $field->form_id = $form->id;
                $fields->add($field);
            }
        }
        return view('forms.edit', compact('form', 'fields'));
    }

    /**
     * Update the specified form in storage.
     */
    public function update(Request $request, Form $form): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        // Basic validation only (client-side validation handles the rest)
        $validated = $request->validate([
            'item_name' => 'nullable|string|max:255',
            'client_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'fields' => 'nullable|array',
            'fields.*.id' => 'nullable|exists:fields,id',
            'fields.*.description' => 'nullable|string',
            'fields.*.quantity' => 'nullable|numeric|min:0',
            'fields.*.length' => 'nullable|numeric|min:0',
            'fields.*.width' => 'nullable|numeric|min:0',
            'fields.*.height' => 'nullable|numeric|min:0',
            'fields.*.product' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $form->update([
                'item_name' => $validated['item_name'] ?? null,
                'client_name' => $validated['client_name'],
                'project_name' => $validated['project_name'],
            ]);

            if (isset($validated['fields']) && is_array($validated['fields'])) {
                $existingFieldIds = [];

                foreach ($validated['fields'] as $fieldData) {
                    // Check if field has any data
                    $hasData = !empty(array_filter($fieldData, function ($value, $key) {
                        return $key !== 'id' && $value !== null && $value !== '';
                    }, ARRAY_FILTER_USE_BOTH));

                    if (isset($fieldData['id']) && $fieldData['id']) {
                        // Update existing field
                        $field = Field::find($fieldData['id']);
                        if ($field && $field->form_id === $form->id) {
                            if ($hasData) {
                                $field->update(Arr::except($fieldData, ['id']));
                            } else {
                                // Delete field if no data
                                $field->delete();
                            }
                            $existingFieldIds[] = $fieldData['id'];
                        }
                    } elseif ($hasData) {
                        // Create new field
                        $newField = $form->fields()->create(Arr::except($fieldData, ['id']));
                        $existingFieldIds[] = $newField->id;
                    }
                }

                // Delete fields that were removed
                $form->fields()->whereNotIn('id', $existingFieldIds)->delete();
            } else {
                // If no fields provided, delete all existing fields
                $form->fields()->delete();
            }

            DB::commit();

            if ($request->wantsJson()) {
                $form->load('fields');
                return response()->json($form);
            }

            return redirect()->route('forms.index')
                ->with('success', 'Form updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update form'], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update form. Please try again.');
        }
    }

    /**
     * Remove the specified form from storage.
     */
    public function destroy(Form $form): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $form->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Form deleted successfully.']);
        }

        return redirect()->route('forms.index')
            ->with('success', 'Form deleted successfully.');
    }

    /**
     * Display a listing of deleted forms.
     */
    public function deleted(Request $request): View|JsonResponse
    {
        // Check if this is a DataTables request
        if ($request->ajax() || $request->wantsJson()) {
            $query = Form::onlyTrashed();

            // Get DataTables parameters
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';
            $orderColumn = $request->get('order')[0]['column'] ?? 0;
            $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

            // Column mapping (index 0 is row number, 1=item_name, 2=client_name, 3=project_name, 4=created_at, 5=actions)
            $columns = [null, 'item_name', 'client_name', 'project_name', 'created_at', null];
            $orderBy = $columns[$orderColumn] ?? 'id';

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            // Get total records
            $totalRecords = Form::onlyTrashed()->count();
            $filteredRecords = $query->count();

            // Apply ordering and pagination
            $forms = $query->orderBy($orderBy, $orderDir)
                ->skip($start)
                ->take($length)
                ->get();

            // Format data for DataTables
            $data = $forms->map(function ($form) {
                return [
                    'id' => $form->id,
                    'item_name' => $form->item_name,
                    'client_name' => $form->client_name,
                    'project_name' => $form->project_name,
                    'created_at' => $form->created_at?->toISOString(),
                ];
            });

            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ]);
        }

        return view('forms.deleted');
    }

    /**
     * Restore the specified deleted form.
     */
    public function restore($id)
    {
        $form = Form::onlyTrashed()->findOrFail($id);

        DB::beginTransaction();
        try {
            // Restore all associated fields first
            Field::onlyTrashed()->where('form_id', $form->id)->restore();

            // Restore the form
            $form->restore();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Form restored successfully.']);
            }

            return redirect()->route('forms.deleted')
                ->with('success', 'Form restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Failed to restore form: ' . $e->getMessage()], 500);
            }

            return redirect()->route('forms.deleted')
                ->with('error', 'Failed to restore form: ' . $e->getMessage());
        }
    }

    /**
     * Export form to Excel.
     */
    public function export(Form $form): StreamedResponse
    {
        $form->load('fields');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set sheet title
        $sheet->setTitle('Form Details');

        // Helper function to format date with ordinal suffix
        $formatDateWithOrdinal = function ($date) {
            if (!$date) return '';
            return $date->format('d/m/Y');
        };

        // Add logo image in A1-A3 (merged cells)
        // Image is located at images/monogram.jpeg
        // Try multiple possible paths
        $logoPath = null;
        $possiblePaths = [
            public_path('images/monogram.jpeg'),    // In public/images directory
            base_path('../images/monogram.jpeg'),   // One level up from Laravel root
            base_path('../../images/monogram.jpeg'), // Two levels up
            '/images/monogram.jpeg',                 // Absolute path on server
            public_path('../images/monogram.jpeg'), // One level up from public
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $logoPath = $path;
                break;
            }
        }

        if (file_exists($logoPath)) {
            // Merge A1:A3 for logo first
            $sheet->mergeCells('A1:A3');
            $sheet->getRowDimension(1)->setRowHeight(20);
            $sheet->getRowDimension(2)->setRowHeight(20);
            $sheet->getRowDimension(3)->setRowHeight(20);

            // Center align the merged cell (this helps with image positioning)
            $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Add monogram image centered in the merged cell
            // Calculate column width in pixels (approximately 7 pixels per character unit)
            $columnWidth = $sheet->getColumnDimension('A')->getWidth() * 7; // Convert to pixels
            $imageHeight = 70;
            $imageWidth = $imageHeight; // Assuming square image, adjust if needed

            // Calculate offsets to center the image (in pixels)
            // OffsetX: (column width - image width) / 2
            // OffsetY: (row height - image height) / 2
            $totalRowHeight = 60; // 3 rows * 20 pixels each
            $offsetX = ($columnWidth - $imageWidth) / 2;
            $offsetY = ($totalRowHeight - $imageHeight) / 2;

            $drawing = new Drawing();
            $drawing->setName('Monogram');
            $drawing->setDescription('Monogram');
            $drawing->setPath($logoPath);
            $drawing->setHeight($imageHeight);
            $drawing->setWidth($imageWidth);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(max(0, $offsetX)); // Center horizontally, ensure non-negative
            $drawing->setOffsetY(max(0, $offsetY)); // Center vertically, ensure non-negative
            $drawing->setWorksheet($sheet);
        }

        // Calculate sum of total before using it
        $sumOfTotal = $form->fields->sum('product') ?? 0;

        // Header Section (Rows 1-3) - Matching the image layout
        // Row 1: Project Name
        $sheet->setCellValue('B1', 'Project Name');
        $sheet->setCellValue('C1', ucwords(strtolower($form->project_name)));

        // Row 2: Item
        $sheet->setCellValue('B2', 'Item');
        $sheet->setCellValue('C2', ucwords(strtolower($form->item_name ?? '')));

        // Row 3: T.QTY and Date
        $sheet->setCellValue('B3', 'T.QTY');
        $sheet->setCellValue('C3', $sumOfTotal);
        $sheet->setCellValue('F3', 'Date');
        $sheet->setCellValue('G3', $formatDateWithOrdinal($form->created_at));

        // Style header rows - labels (bold, left-aligned)
        $headerLabelStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        // Style header rows - values (not bold, right-aligned)
        $headerValueStyle = [
            'font' => ['bold' => false, 'size' => 12, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        // Apply bold style to labels: Project Name, Item, T.QTY, Date
        $sheet->getStyle('B1')->applyFromArray($headerLabelStyle); // Project Name
        $sheet->getStyle('B2')->applyFromArray($headerLabelStyle); // Item
        $sheet->getStyle('B3')->applyFromArray($headerLabelStyle); // T.QTY
        $sheet->getStyle('E3')->applyFromArray($headerLabelStyle); // Date
        // Apply value style to actual values
        $sheet->getStyle('C1')->applyFromArray($headerValueStyle); // Project Name value
        $sheet->getStyle('C2')->applyFromArray($headerValueStyle); // Item value
        $sheet->getStyle('C3')->applyFromArray($headerValueStyle); // T.QTY value
        $sheet->getStyle('F3')->applyFromArray($headerValueStyle); // Date value

        // Add spacing row
        $sheet->setCellValue('A4', '');

        // Fields Table Header (Row 5)
        $sheet->setCellValue('A5', 'S. NO');
        $sheet->setCellValue('B5', 'DESCRIPTION');
        $sheet->setCellValue('C5', 'QTY');
        $sheet->setCellValue('D5', 'L');
        $sheet->setCellValue('E5', 'W');
        $sheet->setCellValue('F5', 'H');
        $sheet->setCellValue('G5', 'TOTAL');

        // Style table header with gray background
        $tableHeaderStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'b9b9b9']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '404040']
                ]
            ]
        ];
        $sheet->getStyle('A5:G5')->applyFromArray($tableHeaderStyle);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Style DESCRIPTION column to be left-aligned
        $sheet->getStyle('B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Fields Data
        $row = 6;
        foreach ($form->fields as $index => $field) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $field->description ?? '');
            $sheet->setCellValue('C' . $row, $field->quantity ?? '');
            $sheet->setCellValue('D' . $row, $field->length ?? '');
            $sheet->setCellValue('E' . $row, $field->width ?? '');
            $sheet->setCellValue('F' . $row, $field->height ?? '');
            $sheet->setCellValue('G' . $row, $field->product ?? '');

            // Style data rows
            $dataStyle = [
                'font' => ['size' => 11, 'color' => ['rgb' => '000000']],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '404040']
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($dataStyle);

            // Left-align DESCRIPTION column
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $row++;
        }

        // Footer removed from sheet - will be set as page footer instead

        // Set column widths optimized for A4 paper
        // Columns A-G are used for both header and table sections
        // Header: A=Logo, B=Labels, C=Values, E=Item/T.QTY labels, F=Item/T.QTY values
        // Table: A=S.NO, B=DESCRIPTION, C=QTY, D=L, E=W, F=H, G=TOTAL
        $sheet->getColumnDimension('A')->setWidth(11.5);  // Logo / S. NO (increased for monogram)
        $sheet->getColumnDimension('B')->setWidth(31);  // Labels / DESCRIPTION (increased for better visibility)
        $sheet->getColumnDimension('C')->setWidth(7);  // Values / QTY (increased for better visibility)
        $sheet->getColumnDimension('D')->setWidth(11.5);   // Empty / L (reduced as much as possible)
        $sheet->getColumnDimension('E')->setWidth(11.5);   // Item, T.QTY / W (reduced as much as possible)
        $sheet->getColumnDimension('F')->setWidth(11.5);  // Item value, T.QTY / H (increased for better visibility)
        $sheet->getColumnDimension('G')->setWidth(11.5);  // Empty / TOTAL (increased for better visibility)

        // Set page setup for A4 paper
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Set print area to include all data (footer rows removed, using page footer instead)
        $lastRow = $row - 1; // Last data row
        $sheet->getPageSetup()->setPrintArea('A1:G' . $lastRow);

        // Set rows to repeat at top (optional - for multi-page printing)
        // $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

        // Set margins for better printing (in inches)
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setBottom(1.0); // Space for page footer
        $sheet->getPageMargins()->setLeft(0.5);

        // Set page footer that appears on every printed page
        // Format: &L = Left, &C = Center, &R = Right, &B = Bold, &12 = Font size 12, &10 = Font size 10
        // All three lines should be centered
        $footerText = "&C&12&B MIA CONSTRUCTION\n";
        $footerText .= "\n&C&10 Consultant - Designer - Estimator - Contractor\n";
        $footerText .= "&C&10 - 03218600259 -";
        $sheet->getHeaderFooter()->setOddFooter($footerText);
        $sheet->getHeaderFooter()->setEvenFooter($footerText); // For even pages

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Generate filename
        $filename = str_replace(' ', '_', $form->client_name) . '_' . date('Y-m-d') . '.xlsx';

        // Return as download
        return new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
