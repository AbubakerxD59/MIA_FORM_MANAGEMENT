<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceRate;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of the invoices.
     */
    public function index(Request $request): View|JsonResponse
    {
        // Check if this is a DataTables request
        if ($request->ajax() || $request->wantsJson()) {
            $data = $this->invoiceService->getInvoicesForDataTable($request);
            return response()->json($data);
        }

        return view('invoices.index');
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('invoices.index');
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
        ]);

        try {
            $invoice = $this->invoiceService->storeInvoice($validated);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'id' => $invoice->id,
                    'client_name' => $invoice->client_name,
                    'project_name' => $invoice->project_name,
                    'created_at' => $invoice->created_at->toISOString(),
                ], 201);
            }

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to create invoice: ' . $e->getMessage()], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to create invoice. Please try again.');
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View|JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json($invoice);
        }

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice): View
    {
        // Get unique invoice items for this invoice (through invoice_summary)
        $invoiceItems = InvoiceItem::where('invoice_id', $invoice->id)->get();

        // If no items found, get all invoice items for the user (for adding new items)
        if ($invoiceItems->isEmpty()) {
            $invoiceItems = InvoiceItem::all();
        }

        // Get all invoice rates
        $invoiceRates = InvoiceRate::all();

        return view('invoices.edit', compact('invoice', 'invoiceItems', 'invoiceRates'));
    }

    /**
     * Get invoice summaries for a specific invoice item.
     */
    public function getInvoiceSummaries(Request $request, Invoice $invoice): JsonResponse
    {
        $itemId = $request->get('item_id');

        if (!$itemId) {
            return response()->json(['error' => 'Item ID is required'], 400);
        }

        $data = $this->invoiceService->getInvoiceSummaries($invoice, (int)$itemId);

        return response()->json($data);
    }

    /**
     * Create a new invoice item.
     */
    public function createInvoiceItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        try {
            $invoiceItem = $this->invoiceService->createInvoiceItem($validated);

            return response()->json([
                'success' => true,
                'message' => 'Invoice item created successfully.',
                'item' => [
                    'id' => $invoiceItem->id,
                    'name' => $invoiceItem->name,
                    'created_at' => $invoiceItem->created_at->toISOString(),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create invoice item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new invoice rate.
     */
    public function createInvoiceRate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'rate' => 'nullable|integer|min:0',
        ]);

        try {
            $invoiceRate = $this->invoiceService->createInvoiceRate($validated);

            return response()->json([
                'success' => true,
                'message' => 'Invoice rate created successfully.',
                'rate' => [
                    'id' => $invoiceRate->id,
                    'name' => $invoiceRate->name,
                    'unit' => $invoiceRate->unit,
                    'rate' => $invoiceRate->rate,
                    'created_at' => $invoiceRate->created_at->toISOString(),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create invoice rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing invoice item.
     */
    public function updateInvoiceItem(Request $request, InvoiceItem $invoiceItem): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        try {
            $invoiceItem = $this->invoiceService->updateInvoiceItem($invoiceItem, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Invoice item updated successfully.',
                'item' => [
                    'id' => $invoiceItem->id,
                    'name' => $invoiceItem->name,
                    'updated_at' => $invoiceItem->updated_at->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update invoice item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing invoice rate.
     */
    public function updateInvoiceRate(Request $request, InvoiceRate $invoiceRate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'rate' => 'nullable|integer|min:0',
        ]);

        try {
            $invoiceRate = $this->invoiceService->updateInvoiceRate($invoiceRate, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Invoice rate updated successfully.',
                'rate' => [
                    'id' => $invoiceRate->id,
                    'name' => $invoiceRate->name,
                    'unit' => $invoiceRate->unit,
                    'rate' => $invoiceRate->rate,
                    'updated_at' => $invoiceRate->updated_at->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update invoice rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an invoice item.
     */
    public function deleteInvoiceItem(InvoiceItem $invoiceItem): JsonResponse
    {
        try {
            $this->invoiceService->deleteInvoiceItem($invoiceItem);

            return response()->json([
                'success' => true,
                'message' => 'Invoice item deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete invoice item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an invoice rate.
     */
    public function deleteInvoiceRate(InvoiceRate $invoiceRate): JsonResponse
    {
        try {
            $this->invoiceService->deleteInvoiceRate($invoiceRate);

            return response()->json([
                'success' => true,
                'message' => 'Invoice rate deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete invoice rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice rates for autocomplete.
     */
    public function getInvoiceRates(Request $request): JsonResponse
    {
        $query = $request->get('term', '');
        $data = $this->invoiceService->getInvoiceRates($query);
        return response()->json($data);
    }

    /**
     * Save invoice summaries for a specific invoice item.
     */
    public function saveInvoiceSummaries(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:invoice_items,id',
            'summaries' => 'required|array',
            'summaries.*.id' => 'nullable|exists:invoice_summary,id',
            'summaries.*.rate_id' => 'required|exists:invoice_rates,id',
            'summaries.*.description' => 'nullable|string',
            'summaries.*.unit' => 'nullable|string|max:255',
            'summaries.*.quantity' => 'nullable|integer|min:0',
            'summaries.*.rate' => 'nullable|integer|min:0',
            'summaries.*.amount' => 'nullable|integer|min:0',
            'summaries.*.remarks' => 'nullable|string',
        ]);

        try {
            $itemId = (int)$validated['item_id'];
            $summaries = $validated['summaries'];
            $savedCount = $this->invoiceService->saveInvoiceSummaries($invoice, $itemId, $summaries);

            return response()->json([
                'success' => true,
                'message' => "Invoice summary saved successfully. {$savedCount} record(s) saved.",
                'saved_count' => $savedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to save invoice summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
        ]);

        try {
            $invoice = $this->invoiceService->updateInvoice($invoice, $validated);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'id' => $invoice->id,
                    'client_name' => $invoice->client_name,
                    'project_name' => $invoice->project_name,
                    'message' => 'Invoice updated successfully.'
                ]);
            }

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Failed to update invoice: ' . $e->getMessage()], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update invoice. Please try again.');
        }
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->invoiceService->deleteInvoice($invoice);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Invoice deleted successfully.']);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Export invoice as Excel.
     */
    public function export(Invoice $invoice): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $spreadsheet = $this->invoiceService->exportInvoice($invoice);

            if ($spreadsheet->getSheetCount() === 0) {
                return redirect()->route('invoices.index')
                    ->with('error', 'No invoice data to export.');
            }

            // Create writer
            $writer = new Xlsx($spreadsheet);

            // Generate filename
            $filename = str_replace(' ', '_', $invoice->client_name) . '_' . str_replace(' ', '_', $invoice->project_name) . '_' . date('Y-m-d') . '.xlsx';

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
            return redirect()->route('invoices.index')
                ->with('error', 'Failed to export invoice: ' . $e->getMessage());
        }
    }
}
