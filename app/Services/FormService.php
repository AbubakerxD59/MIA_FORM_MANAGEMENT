<?php

namespace App\Services;

use App\Models\CdHead;
use App\Models\CdItem;
use App\Models\CdLedger;
use App\Models\CdSummary;
use App\Models\Form;
use App\Models\Field;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Get unique group_by values for autocomplete.
     */
    public function getGroupByValues(string $query = ''): array
    {
        $queryBuilder = Form::whereNotNull('group_by')
            ->where('group_by', '!=', '')
            ->select('group_by')
            ->distinct()
            ->orderBy('group_by', 'asc');

        if (!empty($query)) {
            $queryBuilder->where('group_by', 'like', "%{$query}%");
        }

        $groupByValues = $queryBuilder->take(20)->get();

        return $groupByValues->map(function ($form) {
            return [
                'value' => $form->group_by,
                'label' => $form->group_by
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
            'rate' => $form->rate,
            'group_by' => $form->group_by,
            'fields' => $fieldsData
        ];
    }

    /**
     * Get sidebar items for a client and project, grouped by group_by value.
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
            ->get(['id', 'item_name', 'created_at', 'client_name', 'project_name', 'group_by']);

        // Group forms by group_by value
        $grouped = $relatedForms->groupBy(function ($form) {
            return $form->group_by ?? '__ungrouped__';
        });

        $result = [];
        $ungroupedItems = [];
        $groupedItems = [];

        // Separate grouped and ungrouped items
        foreach ($grouped as $groupBy => $forms) {
            if ($groupBy === '__ungrouped__') {
                // Items without group_by - collect them separately
                foreach ($forms as $form) {
                    $ungroupedItems[] = [
                        'id' => $form->id,
                        'item_name' => $form->item_name,
                        'created_at' => $form->created_at->toISOString(),
                        'client_name' => $form->client_name,
                        'project_name' => $form->project_name,
                        'group_by' => null,
                        'is_group' => false,
                    ];
                }
            } else {
                // Items with group_by - add as group header
                $groupItems = $forms->map(function ($form) {
                    return [
                        'id' => $form->id,
                        'item_name' => $form->item_name,
                        'created_at' => $form->created_at->toISOString(),
                        'client_name' => $form->client_name,
                        'project_name' => $form->project_name,
                        'group_by' => $form->group_by,
                        'is_group' => false,
                    ];
                })->toArray();

                $groupedItems[] = [
                    'group_by' => $groupBy,
                    'is_group' => true,
                    'items' => $groupItems,
                ];
            }
        }

        // Sort grouped items by group_by value in ascending order
        usort($groupedItems, function ($a, $b) {
            return strcasecmp($a['group_by'], $b['group_by']);
        });

        // Add grouped items first (sorted), then ungrouped items
        $result = array_merge($groupedItems, $ungroupedItems);

        return $result;
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
                'rate' => $validated['rate'] ?? null,
                'group_by' => $validated['group_by'] ?? null,
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
                'rate' => $validated['rate'] ?? null,
                'group_by' => $validated['group_by'] ?? null,
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
                    'rate' => $originalForm->rate,
                    'group_by' => $originalForm->group_by,
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

                // Duplicate CD heads, items, ledgers and summaries
                $this->duplicateCdDataForForm($originalForm, $newForm);

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
     * Duplicate all forms with the same group_by value within a project.
     */
    public function duplicateGroupByGroupBy(string $clientName, string $projectName, string $groupBy, string $newGroupBy): int
    {
        DB::beginTransaction();
        try {
            // Get all forms with matching client_name, project_name, and group_by
            $forms = Form::where('client_name', $clientName)
                ->where('project_name', $projectName)
                ->where('group_by', $groupBy)
                ->with('fields')
                ->get();

            if ($forms->isEmpty()) {
                DB::rollBack();
                throw new \Exception('No forms found with the specified group.');
            }

            $duplicatedCount = 0;

            // Duplicate each form and its fields with new group_by value
            foreach ($forms as $originalForm) {
                // Create a new form with the same data but new group_by
                $newForm = Form::create([
                    'item_name' => $originalForm->item_name, // Keep same item names
                    'unit' => $originalForm->unit,
                    'rate' => $originalForm->rate,
                    'group_by' => $newGroupBy, // Use new group name
                    'client_name' => $originalForm->client_name,
                    'project_name' => $originalForm->project_name,
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

                // Duplicate CD heads, items, ledgers and summaries
                $this->duplicateCdDataForForm($originalForm, $newForm);

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
     * Duplicate CD heads, items, ledgers and summaries from one form to another.
     */
    private function duplicateCdDataForForm(Form $originalForm, Form $newForm): void
    {
        $userId = Auth::id();

        // 1. Duplicate CD heads and build head_id mapping (old_id => new_head)
        $headMapping = [];
        $cdHeads = CdHead::where('form_id', $originalForm->id)->get();
        foreach ($cdHeads as $oldHead) {
            $newHead = CdHead::create([
                'user_id' => $userId,
                'form_id' => $newForm->id,
                'name' => $oldHead->name,
            ]);
            $headMapping[$oldHead->id] = $newHead->id;
        }

        // 2. Duplicate CD ledger
        $cdLedger = CdLedger::where('form_id', $originalForm->id)->first();
        if ($cdLedger) {
            CdLedger::create([
                'user_id' => $userId,
                'form_id' => $newForm->id,
                'income' => $cdLedger->income,
            ]);
        }

        // 3. Duplicate CD items (need to map head_id)
        $cdItems = CdItem::where('form_id', $originalForm->id)->get();
        foreach ($cdItems as $oldItem) {
            $newHeadId = $headMapping[$oldItem->head_id] ?? null;
            if ($newHeadId) {
                CdItem::create([
                    'user_id' => $userId,
                    'form_id' => $newForm->id,
                    'name' => $oldItem->name,
                    'head_id' => $newHeadId,
                ]);
            }
        }

        // 4. Duplicate CD summaries (need to map head_id)
        $cdSummaries = CdSummary::where('form_id', $originalForm->id)->get();
        foreach ($cdSummaries as $oldSummary) {
            $newHeadId = $headMapping[$oldSummary->head_id] ?? null;
            if ($newHeadId) {
                CdSummary::create([
                    'user_id' => $userId,
                    'form_id' => $newForm->id,
                    'head_id' => $newHeadId,
                    'cd_type' => $oldSummary->cd_type,
                    'amount' => $oldSummary->amount,
                    'dated' => !empty($oldSummary->dated) ? date('Y-m-d', strtotime($oldSummary->dated)) : date('Y-m-d', strtotime($oldSummary->created_at)),
                    'description' => $oldSummary->description,
                ]);
            }
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

        // Get total unique records count (all deleted forms, not filtered)
        $totalRecords = Form::onlyTrashed()
            ->select('client_name', 'project_name')
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
     * Restore all deleted forms with the same client_name and project_name.
     */
    public function restoreFormsByProject(string $clientName, string $projectName): int
    {
        DB::beginTransaction();
        try {
            // Get all deleted forms with matching client_name and project_name
            $forms = Form::onlyTrashed()
                ->where('client_name', $clientName)
                ->where('project_name', $projectName)
                ->get();

            if ($forms->isEmpty()) {
                DB::rollBack();
                throw new \Exception('No deleted forms found with the specified client name and project name.');
            }

            $restoredCount = 0;

            // Restore all forms and their associated fields
            foreach ($forms as $form) {
                // Restore all associated fields first
                Field::onlyTrashed()->where('form_id', $form->id)->restore();

                // Restore the form
                $form->restore();
                $restoredCount++;
            }

            DB::commit();
            return $restoredCount;
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
        $footerText = excel_footer_text();
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

        // Summary Data - Group by group_by value
        $dataRow = $tableHeaderRow + 1;
        $serialNo = 1;

        // Separate forms into grouped and ungrouped
        $groupedForms = [];
        $ungroupedForms = [];

        foreach ($forms as $form) {
            $groupBy = $form->group_by;
            if (empty($groupBy) || $groupBy === null) {
                $ungroupedForms[] = $form;
            } else {
                if (!isset($groupedForms[$groupBy])) {
                    $groupedForms[$groupBy] = [];
                }
                $groupedForms[$groupBy][] = $form;
            }
        }

        // Group header style
        $groupHeaderStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'd9d9d9']
            ],
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

        // Helper function to add a data row for a form
        $addDataRow = function ($form, &$serialNo) use ($sheet, &$dataRow) {
            // Calculate total QTY (sum of product column, rounded up)
            $totalQty = ceil($form->fields->sum('product') ?? 0);

            // Calculate amount (total QTY * rate) and round up to integer
            $rate = $form->rate ?? 0;
            $amount = ceil($totalQty * $rate);

            $sheet->setCellValue('A' . $dataRow, $serialNo);
            $sheet->setCellValue('B' . $dataRow, ucwords(strtolower($form->item_name ?? '')));
            $sheet->setCellValue('C' . $dataRow, $form->unit ?? 'CFT'); // UNIT from form
            $sheet->setCellValue('D' . $dataRow, (int)$totalQty);
            $sheet->setCellValue('E' . $dataRow, $form->rate ?? ''); // RATE from form
            $sheet->setCellValue('F' . $dataRow, (int)$amount); // AMOUNT = ceil(TOTAL QTY * RATE)

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
            // Format RATE as number with 2 decimal places
            if ($form->rate !== null) {
                $sheet->getStyle('E' . $dataRow)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            // Format AMOUNT as integer (no decimals)
            $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode('#,##0');

            $dataRow++;
            $serialNo++;
        };

        // Track group total row references for grand total calculation
        $groupTotalRows = [];

        // Process grouped forms
        foreach ($groupedForms as $groupByValue => $groupForms) {
            // Add group header row
            $sheet->setCellValue('A' . $dataRow, ucwords(strtolower($groupByValue)));
            $sheet->mergeCells('A' . $dataRow . ':F' . $dataRow);
            $sheet->getStyle('A' . $dataRow . ':F' . $dataRow)->applyFromArray($groupHeaderStyle);
            $sheet->getRowDimension($dataRow)->setRowHeight(25);
            $dataRow++;

            // Track the first data row of this group for total calculation
            $groupFirstDataRow = $dataRow;

            // Add items under this group
            foreach ($groupForms as $form) {
                $addDataRow($form, $serialNo);
            }

            // Add total row for this group
            $groupLastDataRow = $dataRow - 1;
            if ($groupLastDataRow >= $groupFirstDataRow) {
                // Calculate total amount for this group (sum of column F)
                $totalFormula = '=SUM(F' . $groupFirstDataRow . ':F' . $groupLastDataRow . ')';

                // Merge cells A through E for "Total" label
                $sheet->mergeCells('A' . $dataRow . ':E' . $dataRow);

                // Set total row
                $sheet->setCellValue('A' . $dataRow, 'Total Amount');
                $sheet->setCellValue('F' . $dataRow, $totalFormula);

                // Store the row reference for grand total calculation
                $groupTotalRows[] = 'F' . $dataRow;

                // Style total row (no background color, with right alignment for total)
                $totalRowStyle = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '000000']],
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
                $sheet->getStyle('A' . $dataRow . ':F' . $dataRow)->applyFromArray($totalRowStyle);
                $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('F' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode('#,##0'); // Format as integer
                $sheet->getRowDimension($dataRow)->setRowHeight(25);
                $dataRow++;
            }
        }

        // Add blank row before grand total if there are groups
        if (!empty($groupTotalRows)) {
            $sheet->setCellValue('A' . $dataRow, '');
            $sheet->getRowDimension($dataRow)->setRowHeight(5);
            $dataRow++;

            // Add Grand Total row
            $grandTotalFormula = '=SUM(' . implode(',', $groupTotalRows) . ')';

            // Merge cells A through E for "Grand Total Amount" label
            $sheet->mergeCells('A' . $dataRow . ':E' . $dataRow);

            // Set grand total row
            $sheet->setCellValue('A' . $dataRow, 'Grand Total Amount');
            $sheet->setCellValue('F' . $dataRow, $grandTotalFormula);

            // Style grand total row
            $grandTotalRowStyle = [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '000000']],
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
            $sheet->getStyle('A' . $dataRow . ':F' . $dataRow)->applyFromArray($grandTotalRowStyle);
            $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode('#,##0'); // Format as integer
            $sheet->getRowDimension($dataRow)->setRowHeight(30);
            $dataRow++;
        }

        // Process ungrouped forms (null or empty group_by)
        foreach ($ungroupedForms as $form) {
            $addDataRow($form, $serialNo);
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
        $footerText = excel_footer_text();
        $sheet->getHeaderFooter()->setOddFooter($footerText);
        $sheet->getHeaderFooter()->setEvenFooter($footerText);
    }

    /**
     * Generate group summary sheet for Excel export.
     */
    public function generateGroupSummarySheet($sheet, string $projectName, string $groupBy, $forms, ?string $logoPath): void
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
        $sheet->setCellValue('C' . $row, ucwords(strtolower($groupBy)) . ' Summary');
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
        $serialNo = 1;
        $groupFirstDataRow = $dataRow;

        // Helper function to add a data row for a form
        $addDataRow = function ($form, &$serialNo) use ($sheet, &$dataRow) {
            // Calculate total QTY (sum of product column, rounded up)
            $totalQty = ceil($form->fields->sum('product') ?? 0);

            // Calculate amount (total QTY * rate) and round up to integer
            $rate = $form->rate ?? 0;
            $amount = ceil($totalQty * $rate);

            $sheet->setCellValue('A' . $dataRow, $serialNo);
            $sheet->setCellValue('B' . $dataRow, ucwords(strtolower($form->item_name ?? '')));
            $sheet->setCellValue('C' . $dataRow, $form->unit ?? 'CFT');
            $sheet->setCellValue('D' . $dataRow, (int)$totalQty);
            $sheet->setCellValue('E' . $dataRow, $form->rate ?? '');
            $sheet->setCellValue('F' . $dataRow, (int)$amount);

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
            $sheet->getStyle('D' . $dataRow)->getNumberFormat()->setFormatCode('0');
            if ($form->rate !== null) {
                $sheet->getStyle('E' . $dataRow)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode('#,##0');

            $dataRow++;
            $serialNo++;
        };

        // Add items for this group
        foreach ($forms as $form) {
            $addDataRow($form, $serialNo);
        }

        // Add total row for this group
        $groupLastDataRow = $dataRow - 1;
        if ($groupLastDataRow >= $groupFirstDataRow) {
            // Calculate total amount for this group (sum of column F)
            $totalFormula = '=SUM(F' . $groupFirstDataRow . ':F' . $groupLastDataRow . ')';

            // Merge cells A through E for "Total" label
            $sheet->mergeCells('A' . $dataRow . ':E' . $dataRow);

            // Set total row
            $sheet->setCellValue('A' . $dataRow, 'Total Amount');
            $sheet->setCellValue('F' . $dataRow, $totalFormula);

            // Style total row
            $totalRowStyle = [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '000000']],
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
            $sheet->getStyle('A' . $dataRow . ':F' . $dataRow)->applyFromArray($totalRowStyle);
            $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $dataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $dataRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getRowDimension($dataRow)->setRowHeight(25);
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
        $footerText = excel_footer_text();
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

        // Separate forms into grouped and ungrouped
        $groupedForms = [];
        $ungroupedForms = [];

        foreach ($forms as $form) {
            $groupBy = $form->group_by;
            if (empty($groupBy) || $groupBy === null) {
                $ungroupedForms[] = $form;
            } else {
                if (!isset($groupedForms[$groupBy])) {
                    $groupedForms[$groupBy] = [];
                }
                $groupedForms[$groupBy][] = $form;
            }
        }

        // Sort groups by group_by value
        ksort($groupedForms);

        $sheetIndex = 1; // Start from 1 since Summary is at 0

        // Process each group
        foreach ($groupedForms as $groupBy => $groupForms) {
            // Create group summary sheet
            $sanitizedGroupName = preg_replace('/[:\/\\?*\[\]]/', '-', $groupBy);
            $groupSummaryTitle = substr($sanitizedGroupName . ' - Summary', 0, 31);

            $groupSummarySheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $groupSummaryTitle);
            $spreadsheet->addSheet($groupSummarySheet, $sheetIndex);
            $groupSummarySheet->setTitle($groupSummaryTitle);
            $this->generateGroupSummarySheet($groupSummarySheet, $projectName, $groupBy, $groupForms, $logoPath);
            $sheetIndex++;

            // Create a sheet for each form in this group
            foreach ($groupForms as $form) {
                // Sanitize sheet title - Excel doesn't allow: : \ / ? * [ ]
                $rawTitle = $form->item_name ?? 'Sheet' . $sheetIndex;
                $sanitizedTitle = preg_replace('/[:\/\\?*\[\]]/', '-', $rawTitle);
                $sheetTitle = substr($sanitizedTitle, 0, 31);

                $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetTitle);
                $spreadsheet->addSheet($sheet, $sheetIndex);
                $sheet->setTitle($sheetTitle);

                // Generate the form sheet with configuration for multi-item export
                $this->generateFormSheet($sheet, $form, $logoPath, [
                    'headerRows' => 3,
                    'includeClientName' => false,
                    'logoMergeCells' => 'A1:A3',
                    'logoTotalRowHeight' => 60,
                ]);
                $sheetIndex++;
            }
        }

        // Create sheets for ungrouped forms
        foreach ($ungroupedForms as $form) {
            // Sanitize sheet title - Excel doesn't allow: : \ / ? * [ ]
            $rawTitle = $form->item_name ?? 'Sheet' . $sheetIndex;
            $sanitizedTitle = preg_replace('/[:\/\\?*\[\]]/', '-', $rawTitle);
            $sheetTitle = substr($sanitizedTitle, 0, 31);

            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetTitle);
            $spreadsheet->addSheet($sheet, $sheetIndex);
            $sheet->setTitle($sheetTitle);

            // Generate the form sheet with configuration for multi-item export
            $this->generateFormSheet($sheet, $form, $logoPath, [
                'headerRows' => 3,
                'includeClientName' => false,
                'logoMergeCells' => 'A1:A3',
                'logoTotalRowHeight' => 60,
            ]);
            $sheetIndex++;
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

    /**
     * Export forms that share the same group_by value for a client and project.
     */
    public function exportByGroup(string $clientName, string $projectName, string $groupBy): StreamedResponse
    {
        // Get all forms with matching client_name, project_name and group_by where item_name is not null
        $forms = Form::where('client_name', $clientName)
            ->where('project_name', $projectName)
            ->where('group_by', $groupBy)
            ->whereNotNull('item_name')
            ->with('fields')
            ->orderBy('item_name', 'asc')
            ->get();

        if ($forms->isEmpty()) {
            throw new \Exception('No forms found for the specified group. Please ensure there are forms with this group_by value for this client and project.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $logoPath = $this->findLogoPath();

        // Create Summary sheet first
        $summarySheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Summary');
        $spreadsheet->addSheet($summarySheet, 0);
        $summarySheet->setTitle('Summary');
        $this->generateSummarySheet($summarySheet, $projectName, $forms, $logoPath);

        // Create a sheet for each form (only those with matching group_by)
        foreach ($forms as $index => $form) {
            // Sanitize sheet title - Excel doesn't allow: : \ / ? * [ ]
            $rawTitle = $form->item_name ?? 'Sheet' . ($index + 1);
            $sanitizedTitle = preg_replace('/[:\/\\?*\[\]]/', '-', $rawTitle);
            $sheetTitle = substr($sanitizedTitle, 0, 31);

            $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheetTitle);
            $spreadsheet->addSheet($sheet, $index + 1); // Start from index 1 since Summary is at 0
            $sheet->setTitle($sheetTitle);

            // Generate the form sheet with configuration
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
        $filename = str_replace(' ', '_', $clientName) . '_' . str_replace(' ', '_', $projectName) . '_Group_' . str_replace(' ', '_', $groupBy) . '.xlsx';

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
