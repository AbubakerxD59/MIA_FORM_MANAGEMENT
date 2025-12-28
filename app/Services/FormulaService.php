<?php

namespace App\Services;

use App\Models\Formula;
use Illuminate\Support\Collection;

class FormulaService
{
    /**
     * Get all formulas grouped by location_name.
     */
    public function getFormulasGroupedByLocation(): array
    {
        $formulas = Formula::orderBy('location_name', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by location_name
        $grouped = $formulas->groupBy('location_name')->map(function ($group) {
            return $group->map(function ($formula) {
                return [
                    'id' => $formula->id,
                    'location_name' => $formula->location_name,
                    'formula' => $formula->formula,
                    'created_at' => $formula->created_at,
                ];
            })->values();
        });

        return $grouped->toArray();
    }

    /**
     * Get all formulas.
     */
    public function getAllFormulas(): Collection
    {
        return Formula::orderBy('location_name', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get formulas by location_name.
     */
    public function getFormulasByLocation(string $locationName): Collection
    {
        return Formula::where('location_name', $locationName)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Store a new formula.
     */
    public function storeFormula(string $locationName, string $formula): Formula
    {
        return Formula::create([
            'location_name' => $locationName,
            'formula' => $formula,
        ]);
    }

    /**
     * Update an existing formula.
     */
    public function updateFormula(Formula $formula, string $locationName, string $formulaText): Formula
    {
        $formula->update([
            'location_name' => $locationName,
            'formula' => $formulaText,
        ]);

        return $formula->fresh();
    }

    /**
     * Delete a formula.
     */
    public function deleteFormula(Formula $formula): bool
    {
        return $formula->delete();
    }

    /**
     * Find a formula by ID.
     */
    public function findFormula(int $id): ?Formula
    {
        return Formula::find($id);
    }
}

