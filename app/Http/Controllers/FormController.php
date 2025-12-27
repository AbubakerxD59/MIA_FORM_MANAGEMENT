<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Field;
use App\Models\BarBendingFormItem;
use App\Models\BarBendingLocation;
use App\Models\BarBendingFormLocation;
use App\Services\FormService;
use App\Services\BBSService;
use App\Http\Requests\StoreFormRequest;
use App\Http\Requests\UpdateFormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormController extends Controller
{
    protected FormService $formService;
    protected BBSService $bbsService;

    public function __construct(FormService $formService, BBSService $bbsService)
    {
        $this->formService = $formService;
        $this->bbsService = $bbsService;
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
     * Restore the specified deleted form.
     */
    public function restore($id)
    {
        try {
            $this->formService->restoreForm($id);

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Form restored successfully.']);
            }

            return redirect()->route('forms.deleted')
                ->with('success', 'Form restored successfully.');
        } catch (\Exception $e) {
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
}
