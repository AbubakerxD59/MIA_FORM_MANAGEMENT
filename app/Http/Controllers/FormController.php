<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Field;
use App\Models\BarBendingFormItem;
use App\Models\BarBendingLocation;
use App\Models\BarBendingFormLocation;
use App\Models\Formula;
use App\Models\CdHead;
use App\Models\CdLedger;
use App\Models\CdSummary;
use App\Services\FormService;
use App\Services\BBSService;
use App\Services\FormulaService;
use App\Http\Requests\StoreFormRequest;
use App\Http\Requests\UpdateFormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class FormController extends Controller
{
    protected FormService $formService;
    protected BBSService $bbsService;
    protected FormulaService $formulaService;

    public function __construct(FormService $formService, BBSService $bbsService, FormulaService $formulaService)
    {
        $this->formService = $formService;
        $this->bbsService = $bbsService;
        $this->formulaService = $formulaService;
    }
    /**
     * Display a listing of the forms.
     */
    public function index(Request $request): View|JsonResponse
    {
        // Check if this is a DataTables request
        if ($request->ajax() || $request->wantsJson()) {
            $data = $this->formService->getFormsForDataTable($request);
            return response()->json($data);
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
        $suggestions = $this->formService->getClientNames($query);
        return response()->json($suggestions);
    }

    /**
     * Get unique group_by values for autocomplete.
     */
    public function getGroupByValues(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $suggestions = $this->formService->getGroupByValues($query);
        return response()->json($suggestions);
    }

    /**
     * Get form fields for AJAX request.
     */
    public function getFormFields(Form $form): JsonResponse
    {
        $data = $this->formService->getFormFieldsData($form);
        return response()->json($data);
    }

    /**
     * Get sidebar items for AJAX request.
     */
    public function getSidebarItems(Request $request): JsonResponse
    {
        $client_name = $request->get('client_name');
        $project_name = $request->get('project_name');
        $items = $this->formService->getSidebarItems($client_name, $project_name);
        return response()->json($items);
    }

    /**
     * Get bar bending form items for a form.
     */
    public function getBarBendingFormItems(Form $form): JsonResponse
    {
        $items = $this->bbsService->getBarBendingFormItems($form->id);
        return response()->json($items);
    }

    /**
     * Get a single bar bending form item with location.
     */
    public function getBarBendingFormItem(BarBendingFormItem $item): JsonResponse
    {
        $item = $this->bbsService->getBarBendingFormItem($item->id);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'form_id' => $item->form_id,
            'location_id' => $item->location_id,
            'location' => $item->location ? [
                'id' => $item->location->id,
                'name' => $item->location->name,
            ] : null,
        ]);
    }

    /**
     * Store a new bar bending form item.
     */
    public function storeBarBendingFormItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'name' => 'required|string|max:255',
        ]);

        $item = $this->bbsService->storeBarBendingFormItem(
            $validated['form_id'],
            $validated['name']
        );

        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'form_id' => $item->form_id,
            'created_at' => $item->created_at->toISOString(),
            'message' => 'Bar bending form item created successfully.'
        ], 201);
    }

    /**
     * Update or create a bar bending form item's name.
     */
    public function updateBarBendingFormItemName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'name' => 'required|string|max:255',
            'item_id' => 'nullable|exists:bar_bending_form_items,id',
        ]);

        try {
            if (!empty($validated['item_id'])) {
                // Update existing item by ID
                $item = BarBendingFormItem::findOrFail($validated['item_id']);
                $item = $this->bbsService->updateBarBendingFormItemName($item, $validated['name']);
            } else {
                // Check if an item with this name already exists for the form
                $existingItem = $this->bbsService->findBarBendingFormItemByName(
                    $validated['form_id'],
                    $validated['name']
                );

                if ($existingItem) {
                    // Item already exists, update it (this ensures timestamps are updated)
                    $item = $this->bbsService->updateBarBendingFormItemName($existingItem, $validated['name']);
                } else {
                    // Create new item
                    $item = $this->bbsService->storeBarBendingFormItem($validated['form_id'], $validated['name']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Item name saved successfully.',
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'form_id' => $item->form_id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to save item name: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a bar bending form item.
     */
    public function deleteBarBendingFormItem(BarBendingFormItem $item): JsonResponse
    {
        $this->bbsService->deleteBarBendingFormItem($item);
        return response()->json(['message' => 'Bar bending form item deleted successfully.']);
    }

    /**
     * Get locations for autocomplete.
     */
    public function getLocations(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $locations = BarBendingLocation::where('name', 'like', "%{$query}%")
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($locations);
    }

    /**
     * Add location to bar bending form location.
     */
    public function addLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'item_id' => 'required|exists:bar_bending_form_items,id',
            'location_name' => 'required|string|max:255',
        ]);

        try {
            // Find or create location in bar_bending_locations
            $location = BarBendingLocation::firstOrCreate(
                ['name' => $validated['location_name']],
                ['name' => $validated['location_name']]
            );

            // Check if location already exists for this form_id and item_id pair
            $existingFormLocation = BarBendingFormLocation::where('form_id', $validated['form_id'])
                ->where('item_id', $validated['item_id'])
                ->where('location_id', $location->id)
                ->first();

            if ($existingFormLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location already added to the active item',
                ], 400);
            }

            // Create new bar_bending_form_location record
            $formLocation = BarBendingFormLocation::create([
                'form_id' => $validated['form_id'],
                'item_id' => $validated['item_id'],
                'location_id' => $location->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location added successfully.',
                'location' => [
                    'id' => $location->id,
                    'name' => $location->name,
                ],
                'form_location' => [
                    'id' => $formLocation->id,
                    'form_id' => $formLocation->form_id,
                    'item_id' => $formLocation->item_id,
                    'location_id' => $formLocation->location_id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to add location: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a bar bending form location.
     */
    public function deleteLocation(BarBendingFormLocation $location): JsonResponse
    {
        try {
            $location->delete();
            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all formulas grouped by location_name.
     */
    public function getFormulas(): JsonResponse
    {
        try {
            $formulas = $this->formulaService->getFormulasGroupedByLocation();
            return response()->json($formulas);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch formulas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new formula.
     */
    public function storeFormula(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'location_name' => 'required|string|max:255',
                'formula' => 'required|string',
            ]);

            $formula = $this->formulaService->storeFormula(
                $validated['location_name'],
                $validated['formula']
            );

            return response()->json([
                'success' => true,
                'message' => 'Formula created successfully.',
                'formula' => [
                    'id' => $formula->id,
                    'location_name' => $formula->location_name,
                    'formula' => $formula->formula,
                    'created_at' => $formula->created_at,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create formula: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing formula.
     */
    public function updateFormula(Request $request, Formula $formula): JsonResponse
    {
        try {
            $validated = $request->validate([
                'location_name' => 'required|string|max:255',
                'formula' => 'required|string',
            ]);

            $formula = $this->formulaService->updateFormula(
                $formula,
                $validated['location_name'],
                $validated['formula']
            );

            return response()->json([
                'success' => true,
                'message' => 'Formula updated successfully.',
                'formula' => [
                    'id' => $formula->id,
                    'location_name' => $formula->location_name,
                    'formula' => $formula->formula,
                    'created_at' => $formula->created_at,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update formula: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a formula.
     */
    public function deleteFormula(Formula $formula): JsonResponse
    {
        try {
            $this->formulaService->deleteFormula($formula);
            return response()->json([
                'success' => true,
                'message' => 'Formula deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete formula: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created form in storage.
     */
    public function store(StoreFormRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $form = $this->formService->storeForm($request->validated());

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

        try {
            $data = $this->formService->prepareFormForEdit($client_name, $project_name);
            return view('forms.edit', $data);
        } catch (\Exception $e) {
            return redirect()->route('forms.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the BBS (Bar Bending Schedule) view for the specified form.
     */
    public function bbs(Form $form): View
    {
        $data = $this->bbsService->prepareFormForBBS($form);
        return view('forms.bbs', $data);
    }

    /**
     * Show the Credit/Debit view for the specified form.
     */
    public function cd(Form $form): View
    {
        $cdHeads = CdHead::where('form_id', $form->id)
            ->orderBy('name', 'asc')
            ->get();

        $cdLedger = CdLedger::where('form_id', $form->id)->first();
        $baseIncome = $cdLedger ? $cdLedger->income : 0;

        // Load existing summaries with head relationship
        $cdSummaries = CdSummary::where('form_id', $form->id)
            ->with('cdHead')
            ->orderBy('id', 'asc')
            ->get();

        // Group summaries by created_at (within 2 seconds) and head_id to combine debit/credit pairs
        $groupedSummaries = [];
        $processedIds = [];

        foreach ($cdSummaries as $summary) {
            // Skip if already processed
            if (in_array($summary->id, $processedIds)) {
                continue;
            }

            $group = [
                'head_id' => $summary->head_id,
                'head_name' => $summary->cdHead->name ?? '',
                'debit' => 0,
                'credit' => 0,
                'description' => $summary->description ?? '',
                'dated' => $summary->dated?->format('Y-m-d'),
                'created_at' => $summary->created_at,
            ];

            // Set the current summary's value
            if ($summary->cd_type === 'debit') {
                $group['debit'] = $summary->amount;
            } else {
                $group['credit'] = $summary->amount;
            }

            $processedIds[] = $summary->id;

            // Look for matching entries (same head, within 2 seconds, opposite type)
            foreach ($cdSummaries as $otherSummary) {
                if (in_array($otherSummary->id, $processedIds)) {
                    continue;
                }

                $timeDiff = abs($summary->created_at->diffInSeconds($otherSummary->created_at));
                if (
                    $otherSummary->head_id === $summary->head_id &&
                    $timeDiff <= 2 &&
                    $otherSummary->cd_type !== $summary->cd_type
                ) {
                    // Found matching entry
                    if ($otherSummary->cd_type === 'debit') {
                        $group['debit'] = $otherSummary->amount;
                    } else {
                        $group['credit'] = $otherSummary->amount;
                    }
                    // Use description from either entry (prefer non-empty one)
                    if (empty($group['description']) && !empty($otherSummary->description)) {
                        $group['description'] = $otherSummary->description;
                    }
                    $processedIds[] = $otherSummary->id;
                    break; // Only combine one pair
                }
            }

            // Add group if it has at least one value
            if ($group['debit'] > 0 || $group['credit'] > 0) {
                $groupedSummaries[] = $group;
            }
        }

        // Calculate total credit (sum of all credits) and add to base income for 
        $totalIncome = $baseIncome;

        return view('forms.cd', compact('form', 'cdHeads', 'totalIncome', 'groupedSummaries', 'baseIncome'));
    }

    /**
     * Export CD summary to Excel.
     */
    public function exportCd(Form $form): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $cdLedger = CdLedger::where('form_id', $form->id)->first();
            $totalIncome = $cdLedger ? $cdLedger->income : 0;

            // Load existing summaries with head relationship
            $cdSummaries = CdSummary::where('form_id', $form->id)
                ->with('cdHead')
                ->orderBy('id', 'asc')
                ->get();

            // Group summaries by created_at (within 2 seconds) and head_id to combine debit/credit pairs
            $groupedSummaries = [];
            $processedIds = [];

            foreach ($cdSummaries as $summary) {
                // Skip if already processed
                if (in_array($summary->id, $processedIds)) {
                    continue;
                }

                $group = [
                    'head_id' => $summary->head_id,
                    'head_name' => $summary->cdHead->name ?? '',
                    'debit' => 0,
                    'credit' => 0,
                    'description' => $summary->description ?? '',
                    'dated' => $summary->dated?->format('Y-m-d'),
                    'created_at' => $summary->created_at,
                ];

                // Set the current summary's value
                if ($summary->cd_type === 'debit') {
                    $group['debit'] = $summary->amount;
                } else {
                    $group['credit'] = $summary->amount;
                }

                $processedIds[] = $summary->id;

                // Look for matching entries (same head, within 2 seconds, opposite type)
                foreach ($cdSummaries as $otherSummary) {
                    if (in_array($otherSummary->id, $processedIds)) {
                        continue;
                    }

                    $timeDiff = abs($summary->created_at->diffInSeconds($otherSummary->created_at));
                    if (
                        $otherSummary->head_id === $summary->head_id &&
                        $timeDiff <= 2 &&
                        $otherSummary->cd_type !== $summary->cd_type
                    ) {
                        // Found matching entry
                        if ($otherSummary->cd_type === 'debit') {
                            $group['debit'] = $otherSummary->amount;
                        } else {
                            $group['credit'] = $otherSummary->amount;
                        }
                        // Use description from either entry (prefer non-empty one)
                        if (empty($group['description']) && !empty($otherSummary->description)) {
                            $group['description'] = $otherSummary->description;
                        }
                        $processedIds[] = $otherSummary->id;
                        break; // Only combine one pair
                    }
                }

                // Add group if it has at least one value
                if ($group['debit'] > 0 || $group['credit'] > 0) {
                    $groupedSummaries[] = $group;
                }
            }

            // Calculate total expense (sum of all debits)
            $totalExpense = array_sum(array_column($groupedSummaries, 'debit'));
            $inHand = $totalIncome - $totalExpense;

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();

            // Remove default sheet
            $spreadsheet->removeSheetByIndex(0);

            // Create Summary sheet
            $summarySheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Summary');
            $spreadsheet->addSheet($summarySheet, 0);

            // Create Roznamcha sheet
            $roznamchaSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Roznamcha');
            $spreadsheet->addSheet($roznamchaSheet, 1);

            // Set active sheet to Summary
            $spreadsheet->setActiveSheetIndex(0);

            // Add header to Summary sheet
            $this->addCdExportHeader($summarySheet, $form, $totalIncome, $totalExpense, $inHand);

            // Add Summary Section to Summary sheet
            $this->addSummarySection($summarySheet, $form, $cdSummaries);

            // Add header to Roznamcha sheet
            $this->addCdExportHeader($roznamchaSheet, $form, $totalIncome, $totalExpense, $inHand);

            // Add table to Roznamcha sheet
            $this->addRoznamchaTable($roznamchaSheet, $groupedSummaries);

            // Get all heads that have debit summaries
            $headAmounts = [];
            foreach ($cdSummaries as $summary) {
                // Only include debit type
                if ($summary->cd_type === 'debit') {
                    $headId = $summary->head_id;
                    if (!isset($headAmounts[$headId])) {
                        $headAmounts[$headId] = 0;
                    }
                    $headAmounts[$headId] += $summary->amount;
                }
            }

            // Get all CD heads
            $cdHeads = CdHead::where('form_id', $form->id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Create a sheet for each head that has debit summaries
            $sheetIndex = 2; // Start after Summary (0) and Roznamcha (1)
            foreach ($cdHeads as $head) {
                // Skip heads with no debit amount
                $headAmount = isset($headAmounts[$head->id]) ? $headAmounts[$head->id] : 0;
                if ($headAmount == 0) {
                    continue;
                }

                // Filter grouped summaries for this head only
                $headGroupedSummaries = array_filter($groupedSummaries, function ($summary) use ($head) {
                    return isset($summary['head_id']) && $summary['head_id'] == $head->id;
                });
                // Re-index array to start from 0
                $headGroupedSummaries = array_values($headGroupedSummaries);

                // Sanitize head name for sheet name (Excel sheet names have limitations)
                $sheetName = $this->sanitizeSheetName($head->name);

                // Create sheet for this head
                $headSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetName);
                $spreadsheet->addSheet($headSheet, $sheetIndex);

                // Add header to head sheet
                $this->addCdExportHeader($headSheet, $form, $totalIncome, $totalExpense, $inHand);

                // Add table to head sheet (filtered for this head)
                $this->addRoznamchaTable($headSheet, $headGroupedSummaries);

                $sheetIndex++;
            }

            // Set footer on all sheets
            $footerText = excel_footer_text();
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheet->getHeaderFooter()->setOddFooter($footerText);
                $sheet->getHeaderFooter()->setEvenFooter($footerText);
            }

            // Create writer
            $writer = new Xlsx($spreadsheet);

            // Generate filename
            $clientName = str_replace(' ', '_', $form->client_name);
            $projectName = str_replace(' ', '_', $form->project_name);
            $filename = $clientName . '_' . $projectName . '_CD_Summary_' . date('Y-m-d') . '.xlsx';

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
        } catch (\Exception $e) {
            return redirect()->route('forms.cd', $form->id)
                ->with('error', 'Failed to export CD summary: ' . $e->getMessage());
        }
    }

    /**
     * Add header section to CD export sheet.
     */
    private function addCdExportHeader($sheet, $form, $totalIncome, $totalExpense, $inHand): int
    {
        // Find logo path
        $logoPath = $this->formService->findLogoPath();

        // Add logo if available
        $row = 1;
        if ($logoPath && file_exists($logoPath)) {
            $sheet->getColumnDimension('A')->setWidth(11.5);
            $sheet->mergeCells('A1:A3');
            $sheet->getRowDimension(1)->setRowHeight(20);
            $sheet->getRowDimension(2)->setRowHeight(20);
            $sheet->getRowDimension(3)->setRowHeight(20);

            $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $columnWidthPixels = 11.5 * 7;
            $imageHeight = 70;
            $imageWidth = $imageHeight;
            $offsetX = ($columnWidthPixels - $imageWidth) / 2;
            $offsetY = (60 - $imageHeight) / 2;

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
        // Row 1: T. INCOME (left) and CLIENT (right)
        $sheet->setCellValue('B' . $row, 'CLIENT');
        $sheet->setCellValue('C' . $row, strtoupper($form->client_name));
        $sheet->setCellValue('F' . $row, 'T. INCOME');
        $sheet->setCellValue('G' . $row, number_format($totalIncome, 0, '.', ','));
        $row++;

        // Row 2: T.EXPENSE (left) and LOCATION (right)
        $sheet->setCellValue('B' . $row, 'LOCATION');
        $sheet->setCellValue('C' . $row, strtoupper($form->project_name));
        $sheet->setCellValue('F' . $row, 'T.EXPENSE');
        $sheet->setCellValue('G' . $row, number_format($totalExpense, 0, '.', ','));
        $row++;

        // Row 3: INHAND (left) and STARTING (right)
        $sheet->setCellValue('B' . $row, 'STARTING');
        $startingDate = $form->created_at ? $form->created_at->format('d.m.Y') : date('d.m.Y');
        $sheet->setCellValue('C' . $row, $startingDate);
        $sheet->setCellValue('F' . $row, 'INHAND');
        $sheet->setCellValue('G' . $row, number_format($inHand, 0, '.', ','));

        // Style header rows
        $headerLabelStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $headerValueStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $financialLabelStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $financialValueStyle = [
            'font' => ['bold' => false, 'size' => 12, 'color' => ['rgb' => '000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        // Style left side financial summary (T. INCOME, T.EXPENSE, INHAND)
        $sheet->getStyle('B1:B3')->applyFromArray($financialLabelStyle);
        $sheet->getStyle('C1:C3')->applyFromArray($financialValueStyle);

        // Style right side header (CLIENT, LOCATION, STARTING)
        $sheet->getStyle('F1:F3')->applyFromArray($headerLabelStyle);
        $sheet->getStyle('G1:G3')->applyFromArray($headerValueStyle);

        // Set column widths
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(5);
        $sheet->getColumnDimension('E')->setWidth(5);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);

        // Add spacing row
        $row = 4;
        $sheet->setCellValue('A' . $row, '');
        $sheet->getRowDimension($row)->setRowHeight(5);

        return $row;
    }

    /**
     * Add Summary Section to Summary sheet.
     */
    private function addSummarySection($sheet, $form, $cdSummaries): void
    {
        $row = 5; // After header (3 rows) and spacing (row 4)

        // Get all CD heads
        $cdHeads = CdHead::where('form_id', $form->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate sum of debit amounts for each head from summaries
        $headAmounts = [];
        $totalDebitAmount = 0;
        foreach ($cdSummaries as $summary) {
            // Only include debit type
            if ($summary->cd_type === 'debit') {
                $headId = $summary->head_id;
                if (!isset($headAmounts[$headId])) {
                    $headAmounts[$headId] = 0;
                }
                $headAmounts[$headId] += $summary->amount;
                $totalDebitAmount += $summary->amount;
            }
        }

        // Add table header row (starting directly at row 5, no blank row above)
        $tableHeaderRow = $row;
        $sheet->setCellValue('A' . $tableHeaderRow, 'S. No');
        $sheet->setCellValue('B' . $tableHeaderRow, 'Account');
        $sheet->setCellValue('G' . $tableHeaderRow, 'Total');

        // Style table header (same as Roznamcha)
        $tableHeaderStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A' . $tableHeaderRow . ':G' . $tableHeaderRow)->applyFromArray($tableHeaderStyle);

        // Right align column G (Total) in header
        $sheet->getStyle('G' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Merge columns C to F in header row
        $sheet->mergeCells('C' . $tableHeaderRow . ':F' . $tableHeaderRow);

        // Add summary data rows - only for heads with debit amounts
        $currentRow = $tableHeaderRow + 1;
        $serialNumber = 1;
        foreach ($cdHeads as $head) {
            // Skip heads with no debit amount
            $headAmount = isset($headAmounts[$head->id]) ? $headAmounts[$head->id] : 0;
            if ($headAmount == 0) {
                continue;
            }

            // Serial number in column A
            $sheet->setCellValue('A' . $currentRow, $serialNumber);

            // Head name in column B
            $sheet->setCellValue('B' . $currentRow, $head->name);

            // Sum of debit amount for this head in column G
            $sheet->setCellValue('G' . $currentRow, number_format($headAmount, 0, '.', ','));

            // Style table row (same as Roznamcha)
            $tableRowStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
            $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray($tableRowStyle);

            // Center align S. No column
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Right align column G (amount)
            $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Merge columns C to F in data row
            $sheet->mergeCells('C' . $currentRow . ':F' . $currentRow);

            // Set row height for table rows
            $sheet->getRowDimension($currentRow)->setRowHeight(25);

            $serialNumber++;
            $currentRow++;
        }

        // Add total row after all heads
        $sheet->setCellValue('A' . $currentRow, '');
        $sheet->setCellValue('B' . $currentRow, 'Total');
        $sheet->setCellValue('G' . $currentRow, number_format($totalDebitAmount, 0, '.', ','));

        // Style total row (same as table rows with borders)
        $totalRowStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray($totalRowStyle);

        // Right align column G (total amount)
        $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Merge columns C to F in total row
        $sheet->mergeCells('C' . $currentRow . ':F' . $currentRow);

        // Set row height for total row
        $sheet->getRowDimension($currentRow)->setRowHeight(25);
    }

    /**
     * Sanitize sheet name for Excel compatibility.
     * Excel sheet names have limitations: max 31 characters, cannot contain: \ / ? * [ ]
     */
    private function sanitizeSheetName($name): string
    {
        // Remove invalid characters
        $name = preg_replace('/[\\\\\/\?\*\[\]]/', '', $name);

        // Truncate to 31 characters (Excel limit)
        if (mb_strlen($name) > 31) {
            $name = mb_substr($name, 0, 31);
        }

        // If empty after sanitization, use default name
        if (empty($name)) {
            $name = 'Sheet';
        }

        return $name;
    }

    /**
     * Add Roznamcha table to Roznamcha sheet.
     */
    private function addRoznamchaTable($sheet, $groupedSummaries): void
    {
        $row = 5; // After header (3 rows) and spacing (row 4)

        // Table Header
        $tableHeaderRow = $row;
        $sheet->setCellValue('A' . $tableHeaderRow, 'S. No');
        $sheet->setCellValue('B' . $tableHeaderRow, 'Account');
        $sheet->setCellValue('C' . $tableHeaderRow, 'DATED');
        $sheet->setCellValue('D' . $tableHeaderRow, 'DESCRIPTION');
        $sheet->setCellValue('E' . $tableHeaderRow, 'DEB');
        $sheet->setCellValue('F' . $tableHeaderRow, 'CRD');
        $sheet->setCellValue('G' . $tableHeaderRow, 'TOTAL');

        // Style table header
        $tableHeaderStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A' . $tableHeaderRow . ':G' . $tableHeaderRow)->applyFromArray($tableHeaderStyle);

        // Set column widths for table
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);

        // Table Data
        $currentRow = $tableHeaderRow + 1;
        $runningTotal = 0;
        $rowIndex = 0;
        $serialNumber = 1;

        foreach ($groupedSummaries as $summary) {
            $sheet->setCellValue('A' . $currentRow, $serialNumber);
            $sheet->setCellValue('B' . $currentRow, $summary['head_name']);
            $sheet->setCellValue('C' . $currentRow, $summary['created_at']->format('Y-m-d'));
            $sheet->setCellValue('D' . $currentRow, $summary['description']);
            $sheet->setCellValue('E' . $currentRow, $summary['debit'] > 0 ? number_format($summary['debit'], 0, '.', ',') : '');
            $sheet->setCellValue('F' . $currentRow, $summary['credit'] > 0 ? number_format($summary['credit'], 0, '.', ',') : '');

            // Calculate running total using the same logic as JavaScript
            $debit = $summary['debit'] ?? 0;
            $credit = $summary['credit'] ?? 0;

            if ($rowIndex === 0) {
                // First row: total equals debit or credit amount
                if ($credit > 0 && $debit > 0) {
                    $runningTotal = $credit - $debit;
                } elseif ($credit > 0) {
                    $runningTotal = $credit;
                } elseif ($debit > 0) {
                    $runningTotal = -$debit;
                } else {
                    $runningTotal = 0;
                }
            } else {
                // Other rows: if previous total > 0, subtract debit and add credit
                if ($runningTotal > 0) {
                    $runningTotal = $runningTotal - $debit + $credit;
                } else {
                    // If previous total <= 0, still apply the same logic for consistency
                    $runningTotal = $runningTotal - $debit + $credit;
                }
            }

            $sheet->setCellValue('G' . $currentRow, number_format($runningTotal, 0, '.', ','));

            // Style table row
            $tableRowStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
            $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray($tableRowStyle);

            // Center align S. No column
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Right align numeric columns
            $sheet->getStyle('E' . $currentRow . ':G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Set row height for table rows
            $sheet->getRowDimension($currentRow)->setRowHeight(25);

            $currentRow++;
            $rowIndex++;
            $serialNumber++;
        }
    }

    /**
     * Store a new CD head.
     */
    public function storeCdHead(Request $request, Form $form): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $cdHead = CdHead::create([
                'user_id' => Auth::id(),
                'form_id' => $form->id,
                'name' => $validated['name'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Head created successfully.',
                'head' => [
                    'id' => $cdHead->id,
                    'name' => $cdHead->name,
                    'created_at' => $cdHead->created_at->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create head: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a CD head.
     */
    public function getCdHead(CdHead $head): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'head' => [
                    'id' => $head->id,
                    'name' => $head->name,
                    'created_at' => $head->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get head: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a CD head.
     */
    public function updateCdHead(Request $request, CdHead $head): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $head->update([
                'name' => $validated['name'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Head updated successfully.',
                'head' => [
                    'id' => $head->id,
                    'name' => $head->name,
                    'updated_at' => $head->updated_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update head: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get CD heads for autocomplete.
     */
    public function getCdHeadsForAutocomplete(Request $request, Form $form): JsonResponse
    {
        try {
            $query = $request->get('term', '');

            $cdHeads = CdHead::where('form_id', $form->id)
                ->where('name', 'like', '%' . $query . '%')
                ->orderBy('name', 'asc')
                ->pluck('name')
                ->toArray();

            return response()->json($cdHeads);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Store CD summary entries.
     */
    public function storeCdSummary(Request $request, Form $form): JsonResponse
    {
        $validated = $request->validate([
            'summaries' => 'required|array',
            'summaries.*.head_name' => 'required|string',
            'summaries.*.cd_type' => 'required|in:credit,debit',
            'summaries.*.amount' => 'required|numeric|min:0',
            'summaries.*.dated' => 'nullable|date',
            'summaries.*.description' => 'nullable|string',
        ]);

        try {

            $cdLedger = CdLedger::where('form_id', $form->id)->first();
            if (!$cdLedger) {
                $cdLedger = CdLedger::create([
                    'user_id' => Auth::id(),
                    'form_id' => $form->id,
                    'income' => 0,
                ]);
            }

            // Delete all existing summaries for this form
            CdSummary::where('form_id', $form->id)->delete();

            $created = [];
            $totalAmount = 0;
            foreach ($validated['summaries'] as $summary) {
                // Find or create head by name
                $head = CdHead::where('form_id', $form->id)
                    ->where('name', $summary['head_name'])
                    ->first();

                if (!$head) {
                    // Create new head if it doesn't exist
                    $head = CdHead::create([
                        'user_id' => Auth::id(),
                        'form_id' => $form->id,
                        'name' => $summary['head_name'],
                    ]);
                }

                // Create summary entry
                $cdSummary = CdSummary::create([
                    'user_id' => Auth::id(),
                    'form_id' => $form->id,
                    'head_id' => $head->id,
                    'cd_type' => $summary['cd_type'],
                    'amount' => $summary['amount'],
                    'dated' => !empty($summary['dated']) ? $summary['dated'] : null,
                    'description' => $summary['description'] ?? null,
                ]);

                $created[] = [
                    'id' => $cdSummary->id,
                    'head_name' => $summary['head_name'],
                    'cd_type' => $cdSummary->cd_type,
                    'amount' => $cdSummary->amount,
                ];
                if ($summary['cd_type'] === 'credit') {
                    $totalAmount += $summary['amount'];
                }
            }

            CdLedger::where("form_id", $form->id)->update([
                "income" => $totalAmount,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Credit/Debit summary saved successfully.',
                'summaries' => $created,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get CD heads for sidebar.
     */
    public function getCdHeads(Form $form): JsonResponse
    {
        try {
            $cdHeads = CdHead::where('form_id', $form->id)
                ->orderBy('name', 'asc')
                ->get();

            $headsData = $cdHeads->map(function ($head) {
                return [
                    'id' => $head->id,
                    'name' => $head->name,
                    'created_at' => $head->created_at->format('M d, Y'),
                ];
            });

            return response()->json($headsData);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Update the specified form in storage.
     */
    public function update(UpdateFormRequest $request, Form $form): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $form = $this->formService->updateForm($form, $request->validated());

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
     * Delete all forms with the same client_name and project_name.
     */
    public function destroyByProject(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $client_name = $request->get('client_name');
        $project_name = $request->get('project_name');

        if (!$client_name || !$project_name) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Client name and project name are required.'], 400);
            }
            return redirect()->route('forms.index')
                ->with('error', 'Client name and project name are required.');
        }

        try {
            $deletedCount = $this->formService->deleteFormsByProject($client_name, $project_name);
            $message = "All forms ({$deletedCount}) for client '{$client_name}' and project '{$project_name}' have been deleted successfully.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $message, 'deleted_count' => $deletedCount]);
            }

            return redirect()->route('forms.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to delete forms: ' . $e->getMessage()], 500);
            }

            return redirect()->route('forms.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display a listing of deleted forms.
     */
    public function deleted(Request $request): View|JsonResponse
    {
        // Check if this is a DataTables request
        if ($request->ajax() || $request->wantsJson()) {
            $data = $this->formService->getDeletedFormsForDataTable($request);
            return response()->json($data);
        }

        return view('forms.deleted');
    }

    /**
     * Restore the specified deleted form and all forms with the same client_name and project_name.
     */
    public function restore($id)
    {
        try {
            // Get the form to extract client_name and project_name
            $form = Form::onlyTrashed()->findOrFail($id);

            // Restore all forms with the same client_name and project_name
            $restoredCount = $this->formService->restoreFormsByProject(
                $form->client_name,
                $form->project_name
            );

            $message = $restoredCount === 1
                ? 'Form restored successfully.'
                : "All forms ({$restoredCount}) for client '{$form->client_name}' and project '{$form->project_name}' have been restored successfully.";

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'restored_count' => $restoredCount
                ]);
            }

            return redirect()->route('forms.deleted')
                ->with('success', $message);
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Failed to restore forms: ' . $e->getMessage()], 500);
            }

            return redirect()->route('forms.deleted')
                ->with('error', 'Failed to restore forms: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate all forms with the same client_name and project_name.
     */
    public function duplicate(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $client_name = $request->get('client_name');
        $project_name = $request->get('project_name');

        if (!$client_name || !$project_name) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Client name and project name are required.'], 400);
            }
            return redirect()->route('forms.index')
                ->with('error', 'Client name and project name are required.');
        }

        try {
            $duplicatedCount = $this->formService->duplicateFormsByProject($client_name, $project_name);
            $message = "All forms ({$duplicatedCount}) for client '{$client_name}' and project '{$project_name}' have been duplicated successfully.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $message, 'duplicated_count' => $duplicatedCount]);
            }

            return redirect()->route('forms.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to duplicate forms: ' . $e->getMessage()], 500);
            }

            return redirect()->route('forms.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Duplicate all forms with the same group_by value within a project.
     */
    public function duplicateGroup(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string',
            'project_name' => 'required|string',
            'group_by' => 'required|string',
            'new_group_by' => 'required|string',
        ]);

        try {
            $duplicatedCount = $this->formService->duplicateGroupByGroupBy(
                $validated['client_name'],
                $validated['project_name'],
                $validated['group_by'],
                $validated['new_group_by']
            );
            $message = "Group duplicated successfully. {$duplicatedCount} items have been duplicated with the new group name.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'duplicated_count' => $duplicatedCount
                ]);
            }

            return redirect()->route('forms.edit', [
                'client_name' => $validated['client_name'],
                'project_name' => $validated['project_name']
            ])->with('success', $message);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to duplicate group: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('forms.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update client name and project name for all forms with the same client_name and project_name.
     */
    public function updateDetails(Request $request): JsonResponse
    {
        $request->validate([
            'old_client_name' => 'required|string',
            'old_project_name' => 'required|string',
            'new_client_name' => 'required|string',
            'new_project_name' => 'required|string',
        ]);

        try {
            $updatedCount = $this->formService->updateFormsDetails(
                $request->get('old_client_name'),
                $request->get('old_project_name'),
                $request->get('new_client_name'),
                $request->get('new_project_name')
            );

            $message = "All forms ({$updatedCount}) have been updated successfully.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update forms: ' . $e->getMessage()
            ], 500);
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

        try {
            return $this->formService->exportByProject($client_name, $project_name);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export a single form to Excel.
     */
    public function export(Form $form): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            return $this->formService->exportForm($form);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export only items that share the same group_by value as the selected item.
     */
    public function exportByGroup(Request $request): StreamedResponse|\Illuminate\Http\RedirectResponse|JsonResponse
    {
        $client_name = $request->get('client_name');
        $project_name = $request->get('project_name');
        $group_by = $request->get('group_by');

        if (!$client_name || !$project_name || $group_by === null || $group_by === '') {
            return back()->with('error', 'Client name, project name and group by are required for group export.');
        }

        try {
            return $this->formService->exportByGroup($client_name, $project_name, $group_by);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
