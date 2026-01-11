<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Field;
use App\Models\BarBendingFormItem;
use App\Models\BarBendingLocation;
use App\Models\BarBendingFormLocation;
use App\Models\Formula;
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
