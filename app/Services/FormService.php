<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Field;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormService
{
    /**
     * Get forms data for DataTables.
     */
    public function getFormsForDataTable(Request $request): array
    {
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

        return [
            'draw' => intval($request->get('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

    /**
     * Get client names for autocomplete.
     */
    public function getClientNames(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        $forms = Form::where('client_name', 'like', "%{$query}%")
            ->select('client_name', 'project_name')
            ->distinct()
            ->orderBy('client_name', 'asc')
            ->orderBy('project_name', 'asc')
            ->take(20)
            ->get();

        return $forms->map(function ($form) {
            return [
                'client_name' => $form->client_name,
                'project_name' => $form->project_name,
                'display' => $form->client_name . ' - ' . $form->project_name
            ];
        })->toArray();
    }

    /**
     * Get form fields data.
     */
    public function getFormFieldsData(Form $form): array
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

        return [
            'form_id' => $form->id,
            'item_name' => $form->item_name,
            'unit' => $form->unit,
            'fields' => $fieldsData
        ];
    }

    /**
     * Get sidebar items for a client and project.
     */
    public function getSidebarItems(string $clientName, string $projectName): array
    {
        if (!$clientName || !$projectName) {
            return [];
        }

        $relatedForms = Form::where('client_name', $clientName)
            ->where('project_name', $projectName)
            ->whereNotNull('item_name')
            ->orderBy('item_name', 'asc')
            ->get(['id', 'item_name', 'created_at', 'client_name', 'project_name']);

        return $relatedForms->map(function ($form) {
            return [
                'id' => $form->id,
                'item_name' => $form->item_name,
                'created_at' => $form->created_at->toISOString(),
                'client_name' => $form->client_name,
                'project_name' => $form->project_name,
            ];
        })->toArray();
    }

    /**
     * Store a new form.
     */
    public function storeForm(array $validated): Form
    {
        DB::beginTransaction();
        try {
            $form = Form::create([
                'item_name' => $validated['item_name'] ?? null,
                'unit' => $validated['unit'] ?? null,
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
            return $form;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a form.
     */
    public function updateForm(Form $form, array $validated): Form
    {
        DB::beginTransaction();
        try {
            $form->update([
                'item_name' => $validated['item_name'] ?? null,
                'unit' => $validated['unit'] ?? null,
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
            return $form->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete forms by project.
     */
    public function deleteFormsByProject(string $clientName, string $projectName): int
    {
        DB::beginTransaction();
        try {
            // Get all forms with matching client_name and project_name
            $forms = Form::where('client_name', $clientName)
                ->where('project_name', $projectName)
                ->get();

            if ($forms->isEmpty()) {
                DB::rollBack();
                throw new \Exception('No forms found with the specified client name and project name.');
            }

            // Delete all associated fields first (cascade delete)
            foreach ($forms as $form) {
                $form->fields()->delete();
            }

            // Delete all forms
            $deletedCount = Form::where('client_name', $clientName)
                ->where('project_name', $projectName)
                ->delete();

            DB::commit();
            return $deletedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Duplicate forms by project.
     */
    public function duplicateFormsByProject(string $clientName, string $projectName): int
    {
        DB::beginTransaction();
        try {
            $randomSuffix = Str::random(6);
            // Get all forms with matching client_name and project_name
            $forms = Form::where('client_name', $clientName)
                ->where('project_name', $projectName)
                ->with('fields')
                ->get();

            if ($forms->isEmpty()) {
                DB::rollBack();
                throw new \Exception('No forms found with the specified client name and project name.');
            }

            $duplicatedCount = 0;

            // Duplicate each form and its fields
            foreach ($forms as $originalForm) {
                // Create a new form with the same data
                $newForm = Form::create([
                    'item_name' => $originalForm->item_name,
                    'unit' => $originalForm->unit,
                    'client_name' => $originalForm->client_name . ' - ' . $randomSuffix,
                    'project_name' => $originalForm->project_name . ' - ' . $randomSuffix,
                ]);

                // Duplicate all fields associated with the original form
                foreach ($originalForm->fields as $originalField) {
                    $newForm->fields()->create([
                        'description' => $originalField->description,
                        'quantity' => $originalField->quantity,
                        'length' => $originalField->length,
                        'width' => $originalField->width,
                        'height' => $originalField->height,
                        'product' => $originalField->product,
                    ]);
                }

                $duplicatedCount++;
            }

            DB::commit();
            return $duplicatedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update client name and project name for all forms with the same client_name and project_name.
     */
    public function updateFormsDetails(string $oldClientName, string $oldProjectName, string $newClientName, string $newProjectName): int
    {
        DB::beginTransaction();
        try {
            // Get all forms with matching old client_name and project_name
            $forms = Form::where('client_name', $oldClientName)
                ->where('project_name', $oldProjectName)
                ->get();

            if ($forms->isEmpty()) {
                DB::rollBack();
                throw new \Exception('No forms found with the specified client name and project name.');
            }

            // Update all forms with new client_name and project_name
            $updatedCount = Form::where('client_name', $oldClientName)
                ->where('project_name', $oldProjectName)
                ->update([
                    'client_name' => $newClientName,
                    'project_name' => $newProjectName,
                ]);

            DB::commit();
            return $updatedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get deleted forms for DataTables.
     */
    public function getDeletedFormsForDataTable(Request $request): array
    {
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

        return [
            'draw' => intval($request->get('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ];
    }

    /**
     * Restore a deleted form.
     */
    public function restoreForm(int $id): Form
    {
        $form = Form::onlyTrashed()->findOrFail($id);

        DB::beginTransaction();
        try {
            // Restore all associated fields first
            Field::onlyTrashed()->where('form_id', $form->id)->restore();

            // Restore the form
            $form->restore();

            DB::commit();
            return $form;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Prepare form for editing.
     */
    public function prepareFormForEdit(string $clientName, string $projectName): array
    {
        $form = Form::where('client_name', $clientName)
            ->where('project_name', $projectName)
            ->first();

        if (!$form) {
            throw new \Exception('Form with client name "' . $clientName . '" and project name "' . $projectName . '" not found.');
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
            ->where('client_name', $clientName)
            ->where('project_name', $projectName)
            ->orderBy('item_name', 'asc')
            ->get(['id', 'item_name', 'created_at', 'client_name', 'project_name']);

        return [
            'form' => $form,
            'fields' => $fields,
            'relatedForms' => $relatedForms
        ];
    }

    /**
     * Generate a sheet for a form in Excel export.
     */
    public function generateFormSheet($sheet, Form $form, ?string $logoPath, array $config = []): void
    {
        $headerRows = $config['headerRows'] ?? 3;
        $includeClientName = $config['includeClientName'] ?? false;
        $logoMergeCells = $config['logoMergeCells'] ?? 'A1:A3';
        $logoTotalRowHeight = $config['logoTotalRowHeight'] ?? 60;

        // Add logo image
        if ($logoPath && file_exists($logoPath)) {
            // Set column width for A before adding logo
            $sheet->getColumnDimension('A')->setWidth(11.5);

            $sheet->mergeCells($logoMergeCells);
            for ($i = 1; $i <= $headerRows; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(20);
            }

            $sheet->getStyle($logoMergeCells)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($logoMergeCells)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $columnWidthPixels = 11.5 * 7;
            $imageHeight = 70;
            $imageWidth = $imageHeight;
            $offsetX = ($columnWidthPixels - $imageWidth) / 2;
            $offsetY = ($logoTotalRowHeight - $imageHeight) / 2;

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
        } else {
            $sheet->getColumnDimension('A')->setWidth(11.5);
        }

        // Calculate sum of total and round up to next whole number
        $sumOfTotal = ceil($form->fields->sum('product') ?? 0);

        // Header Section
        $row = 1;
        if ($includeClientName) {
            $sheet->setCellValue('B' . $row, 'CLIENT NAME');
            $sheet->setCellValue('C' . $row, ucwords(strtolower($form->client_name)));
            $row++;
        }

        $sheet->setCellValue('B' . $row, 'PROJECT NAME');
        $sheet->setCellValue('C' . $row, ucwords(strtolower($form->project_name)));
        $row++;

        $sheet->setCellValue('B' . $row, 'ITEM');
        $sheet->setCellValue('C' . $row, ucwords(strtolower($form->item_name ?? '')));
        $row++;

        $tqtyRow = $row;
        $sheet->setCellValue('B' . $tqtyRow, 'T.QTY');
        $sheet->setCellValue('C' . $tqtyRow, (int)$sumOfTotal);
        $sheet->setCellValue('D' . $tqtyRow, $form->unit ?? 'CFT');
        // Format T.QTY as integer (no decimals)
        $sheet->getStyle('C' . $tqtyRow)->getNumberFormat()->setFormatCode('0');

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

        $startRow = $includeClientName ? 1 : 1;
        for ($i = $startRow; $i <= $headerRows; $i++) {
            $sheet->getStyle('B' . $i)->applyFromArray($headerLabelStyle);
            $sheet->getStyle('C' . $i)->applyFromArray($headerValueStyle);
        }
        // Unit - simple (not bold)
        $sheet->getStyle('D' . $tqtyRow)->applyFromArray($headerValueStyle);

        // Add spacing row
        $spacingRow = $headerRows + 1;
        $sheet->setCellValue('A' . $spacingRow, '');
        $sheet->getRowDimension($spacingRow)->setRowHeight(5);

        // Fields Table Header
        $tableHeaderRow = $spacingRow + 1;
        $sheet->setCellValue('A' . $tableHeaderRow, 'S. NO');
        $sheet->setCellValue('B' . $tableHeaderRow, 'DESCRIPTION');
        $sheet->setCellValue('C' . $tableHeaderRow, 'No');
        $sheet->setCellValue('D' . $tableHeaderRow, 'L');
        $sheet->setCellValue('E' . $tableHeaderRow, 'W');
        $sheet->setCellValue('F' . $tableHeaderRow, 'H');
        $sheet->setCellValue('G' . $tableHeaderRow, 'T  Qty');

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
        $sheet->getStyle('A' . $tableHeaderRow . ':G' . $tableHeaderRow)->applyFromArray($tableHeaderStyle);
        $sheet->getRowDimension($tableHeaderRow)->setRowHeight(25);
        $sheet->getStyle('B' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Fields Data
        $dataRow = $tableHeaderRow + 1;
        foreach ($form->fields as $fieldIndex => $field) {
            $sheet->setCellValue('A' . $dataRow, $fieldIndex + 1);
            $sheet->setCellValue('B' . $dataRow, $field->description ?? '');
            $sheet->setCellValue('C' . $dataRow, $field->quantity ?? '');
            $sheet->setCellValue('D' . $dataRow, $field->length ?? '');
            $sheet->setCellValue('E' . $dataRow, $field->width ?? '');
            $sheet->setCellValue('F' . $dataRow, $field->height ?? '');
            $sheet->setCellValue('G' . $dataRow, $field->product ?? '');

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
            $sheet->getStyle('A' . $dataRow . ':G' . $dataRow)->applyFromArray($dataStyle);
            $sheet->getStyle('B' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $dataRow++;
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

        $lastRow = $dataRow - 1;
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

    /**
     * Find logo path.
     */
    public function findLogoPath(): ?string
    {
        $possiblePaths = [
            public_path('images/monogram.jpeg'),
            base_path('../images/monogram.jpeg'),
            base_path('../../images/monogram.jpeg'),
            '/images/monogram.jpeg',
            public_path('../images/monogram.jpeg'),
            public_path('images/logo.png'),
            public_path('images/logo.jpg'),
            public_path('logo.png'),
            public_path('logo.jpg'),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Generate summary sheet for Excel export.
     */
    public function generateSummarySheet($sheet, string $projectName, $forms, ?string $logoPath): void
    {
        $headerRows = 3;
        $logoMergeCells = 'A1:A3';
        $logoTotalRowHeight = 60;

        // Add logo image
        if ($logoPath && file_exists($logoPath)) {
            // Set column width for A before adding logo
            $sheet->getColumnDimension('A')->setWidth(11.5);

            $sheet->mergeCells($logoMergeCells);
            for ($i = 1; $i <= $headerRows; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(20);
            }

            $sheet->getStyle($logoMergeCells)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($logoMergeCells)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $columnWidthPixels = 11.5 * 7;
            $imageHeight = 70;
            $imageWidth = $imageHeight;
            $offsetX = ($columnWidthPixels - $imageWidth) / 2;
            $offsetY = ($logoTotalRowHeight - $imageHeight) / 2;

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
        } else {
            $sheet->getColumnDimension('A')->setWidth(11.5);
        }

        // Header Section
        $row = 1;
        $sheet->setCellValue('B' . $row, 'PROJECT NAME');
        $sheet->setCellValue('C' . $row, ucwords(strtolower($projectName)));
        $row++;

        $sheet->setCellValue('B' . $row, 'ITEM');
        $sheet->setCellValue('C' . $row, 'Project Summary');
        $row++;

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
        $sheet->getStyle('C1')->applyFromArray($headerValueStyle);
        $sheet->getStyle('B2')->applyFromArray($headerLabelStyle);
        $sheet->getStyle('C2')->applyFromArray($headerValueStyle);

        // Add spacing row
        $spacingRow = $headerRows + 1;
        $sheet->setCellValue('A' . $spacingRow, '');
        $sheet->getRowDimension($spacingRow)->setRowHeight(5);

        // Summary Table Header
        $tableHeaderRow = $spacingRow + 1;
        $sheet->setCellValue('A' . $tableHeaderRow, 'S. NO');
        $sheet->setCellValue('B' . $tableHeaderRow, 'DESCRIPTION');
        $sheet->setCellValue('C' . $tableHeaderRow, 'UNIT');
        $sheet->setCellValue('D' . $tableHeaderRow, 'TOTAL QTY');
        $sheet->setCellValue('E' . $tableHeaderRow, 'RATE');
        $sheet->setCellValue('F' . $tableHeaderRow, 'AMOUNT');

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
        $sheet->getStyle('A' . $tableHeaderRow . ':F' . $tableHeaderRow)->applyFromArray($tableHeaderStyle);
        $sheet->getRowDimension($tableHeaderRow)->setRowHeight(25);
        $sheet->getStyle('B' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Summary Data
        $dataRow = $tableHeaderRow + 1;
        foreach ($forms as $index => $form) {
            // Calculate total QTY (sum of product column, rounded up)
            $totalQty = ceil($form->fields->sum('product') ?? 0);

            $sheet->setCellValue('A' . $dataRow, $index + 1);
            $sheet->setCellValue('B' . $dataRow, ucwords(strtolower($form->item_name ?? '')));
            $sheet->setCellValue('C' . $dataRow, $form->unit ?? 'CFT'); // UNIT from form
            $sheet->setCellValue('D' . $dataRow, (int)$totalQty);
            $sheet->setCellValue('E' . $dataRow, ''); // RATE - blank
            $sheet->setCellValue('F' . $dataRow, ''); // AMOUNT - blank

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
            $sheet->getStyle('A' . $dataRow . ':F' . $dataRow)->applyFromArray($dataStyle);
            $sheet->getStyle('B' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $dataRow)->getNumberFormat()->setFormatCode('0'); // Format as integer

            $dataRow++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(11.5);
        $sheet->getColumnDimension('B')->setWidth(31);
        $sheet->getColumnDimension('C')->setWidth(11.5);
        $sheet->getColumnDimension('D')->setWidth(11.5);
        $sheet->getColumnDimension('E')->setWidth(11.5);
        $sheet->getColumnDimension('F')->setWidth(11.5);

        // Set page setup
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        $lastRow = $dataRow - 1;
        $sheet->getPageSetup()->setPrintArea('A1:F' . $lastRow);

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

    /**
     * Export multiple forms to Excel with multiple sheets.
     */
    public function exportByProject(string $clientName, string $projectName): StreamedResponse
    {
        // Get all forms with matching client_name and project_name where item_name is not null
        $forms = Form::where('client_name', $clientName)
            ->where('project_name', $projectName)
            ->whereNotNull('item_name')
            ->with('fields')
            ->orderBy('item_name', 'asc')
            ->get();

        if ($forms->isEmpty()) {
            throw new \Exception('No forms found with the specified client name and project name. Please ensure there are forms with item names for this client and project.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $logoPath = $this->findLogoPath();

        // Create Summary sheet first
        $summarySheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Summary');
        $spreadsheet->addSheet($summarySheet, 0);
        $summarySheet->setTitle('Summary');
        $this->generateSummarySheet($summarySheet, $projectName, $forms, $logoPath);

        // Create a sheet for each form
        foreach ($forms as $index => $form) {
            // Sanitize sheet title - Excel doesn't allow: : \ / ? * [ ]
            $rawTitle = $form->item_name ?? 'Sheet' . ($index + 1);
            $sanitizedTitle = preg_replace('/[:\/\\?*\[\]]/', '-', $rawTitle);
            $sheetTitle = substr($sanitizedTitle, 0, 31);

            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetTitle);
            $spreadsheet->addSheet($sheet, $index + 1); // Start from index 1 since Summary is at 0
            $sheet->setTitle($sheetTitle);

            // Generate the form sheet with configuration for multi-item export
            $this->generateFormSheet($sheet, $form, $logoPath, [
                'headerRows' => 3,
                'includeClientName' => false,
                'logoMergeCells' => 'A1:A3',
                'logoTotalRowHeight' => 60,
            ]);
        }

        // Set active sheet to Summary (first one)
        $spreadsheet->setActiveSheetIndex(0);

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Generate filename
        $filename = str_replace(' ', '_', $clientName) . '_' . str_replace(' ', '_', $projectName) . '.xlsx';

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

    /**
     * Export a single form to Excel.
     */
    public function exportForm(Form $form): StreamedResponse
    {
        $form->load('fields');

        if ($form->fields->isEmpty()) {
            throw new \Exception('This form has no fields to export.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $logoPath = $this->findLogoPath();

        // Sanitize sheet title - Excel doesn't allow: : \ / ? * [ ]
        $rawTitle = $form->project_name . ' - ' . $form->item_name ?? 'Form';
        $sanitizedTitle = preg_replace('/[:\/\\?*\[\]]/', '-', $rawTitle);
        $sheetTitle = substr($sanitizedTitle, 0, 31);

        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetTitle);
        $spreadsheet->addSheet($sheet, 0);
        $sheet->setTitle($sheetTitle);

        // Generate the form sheet with configuration for single item export
        $this->generateFormSheet($sheet, $form, $logoPath, [
            'headerRows' => 3,
            'includeClientName' => false,
            'logoMergeCells' => 'A1:A3',
            'logoTotalRowHeight' => 60,
        ]);

        // Set active sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Generate filename
        $clientName = str_replace(' ', '_', $form->client_name);
        $projectName = str_replace(' ', '_', $form->project_name);
        $itemName = $form->item_name ? str_replace(' ', '_', $form->item_name) : 'Form';
        $filename = $clientName . '_' . $projectName . '_' . $itemName . '.xlsx';

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
