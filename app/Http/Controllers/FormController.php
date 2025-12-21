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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
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
                    $q->where('item_name', 'like', "%{$search}%")
                        ->orWhere('client_name', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            // Get all forms matching the filters (before pagination)
            $allForms = $query->get();

            // Filter collection to get unique combinations of client_name and project_name
            // Get the latest form (highest ID) for each unique combination
            $uniqueForms = $allForms->groupBy(function ($form) {
                return $form->client_name . '|' . $form->project_name;
            })->map(function ($group) {
                // Always get the latest form from each group (highest ID)
                return $group->sortByDesc('id')->first();
            })->values();

            // Sort the unique forms collection based on orderBy
            if ($orderBy && in_array($orderBy, ['id', 'client_name', 'project_name', 'created_at'])) {
                if ($orderDir === 'desc') {
                    $uniqueForms = $uniqueForms->sortByDesc($orderBy)->values();
                } else {
                    $uniqueForms = $uniqueForms->sortBy($orderBy)->values();
                }
            } else {
                // Default sort by ID descending (latest first)
                $uniqueForms = $uniqueForms->sortByDesc('id')->values();
            }

            // Get total unique records count (all forms, not filtered)
            $totalRecords = Form::select('client_name', 'project_name')
                ->distinct()
                ->count();

            // Get filtered unique records count (after applying search/filters)
            $filteredRecords = $uniqueForms->count();

            // Apply pagination to the filtered collection
            $forms = $uniqueForms->slice($start, $length)->values();

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
    public function create(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('forms.index');
    }

    /**
     * Get client names for autocomplete.
     */
    public function getClientNames(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json([]);
        }

        $forms = Form::where('client_name', 'like', "%{$query}%")
            ->select('client_name', 'project_name')
            ->distinct()
            ->orderBy('client_name', 'asc')
            ->orderBy('project_name', 'asc')
            ->take(20)
            ->get();

        $suggestions = $forms->map(function ($form) {
            return [
                'client_name' => $form->client_name,
                'project_name' => $form->project_name,
                'display' => $form->client_name . ' - ' . $form->project_name
            ];
        })->toArray();

        return response()->json($suggestions);
    }

    /**
     * Get form fields for AJAX request.
     */
    public function getFormFields(Form $form): JsonResponse
    {
        $form->load('fields');
        $fields = $form->fields;

        // Ensure we have at least 30 rows
        $fieldCount = $fields->count();
        if ($fieldCount < 30) {
            for ($i = $fieldCount; $i < 30; $i++) {
                $field = new Field();
                $field->form_id = $form->id;
                $fields->add($field);
            }
        }

        $fieldsData = $fields->map(function ($field, $index) {
            return [
                'id' => $field->id,
                'index' => $index,
                'description' => $field->description ?? '',
                'quantity' => $field->quantity ?? '',
                'length' => $field->length ?? '',
                'width' => $field->width ?? '',
                'height' => $field->height ?? '',
                'product' => $field->product ?? '',
            ];
        });

        return response()->json([
            'form_id' => $form->id,
            'item_name' => $form->item_name,
            'fields' => $fieldsData
        ]);
    }

    /**
     * Get sidebar items for AJAX request.
     */
    public function getSidebarItems(Request $request): JsonResponse
    {
        $client_name = $request->get('client_name');
        $project_name = $request->get('project_name');

        if (!$client_name || !$project_name) {
            return response()->json([]);
        }

        $relatedForms = Form::where('client_name', $client_name)
            ->where('project_name', $project_name)
            ->whereNotNull('item_name')
            ->orderBy('item_name', 'asc')
            ->get(['id', 'item_name', 'created_at', 'client_name', 'project_name']);

        $items = $relatedForms->map(function ($form) {
            return [
                'id' => $form->id,
                'item_name' => $form->item_name,
                'created_at' => $form->created_at->toISOString(),
                'client_name' => $form->client_name,
                'project_name' => $form->project_name,
            ];
        });

        return response()->json($items);
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
            'fields.*.quantity' => 'nullable|numeric',
            'fields.*.length' => 'nullable|numeric|min:0',
            'fields.*.width' => 'nullable|numeric|min:0',
            'fields.*.height' => 'nullable|numeric|min:0',
            'fields.*.product' => 'nullable|numeric',
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

            if ($request->wantsJson() || $request->ajax()) {
                $form->load('fields');
                return response()->json([
                    'id' => $form->id,
                    'item_name' => $form->item_name,
                    'client_name' => $form->client_name,
                    'project_name' => $form->project_name,
                    'created_at' => $form->created_at->toISOString(),
                ], 201);
            }

            return redirect()->route('forms.index')
                ->with('success', 'Form created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to create form: ' . $e->getMessage()], 500);
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
    public function edit(Request $request)
    {
        $client_name = $request->get('client_name');
        $project_name = $request->get('project_name');
        $form = Form::where('client_name', $client_name)->where('project_name', $project_name)->first();
        if (!$form) {
            return redirect()->route('forms.index')
                ->with('error', 'Form with client name "' . $client_name . '" and project name "' . $project_name . '" not found.');
        }
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

        // Get all forms with the same client_name and project_name

        $relatedForms = Form::where('client_name', $form->client_name)
            ->whereNotNull('item_name')
            ->where('client_name', $client_name)
            ->where('project_name', $project_name)
            ->orderBy('item_name', 'asc')
            ->get(['id', 'item_name', 'created_at', 'client_name', 'project_name']);

        return view('forms.edit', compact('form', 'fields', 'relatedForms'));
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
            'fields.*.quantity' => 'nullable|numeric',
            'fields.*.length' => 'nullable|numeric|min:0',
            'fields.*.width' => 'nullable|numeric|min:0',
            'fields.*.height' => 'nullable|numeric|min:0',
            'fields.*.product' => 'nullable|numeric',
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

            if ($request->wantsJson() || $request->ajax()) {
                $form->load('fields');
                return response()->json([
                    'id' => $form->id,
                    'item_name' => $form->item_name,
                    'client_name' => $form->client_name,
                    'project_name' => $form->project_name,
                    'message' => 'Form updated successfully.'
                ]);
            }

            return redirect()->route('forms.index')
                ->with('success', 'Form updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to update form: ' . $e->getMessage()], 500);
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
     * Export multiple forms to Excel with multiple sheets.
     */
    public function exportByProject(Request $request): StreamedResponse|\Illuminate\Http\RedirectResponse|JsonResponse
    {
        $client_name = $request->get('client_name');
        $project_name = $request->get('project_name');

        if (!$client_name || !$project_name) {
            return back()->with('error', 'Client name and project name are required');
        }

        // Get all forms with matching client_name and project_name where item_name is not null
        $forms = Form::where('client_name', $client_name)
            ->where('project_name', $project_name)
            ->whereNotNull('item_name')
            ->with('fields')
            ->orderBy('item_name', 'asc')
            ->get();

        if ($forms->isEmpty()) {
            return back()->with('error', 'No forms found with the specified client name and project name. Please ensure there are forms with item names for this client and project.');
        }

        $spreadsheet = new Spreadsheet();

        // Remove default sheet
        $spreadsheet->removeSheetByIndex(0);

        // Helper function to format date
        $formatDateWithOrdinal = function ($date) {
            if (!$date) return '';
            return $date->format('d/m/Y');
        };

        // Helper function to find logo path
        $findLogoPath = function () {
            $possiblePaths = [
                public_path('images/monogram.jpeg'),
                base_path('../images/monogram.jpeg'),
                base_path('../../images/monogram.jpeg'),
                '/images/monogram.jpeg',
                public_path('../images/monogram.jpeg'),
            ];

            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            return null;
        };

        $logoPath = $findLogoPath();

        // Create a sheet for each form
        foreach ($forms as $index => $form) {
            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $form->item_name ?? 'Sheet' . ($index + 1));
            $spreadsheet->addSheet($sheet, $index);

            // Set sheet title (Excel sheet names have a 31 character limit)
            $sheetTitle = substr($form->item_name ?? 'Sheet' . ($index + 1), 0, 31);
            $sheet->setTitle($sheetTitle);

            // Add logo image in A1-A3 (merged cells)
            if ($logoPath && file_exists($logoPath)) {
                $sheet->mergeCells('A1:A3');
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);

                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $columnWidth = $sheet->getColumnDimension('A')->getWidth() * 7;
                $imageHeight = 70;
                $imageWidth = $imageHeight;
                $totalRowHeight = 60;
                $offsetX = ($columnWidth - $imageWidth) / 2;
                $offsetY = ($totalRowHeight - $imageHeight) / 2;

                $drawing = new Drawing();
                $drawing->setName('Monogram');
                $drawing->setDescription('Monogram');
                $drawing->setPath($logoPath);
                $drawing->setHeight($imageHeight);
                $drawing->setWidth($imageWidth);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(max(0, $offsetX));
                $drawing->setOffsetY(max(0, $offsetY));
                $drawing->setWorksheet($sheet);
            }

            // Calculate sum of total and round up to next whole number
            $sumOfTotal = ceil($form->fields->sum('product') ?? 0);

            // Header Section (Rows 1-3)
            $sheet->setCellValue('B1', 'Project Name');
            $sheet->setCellValue('C1', ucwords(strtolower($form->project_name)));

            $sheet->setCellValue('B2', 'Item');
            $sheet->setCellValue('C2', ucwords(strtolower($form->item_name ?? '')));

            $sheet->setCellValue('B3', 'T.QTY');
            $sheet->setCellValue('C3', (int)$sumOfTotal);
            // Format T.QTY as integer (no decimals)
            $sheet->getStyle('C3')->getNumberFormat()->setFormatCode('0');
            $sheet->setCellValue('F3', 'Date');
            $sheet->setCellValue('G3', $formatDateWithOrdinal($form->created_at));

            // Style header rows
            $headerLabelStyle = [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            $headerValueStyle = [
                'font' => ['bold' => false, 'size' => 12, 'color' => ['rgb' => '000000']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];

            $sheet->getStyle('B1')->applyFromArray($headerLabelStyle);
            $sheet->getStyle('B2')->applyFromArray($headerLabelStyle);
            $sheet->getStyle('B3')->applyFromArray($headerLabelStyle);
            $sheet->getStyle('F3')->applyFromArray($headerLabelStyle);
            $sheet->getStyle('C1')->applyFromArray($headerValueStyle);
            $sheet->getStyle('C2')->applyFromArray($headerValueStyle);
            $sheet->getStyle('C3')->applyFromArray($headerValueStyle);
            $sheet->getStyle('G3')->applyFromArray($headerValueStyle);

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

            // Style table header
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
            $sheet->getStyle('B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Fields Data
            $row = 6;
            foreach ($form->fields as $fieldIndex => $field) {
                $sheet->setCellValue('A' . $row, $fieldIndex + 1);
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
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                // Format TOTAL column (G) as integer (no decimals)
                $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('0');

                $row++;
            }

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(11.5);
            $sheet->getColumnDimension('B')->setWidth(31);
            $sheet->getColumnDimension('C')->setWidth(7);
            $sheet->getColumnDimension('D')->setWidth(11.5);
            $sheet->getColumnDimension('E')->setWidth(11.5);
            $sheet->getColumnDimension('F')->setWidth(11.5);
            $sheet->getColumnDimension('G')->setWidth(11.5);

            // Set page setup
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            $lastRow = $row - 1;
            $sheet->getPageSetup()->setPrintArea('A1:G' . $lastRow);

            // Set margins
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.5);
            $sheet->getPageMargins()->setBottom(1.0);
            $sheet->getPageMargins()->setLeft(0.5);

            // Set page footer
            $footerText = "&C&12&B MIA CONSTRUCTION\n";
            $footerText .= "\n&C&10 Consultant - Designer - Estimator - Contractor\n";
            $footerText .= "&C&10 - 03218600259 -";
            $sheet->getHeaderFooter()->setOddFooter($footerText);
            $sheet->getHeaderFooter()->setEvenFooter($footerText);
        }

        // Set active sheet to first one
        $spreadsheet->setActiveSheetIndex(0);

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Generate filename
        $filename = str_replace(' ', '_', $client_name) . '_' . str_replace(' ', '_', $project_name) . '_' . date('Y-m-d') . '.xlsx';

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
