<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Field;
use App\Models\BarBendingFormItem;
use App\Models\BarBendingFormLocation;
use Illuminate\Support\Facades\DB;

class BBSService
{
    /**
     * Prepare form for BBS view.
     */
    public function prepareFormForBBS(Form $form): array
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

        // Get all bar bending form items for this form
        $barBendingFormItems = BarBendingFormItem::where('form_id', $form->id)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'created_at', 'form_id']);

        return [
            'form' => $form,
            'fields' => $fields,
            'barBendingFormItems' => $barBendingFormItems
        ];
    }

    /**
     * Get bar bending form items for a form with their locations.
     */
    public function getBarBendingFormItems(int $formId): array
    {
        $items = BarBendingFormItem::where('form_id', $formId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'created_at', 'form_id']);

        // Get locations for each item
        $itemsWithLocations = $items->map(function ($item) {
            $locations = BarBendingFormLocation::where('form_id', $item->form_id)
                ->where('item_id', $item->id)
                ->with('location')
                ->get()
                ->map(function ($formLocation) {
                    return [
                        'id' => $formLocation->location->id,
                        'name' => $formLocation->location->name,
                        'form_location_id' => $formLocation->id,
                    ];
                })
                ->toArray();

            return [
                'id' => $item->id,
                'name' => $item->name,
                'created_at' => $item->created_at,
                'form_id' => $item->form_id,
                'locations' => $locations,
            ];
        });

        return $itemsWithLocations->toArray();
    }

    /**
     * Store a new bar bending form item.
     */
    public function storeBarBendingFormItem(int $formId, string $name): BarBendingFormItem
    {
        return BarBendingFormItem::create([
            'form_id' => $formId,
            'name' => $name,
        ]);
    }

    /**
     * Update a bar bending form item's name.
     */
    public function updateBarBendingFormItemName(BarBendingFormItem $item, string $name): BarBendingFormItem
    {
        $item->update([
            'name' => $name,
        ]);

        return $item->fresh();
    }

    /**
     * Find a bar bending form item by form ID and name.
     */
    public function findBarBendingFormItemByName(int $formId, string $name): ?BarBendingFormItem
    {
        return BarBendingFormItem::where('form_id', $formId)
            ->where('name', $name)
            ->first();
    }

    /**
     * Update or create a bar bending form item by ID or form ID.
     */
    public function updateOrCreateBarBendingFormItem(?int $itemId, int $formId, string $name): BarBendingFormItem
    {
        if ($itemId) {
            $item = BarBendingFormItem::findOrFail($itemId);
            $item->update(['name' => $name]);
            return $item->fresh();
        }

        // Check if an item with this name already exists for this form
        $existingItem = $this->findBarBendingFormItemByName($formId, $name);
        if ($existingItem) {
            // Item already exists, just return it (name is already the same)
            return $existingItem;
        }

        return BarBendingFormItem::create([
            'form_id' => $formId,
            'name' => $name,
        ]);
    }

    /**
     * Get a bar bending form item with location.
     */
    public function getBarBendingFormItem(int $itemId): ?BarBendingFormItem
    {
        return BarBendingFormItem::find($itemId);
    }

    /**
     * Delete a bar bending form item.
     */
    public function deleteBarBendingFormItem(BarBendingFormItem $item): bool
    {
        return $item->delete();
    }
}
