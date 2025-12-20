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
        // Image is located outside root folder at images/monogram.jpeg on server
        // Try multiple possible paths
        $logoPath = null;
        $possiblePaths = [
            base_path('../images/monogram.jpeg'),  // One level up from Laravel root
            base_path('../../images/monogram.jpeg'), // Two levels up
            '/images/monogram.jpeg',  // Absolute path on server
            public_path('../images/monogram.jpeg'), // One level up from public
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $logoPath = $path;
                break;
            }
        }

        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Monogram');
            $drawing->setDescription('Monogram');
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);

            // Merge A1:A3 for logo
            $sheet->mergeCells('A1:A3');
            $sheet->getRowDimension(1)->setRowHeight(20);
            $sheet->getRowDimension(2)->setRowHeight(20);
            $sheet->getRowDimension(3)->setRowHeight(20);
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

        // Add spacing row before footer
        $row++;

        // Add footer section with monogram on the left
        // Footer Row 1: MIA CONSTRUCTION (centered, bold)
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('A' . $row, 'MIA CONSTRUCTION');
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // Footer Row 2: Monogram on left, text on right
        if (file_exists($logoPath)) {
            $drawingFooter = new Drawing();
            $drawingFooter->setName('Monogram Footer');
            $drawingFooter->setDescription('Monogram Footer');
            $drawingFooter->setPath($logoPath);
            $drawingFooter->setHeight(30);
            $drawingFooter->setCoordinates('A' . $row);
            $drawingFooter->setWorksheet($sheet);
        }
        $sheet->setCellValue('B' . $row, 'Consultant - Designer - Estimator - Contractor');
        $sheet->getStyle('B' . $row)->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(30);
        $row++;

        // Footer Row 3: Phone number (centered)
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('A' . $row, '- 03218600259 -');
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);

        // Set column widths optimized for A4 paper
        // Columns A-G are used for both header and table sections
        // Header: A=Logo, B=Labels, C=Values, F=Date label, G=Date value
        // Table: A=S.NO, B=DESCRIPTION, C=QTY, D=L, E=W, F=H, G=TOTAL
        // Width conversion: 1 character unit ≈ 7 pixels
        // 106px = 11 character units, 58px = 8.3 character units
        $sheet->getColumnDimension('A')->setWidth(11.5);  // Logo / S. NO (106px)
        $sheet->getColumnDimension('B')->setWidth(31);    // Labels / DESCRIPTION
        $sheet->getColumnDimension('C')->setWidth(7);   // Values / QTY (58px)
        $sheet->getColumnDimension('D')->setWidth(11.5);  // L (106px)
        $sheet->getColumnDimension('E')->setWidth(11.5);  // W (106px)
        $sheet->getColumnDimension('F')->setWidth(11.5);  // Date label / H (106px)
        $sheet->getColumnDimension('G')->setWidth(11.5);  // Date value / TOTAL (106px)

        // Set page setup for Letter paper (8.5" x 11")
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setHorizontalCentered(false);
        $sheet->getPageSetup()->setVerticalCentered(false);

        // Set print area to include all data including footer
        $lastRow = $row; // Last row including footer
        $sheet->getPageSetup()->setPrintArea('A1:G' . $lastRow);

        // Set rows to repeat at top (optional - for multi-page printing)
        // $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

        // Set margins optimized for Letter paper (in inches)
        // Letter: 8.5" wide, 11" tall
        // Standard margins: 0.5" on all sides for better printability
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setBottom(0.5);
        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setHeader(0.3);
        $sheet->getPageMargins()->setFooter(0.3);

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
