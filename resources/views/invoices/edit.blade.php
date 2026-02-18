<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Invoice - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- jQuery UI for autocomplete -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }

        .sidebar-item {
            transition: background-color 0.2s;
        }

        .sidebar-item:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .sidebar-item.active {
            background-color: rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
        }

        .rate-item {
            transition: background-color 0.2s;
        }

        .rate-item:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        input[type="number"],
        textarea {
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Hide number input spinners */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Table styling */
        .invoice-table {
            border-collapse: collapse;
            width: 100%;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
            text-align: left;
        }

        .dark .invoice-table th,
        .dark .invoice-table td {
            border-color: #4b5563;
        }

        .invoice-table th {
            background-color: #f9fafb;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
        }

        .dark .invoice-table th {
            background-color: #1f2937;
        }

        .invoice-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .dark .invoice-table tbody tr:hover {
            background-color: #1f2937;
        }

        /* Column width adjustments */
        .invoice-table th:nth-child(1),
        .invoice-table td:nth-child(1) {
            /* DESCRIPTION - wider */
            width: 35%;
        }

        .invoice-table th:nth-child(2),
        .invoice-table td:nth-child(2) {
            /* UNIT - wider */
            width: 15%;
        }

        .invoice-table th:nth-child(3),
        .invoice-table td:nth-child(3) {
            /* QTY - narrower (integer) */
            width: 15%;
        }

        .invoice-table th:nth-child(4),
        .invoice-table td:nth-child(4) {
            /* RATE - narrower (mostly under 100) */
            width: 15%;
        }

        .invoice-table th:nth-child(5),
        .invoice-table td:nth-child(5) {
            /* AMOUNT */
            width: 20%;
        }

        /* Bottom Bar */
        #bottomBar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: white;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .dark #bottomBar {
            background: #1f2937;
            border-top-color: #374151;
        }

        #submitSummaryBtn {
            transition: all 0.3s ease;
        }

        #submitSummaryBtn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        #submitSummaryBtn:active {
            transform: translateY(0);
        }

        #submitSummaryBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Add padding to main content to account for bottom bar */
        main {
            padding-bottom: 4.5rem !important;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Invoice</h1>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('invoices.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to List
                        </a>
                        <x-user-dropdown />
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8" style="padding-bottom: 6rem;">
            <!-- Success Message -->
            @if (session('success'))
                <div
                    class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-5 py-4 rounded-r-lg shadow-md animate-slide-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Error Message -->
            @if (session('error'))
                <div
                    class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border-l-4 border-red-500 text-red-800 dark:text-red-200 px-5 py-4 rounded-r-lg shadow-md animate-slide-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="flex gap-6">
                <!-- Left Sidebar - Invoice Items -->
                <aside class="w-80 flex-shrink-0">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">
                            Invoice Items
                        </h2>

                        <!-- Create Item and Export Buttons -->
                        <div class="mb-4 flex gap-2">
                            <button type="button" id="createItemBtn"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Item
                            </button>
                            <a href="{{ route('invoices.export', $invoice->id) }}" 
                               class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export
                            </a>
                        </div>

                        <div id="invoiceItemsList" class="space-y-3 max-h-[calc(100vh-300px)] overflow-y-auto">
                            @if ($invoiceItems->count() > 0)
                                @foreach ($invoiceItems as $item)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 sidebar-item">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white flex-1 cursor-pointer"
                                                onclick="loadItemSummaries({{ $item->id }})">
                                                {{ $item->name ?: 'No Name' }}
                                            </p>
                                            <div class="flex gap-1">
                                                <button type="button" class="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                                    onclick="event.stopPropagation(); editInvoiceItem({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->type }}', {{ json_encode($item->notes ?? []) }})"
                                                    title="Edit item">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button type="button" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                                    onclick="event.stopPropagation(); deleteInvoiceItem({{ $item->id }}, '{{ addslashes($item->name) }}')"
                                                    title="Delete item">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-8">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        No items found.
                                    </p>
                                </div>
                            @endif
                        </div>

                    </div>
                </aside>

                <!-- Main Content Area -->
                <div class="flex-1">
                    <!-- Invoice Details -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Invoice Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Client Name
                                </label>
                                <input type="text" value="{{ $invoice->client_name }}" readonly
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Project Name
                                </label>
                                <input type="text" value="{{ $invoice->project_name }}" readonly
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Create Item/Rate Section -->
                    <div id="createSection" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <!-- Create/Edit Item Form -->
                        <div id="createItemForm" class="hidden">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" id="itemFormTitle">Add New Item</h3>
                            <div class="space-y-4">
                                <input type="hidden" id="editItemId" value="">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Item Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="newItemName" placeholder="Enter item name"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Type <span class="text-red-500">*</span>
                                    </label>
                                    <select id="invoiceType"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select Type</option>
                                        <option value="invoice">Invoice</option>
                                        <option value="bill">Bill</option>
                                        <option value="quotation">Quotation</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Notes
                                    </label>
                                    <div id="notesContainer" class="space-y-3">
                                        <!-- Dynamic Note Inputs will go here -->
                                    </div>
                                    <button type="button" id="addNoteBtn"
                                        class="mt-3 inline-flex items-center px-4 py-2 border border-blue-600 dark:border-blue-400 text-sm font-medium rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Note
                                    </button>
                                </div>
                                <div class="flex gap-3">
                                    <button type="button" id="saveItemBtn"
                                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                        Save
                                    </button>
                                    <button type="button" id="updateItemBtn" class="hidden px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                        Update
                                    </button>
                                    <button type="button" id="cancelItemBtn"
                                        class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Create/Edit Rate Form -->
                        <div id="createRateForm" class="hidden">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" id="rateFormTitle">Create New Rate</h3>
                            <div class="space-y-4">
                                <input type="hidden" id="editRateId" value="">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="newRateName" placeholder="Enter rate name"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Unit
                                    </label>
                                    <select id="newRateUnit"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select Unit</option>
                                        @foreach(unit_types() as $unit)
                                        <option value="{{ $unit }}">{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Rate
                                    </label>
                                    <input type="number" id="newRateValue" step="1" placeholder="Enter rate"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div class="flex gap-3">
                                    <button type="button" id="saveRateBtn"
                                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                        Save
                                    </button>
                                    <button type="button" id="updateRateBtn" class="hidden px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                        Update
                                    </button>
                                    <button type="button" id="cancelRateBtn"
                                        class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="mainContent"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div id="emptyMessage" class="text-center py-16">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Please select an item to
                                start working</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Choose an item from the left sidebar to view and edit its details.
                            </p>
                        </div>

                        <div id="tableContent" class="hidden">
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="selectedItemName">
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="invoice-table">
                                    <thead>
                                        <tr>
                                            <th>DESCRIPTION</th>
                                            <th class="text-center">UNIT</th>
                                            <th class="text-center">QTY</th>
                                            <th class="text-center">RATE</th>
                                            <th class="text-center">AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody id="summaryTableBody">
                                        <!-- Data will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar - Invoice Rates -->
                <aside class="w-80 flex-shrink-0">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">
                            Invoice Rates
                        </h2>

                        <!-- Add Rate Button -->
                        <div class="mb-4">
                            <button type="button" id="addRateBtn"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Rate
                            </button>
                        </div>

                        <div id="invoiceRatesList" class="space-y-2 max-h-[calc(100vh-300px)] overflow-y-auto">
                            @if ($invoiceRates->count() > 0)
                                @foreach ($invoiceRates as $rate)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 rate-item">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white flex-1">
                                                {{ $rate->name }}
                                            </p>
                                            <div class="flex gap-1">
                                                <button type="button" class="p-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition-colors"
                                                    onclick="editInvoiceRate({{ $rate->id }}, '{{ addslashes($rate->name) }}', '{{ $rate->unit ?? '' }}', '{{ $rate->rate ?? '' }}')"
                                                    title="Edit rate">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button type="button" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                                    onclick="deleteInvoiceRate({{ $rate->id }}, '{{ addslashes($rate->name) }}')"
                                                    title="Delete rate">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-8">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        No rates found.
                                    </p>
                                </div>
                            @endif
                        </div>

                    </div>
                </aside>
            </div>
        </main>

        <!-- Bottom Bar -->
        <div id="bottomBar" class="py-2 px-6">
            <div class="max-w-7xl mx-auto flex justify-center gap-3">
                <button type="button" id="addRowBtn"
                    class="hidden inline-flex items-center px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-lg transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Row
                </button>
                <button type="button" id="submitSummaryBtn"
                    class="hidden inline-flex items-center px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg transition-all duration-300">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7"></path>
                    </svg>
                    Submit
                </button>
            </div>
        </div>
    </div>

    <script>
        window.unitTypes = @json(unit_types());
        let currentItemId = null;

        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Handle Create Item button click
            $('#createItemBtn').on('click', function() {
                resetItemForm();
                $('#createSection').removeClass('hidden');
                $('#createItemForm').removeClass('hidden');
                $('#createRateForm').addClass('hidden');
                $('#newItemName').focus();
            });

            // Handle Cancel Item button click
            $('#cancelItemBtn').on('click', function() {
                resetItemForm();
                $('#createSection').addClass('hidden');
                $('#createItemForm').addClass('hidden');
            });

            // Handle Save Item button click
            $('#saveItemBtn').on('click', function() {
                const itemName = $('#newItemName').val().trim();
                const invoiceType = $('#invoiceType').val();
                if (!itemName) {
                    alert('Please enter an item name');
                    return;
                }
                if(!invoiceType) {
                    alert('Please select an invoice type');
                    return;
                }
                createInvoiceItem(itemName, invoiceType);
            });

            // Handle Update Item button click
            $('#updateItemBtn').on('click', function() {
                const itemId = $('#editItemId').val();
                const itemName = $('#newItemName').val().trim();
                const invoiceType = $('#invoiceType').val();
                if (!itemName) {
                    alert('Please enter an item name');
                    return;
                }
                if(!invoiceType) {
                    alert('Please select an invoice type');
                    return;
                }
                updateInvoiceItem(itemId, itemName, invoiceType);
            });

            // Handle Add Rate button click
            $('#addRateBtn').on('click', function() {
                resetRateForm();
                $('#createSection').removeClass('hidden');
                $('#createRateForm').removeClass('hidden');
                $('#createItemForm').addClass('hidden');
                $('#newRateName').focus();
            });

            // Handle Cancel Rate button click
            $('#cancelRateBtn').on('click', function() {
                resetRateForm();
                $('#createSection').addClass('hidden');
                $('#createRateForm').addClass('hidden');
            });

            // Handle Save Rate button click
            $('#saveRateBtn').on('click', function() {
                const rateName = $('#newRateName').val().trim();
                if (!rateName) {
                    alert('Please enter a rate name');
                    return;
                }
                const unit = $('#newRateUnit').val();
                const rate = $('#newRateValue').val();
                createInvoiceRate(rateName, unit, rate);
            });

            // Handle Update Rate button click
            $('#updateRateBtn').on('click', function() {
                const rateId = $('#editRateId').val();
                const rateName = $('#newRateName').val().trim();
                if (!rateName) {
                    alert('Please enter a rate name');
                    return;
                }
                const unit = $('#newRateUnit').val();
                const rate = $('#newRateValue').val();
                updateInvoiceRate(rateId, rateName, unit, rate);
            });

            // Handle Submit Summary button click
            $('#submitSummaryBtn').on('click', function() {
                submitInvoiceSummary();
            });

            // Handle Add Row button click
            $('#addRowBtn').on('click', function() {
                addNewRow();
            });

            // Handle Add Note button click
            $('#addNoteBtn').on('click', function() {
                addNoteInput();
            });

            // Handle Delete Note button click (using delegation)
            $('#notesContainer').on('click', '.delete-note-btn', function() {
                $(this).closest('.note-entry').remove();
            });
        });

        function addNoteInput(value = '') {
            const noteInput = `
                <div class="flex gap-2 items-center note-entry animate-fade-in">
                    <input type="text" name="notes[]" value="${value.replace(/"/g, '&quot;')}" placeholder="Enter note"
                        class="notes_field flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <button type="button" class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors delete-note-btn" title="Delete note">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `;
            $('#notesContainer').append(noteInput);
        }

        function getNotes() {
            const notes = [];
            $('.notes_field').each(function() {
                const val = $(this).val().trim();
                if (val) {
                    notes.push(val);
                }
            });
            return notes;
        }

        function createInvoiceItem(itemName, invoiceType) {
            const saveBtn = $('#saveItemBtn');
            const originalText = saveBtn.text();
            saveBtn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '{{ route('api.create-invoice-item') }}',
                method: 'POST',
                data: {
                    name: itemName,
                    type: invoiceType,
                    invoice_id: {{ $invoice->id }},
                    notes: getNotes()
                },
                success: function(response) {
                    if (response.success) {
                        // Hide the section and reload
                        $('#createSection').addClass('hidden');
                        $('#createItemForm').addClass('hidden');
                        $('#newItemName').val('');
                        $('#invoiceType').val('');
                        $('#notesContainer').empty();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                        saveBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Unknown error';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                    }
                    alert('Error creating item: ' + errorMessage);
                    saveBtn.prop('disabled', false).text(originalText);
                }
            });
        }

        function createInvoiceRate(rateName, unit, rate) {
            const saveBtn = $('#saveRateBtn');
            const originalText = saveBtn.text();
            saveBtn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '{{ route('api.create-invoice-rate') }}',
                method: 'POST',
                data: {
                    name: rateName,
                    unit: unit || '',
                    rate: rate || ''
                },
                success: function(response) {
                    if (response.success) {
                        // Hide the section and reload
                        $('#createSection').addClass('hidden');
                        $('#createRateForm').addClass('hidden');
                        $('#newRateName').val('');
                        $('#newRateUnit').val('');
                        $('#newRateValue').val('');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                        saveBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Unknown error';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                    }
                    alert('Error creating rate: ' + errorMessage);
                    saveBtn.prop('disabled', false).text(originalText);
                }
            });
        }

        function resetItemForm() {
            $('#editItemId').val('');
            $('#newItemName').val('');
            $('#invoiceType').val('');
            $('#notesContainer').empty();
            $('#itemFormTitle').text('Add New Item');
            $('#saveItemBtn').removeClass('hidden');
            $('#updateItemBtn').addClass('hidden');
        }

        function resetRateForm() {
            $('#editRateId').val('');
            $('#newRateName').val('');
            $('#newRateUnit').val('');
            $('#newRateValue').val('');
            $('#rateFormTitle').text('Create New Rate');
            $('#saveRateBtn').removeClass('hidden');
            $('#updateRateBtn').addClass('hidden');
        }

        function editInvoiceItem(itemId, itemName, type, notes = []) {
            resetItemForm();
            $('#editItemId').val(itemId);
            $('#newItemName').val(itemName);
            $('#invoiceType').val(type);

            // Populate notes
            if (Array.isArray(notes)) {
                notes.forEach(note => {
                   addNoteInput(note);
                });
            } else if (typeof notes === 'string') {
                // Handle potential JSON string or single string
                try {
                    const parsed = JSON.parse(notes);
                    if (Array.isArray(parsed)) {
                        parsed.forEach(note => addNoteInput(note));
                    } else {
                        addNoteInput(notes);
                    }
                } catch(e) {
                    addNoteInput(notes);
                }
            }
            $('#itemFormTitle').text('Edit Item');
            $('#saveItemBtn').addClass('hidden');
            $('#updateItemBtn').removeClass('hidden');
            $('#createSection').removeClass('hidden');
            $('#createItemForm').removeClass('hidden');
            $('#createRateForm').addClass('hidden');
            $('#newItemName').focus();
        }

        function editInvoiceRate(rateId, rateName, unit, rate) {
            resetRateForm();
            $('#editRateId').val(rateId);
            $('#newRateName').val(rateName);
            $('#newRateUnit').val(unit || '');
            $('#newRateValue').val(rate || '');
            $('#rateFormTitle').text('Edit Rate');
            $('#saveRateBtn').addClass('hidden');
            $('#updateRateBtn').removeClass('hidden');
            $('#createSection').removeClass('hidden');
            $('#createRateForm').removeClass('hidden');
            $('#createItemForm').addClass('hidden');
            $('#newRateName').focus();
        }

        function updateInvoiceItem(itemId, itemName, invoiceType) {
            const updateBtn = $('#updateItemBtn');
            const originalText = updateBtn.text();
            updateBtn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: `/api/invoices/items/${itemId}`,
                method: 'PUT',
                data: {
                    name: itemName,
                    type: invoiceType,
                    invoice_id: {{ $invoice->id }},
                    notes: getNotes()
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        resetItemForm();
                        $('#createSection').addClass('hidden');
                        $('#createItemForm').addClass('hidden');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                        updateBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Unknown error';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                    }
                    alert('Error updating item: ' + errorMessage);
                    updateBtn.prop('disabled', false).text(originalText);
                }
            });
        }

        function updateInvoiceRate(rateId, rateName, unit, rate) {
            const updateBtn = $('#updateRateBtn');
            const originalText = updateBtn.text();
            updateBtn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: `/api/invoices/rates/${rateId}`,
                method: 'PUT',
                data: {
                    name: rateName,
                    unit: unit || '',
                    rate: rate || ''
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        resetRateForm();
                        $('#createSection').addClass('hidden');
                        $('#createRateForm').addClass('hidden');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                        updateBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Unknown error';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                    }
                    alert('Error updating rate: ' + errorMessage);
                    updateBtn.prop('disabled', false).text(originalText);
                }
            });
        }

        function deleteInvoiceItem(itemId, itemName) {
            if (!confirm(`Are you sure you want to delete the item "${itemName}"? This action cannot be undone.`)) {
                return;
            }

            $.ajax({
                url: `/api/invoices/items/${itemId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        // If the deleted item was currently selected, clear the selection
                        if (currentItemId === itemId) {
                            currentItemId = null;
                            $('#emptyMessage').removeClass('hidden');
                            $('#tableContent').addClass('hidden');
                            $('#submitSummaryBtn').addClass('hidden');
                            $('#addRowBtn').addClass('hidden');
                            $('#addRowBtn').addClass('hidden');
                        }
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Unknown error';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                    }
                    alert('Error deleting item: ' + errorMessage);
                }
            });
        }

        function deleteInvoiceRate(rateId, rateName) {
            if (!confirm(`Are you sure you want to delete the rate "${rateName}"? This action cannot be undone.`)) {
                return;
            }

            $.ajax({
                url: `/api/invoices/rates/${rateId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Unknown error';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                    }
                    alert('Error deleting rate: ' + errorMessage);
                }
            });
        }

        function loadItemSummaries(itemId) {
            currentItemId = itemId;

            // Update active state in sidebar
            $('.sidebar-item').removeClass('active');
            $(`.sidebar-item[data-item-id="${itemId}"]`).addClass('active');

            // Get item name
            const itemName = $(`.sidebar-item[data-item-id="${itemId}"]`).find('p').first().text();

            // Show loading state
            $('#emptyMessage').addClass('hidden');
            $('#tableContent').removeClass('hidden');
            $('#submitSummaryBtn').removeClass('hidden');
            $('#addRowBtn').removeClass('hidden');
            $('#selectedItemName').text(itemName);
            
            // Show 20 blank rows by default with input fields
            let html = '';
            for (let i = 0; i < 20; i++) {
                html += `
                    <tr data-row-index="${i}" data-summary-id="">
                        <td>
                            <input type="text" class="description-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                                   value="" 
                                   data-rate-id=""
                                   placeholder="Enter description">
                        </td>
                        <td class="text-center">
                            <select class="unit-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center text-sm">
                                ${window.unitTypes.map(u => `<option value="${u}" ${u === 'SFT' ? 'selected' : ''}>${u}</option>`).join('')}
                            </select>
                        </td>
                        <td class="text-center">
                            <input type="number" class="qty-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                   value="" 
                                   step="1" 
                                   placeholder="0" 
                                   data-row-index="${i}">
                        </td>
                        <td class="text-center">
                            <input type="number" class="rate-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                   value="" 
                                   step="1" 
                                   placeholder="0" 
                                   data-row-index="${i}">
                        </td>
                        <td class="text-center">
                            <input type="number" class="amount-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                   value="0" 
                                   step="1" 
                                   placeholder="0" 
                                   readonly>
                        </td>
                    </tr>
                `;
            }
            $('#summaryTableBody').html(html);
            
            // Initialize autocomplete for blank rows
            initializeAutocomplete();
            
            // Initialize amount calculation
            initializeAmountCalculation();
            
            // Recalculate all amounts (will be 0 for blank rows)
            recalculateAllAmounts();

            // Fetch invoice summaries for this item
            $.ajax({
                url: '{{ route('api.invoice-summaries', $invoice->id) }}',
                method: 'GET',
                data: {
                    item_id: itemId
                },
                success: function(data) {
                    let html = '';
                    
                    // Populate rows with data, keeping 20 rows total
                    for (let i = 0; i < 20; i++) {
                        const row = data[i] || null;
                        const rowId = row ? row.id : null;
                        const rateId = row ? row.rate_id : null;
                        html += `
                            <tr data-row-index="${i}" data-summary-id="${rowId || ''}">
                                <td>
                                    <input type="text" class="description-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                                           value="${row ? (row.description || '') : ''}" 
                                           data-rate-id="${rateId || ''}"
                                           placeholder="Enter description">
                                </td>
                                <td class="text-center">
                                    <select class="unit-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center text-sm">
                                        ${window.unitTypes.map(u => { const sel = (!row || !row.unit) ? (u === 'SFT') : (row.unit === u); return `<option value="${u}" ${sel ? 'selected' : ''}>${u}</option>`; }).join('')}
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="number" class="qty-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                           value="${row ? (parseFloat(row.qty) || '') : ''}" 
                                           step="1" 
                                           placeholder="0" 
                                           data-row-index="${i}">
                                </td>
                                <td class="text-center">
                                    <input type="number" class="rate-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                           value="${row ? (parseFloat(row.rate) || '') : ''}" 
                                           step="1" 
                                           placeholder="0" 
                                           data-row-index="${i}">
                                </td>
                                <td class="text-center">
                                    <input type="number" class="amount-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                           value="${row ? (parseFloat(row.amount) || '0') : '0'}" 
                                           step="1" 
                                           placeholder="0" 
                                           readonly>
                                </td>
                            </tr>
                        `;
                    }

                    $('#summaryTableBody').html(html);
                    
                    // Initialize autocomplete for all description inputs
                    initializeAutocomplete();
                    
                    // Initialize amount calculation
                    initializeAmountCalculation();
                    
                    // Recalculate all amounts to ensure they're correct
                    recalculateAllAmounts();
                },
                error: function(xhr) {
                    console.error('Error loading summaries:', xhr);
                    // Keep the 20 blank rows even on error
                    let html = '';
                    for (let i = 0; i < 20; i++) {
                        html += `
                            <tr data-row-index="${i}" data-summary-id="">
                                <td>
                                    <input type="text" class="description-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                                           value="" 
                                           data-rate-id=""
                                           placeholder="Enter description">
                                </td>
                                <td class="text-center">
                                    <select class="unit-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center text-sm">
                                        ${window.unitTypes.map(u => `<option value="${u}" ${u === 'SFT' ? 'selected' : ''}>${u}</option>`).join('')}
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="number" class="qty-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                           value="" 
                                           step="1" 
                                           placeholder="0" 
                                           data-row-index="${i}">
                                </td>
                                <td class="text-center">
                                    <input type="number" class="rate-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                           value="" 
                                           step="1" 
                                           placeholder="0" 
                                           data-row-index="${i}">
                                </td>
                                <td class="text-center">
                                    <input type="number" class="amount-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                                           value="0" 
                                           step="1" 
                                           placeholder="0" 
                                           readonly>
                                </td>
                            </tr>
                        `;
                    }
                    $('#summaryTableBody').html(html);
                    
                    // Initialize autocomplete and amount calculation even on error
                    initializeAutocomplete();
                    initializeAmountCalculation();
                    
                    // Recalculate all amounts
                    recalculateAllAmounts();
                }
            });
        }

        function initializeAutocomplete() {
            $('.description-input').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route('api.invoice-rates') }}',
                        method: 'GET',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            const suggestions = data.map(function(item) {
                                return {
                                    label: item.label,
                                    value: item.value,
                                    id: item.id,
                                    unit: item.unit,
                                    rate: item.rate
                                };
                            });
                            
                            // Allow custom entry - if user types something not in suggestions, allow it
                            response(suggestions);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    const $input = $(this);
                    const $row = $input.closest('tr');
                    
                    // Set description value
                    $input.val(ui.item.value);
                    
                    // Set rate ID if it exists
                    if (ui.item.id) {
                        $input.attr('data-rate-id', ui.item.id);
                    } else {
                        $input.attr('data-rate-id', '');
                    }
                    
                    // Auto-fill unit and rate if available
                    if (ui.item.unit) {
                        $row.find('.unit-input').val(ui.item.unit);
                    }
                    if (ui.item.rate) {
                        $row.find('.rate-input').val(ui.item.rate);
                    }
                    
                    // Calculate amount
                    calculateAmount($row);
                    
                    return false;
                }
            }).on('blur', function() {
                // When user leaves the field, check if we need to create a new rate
                const $input = $(this);
                const $row = $input.closest('tr');
                const description = $input.val().trim();
                const rateId = $input.attr('data-rate-id');
                
                // If description is entered but no rate ID, mark it for rate creation
                if (description && !rateId) {
                    $input.attr('data-needs-rate', 'true');
                } else {
                    $input.removeAttr('data-needs-rate');
                }
            });
        }

        function initializeAmountCalculation() {
            // Calculate amount when QTY or RATE changes
            $(document).on('input', '.qty-input, .rate-input', function() {
                const $row = $(this).closest('tr');
                calculateAmount($row);
            });
        }

        function calculateAmount($row) {
            const qty = parseInt($row.find('.qty-input').val()) || 0;
            const rate = parseInt($row.find('.rate-input').val()) || 0;
            const amount = qty * rate;
            
            // Update amount field with calculated value (always recalculate, don't trust stored value)
            $row.find('.amount-input').val(Math.round(amount));
        }
        
        // Recalculate all amounts on page load for existing data
        function recalculateAllAmounts() {
            $('#summaryTableBody tr').each(function() {
                calculateAmount($(this));
            });
        }

        function addNewRow() {
            // Get the current highest row index
            let maxRowIndex = -1;
            $('#summaryTableBody tr').each(function() {
                const rowIndex = parseInt($(this).attr('data-row-index')) || 0;
                if (rowIndex > maxRowIndex) {
                    maxRowIndex = rowIndex;
                }
            });
            const newRowIndex = maxRowIndex + 1;

            // Create new row HTML
            const newRow = $(`
                <tr data-row-index="${newRowIndex}" data-summary-id="">
                    <td>
                        <input type="text" class="description-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                               value="" 
                               data-rate-id=""
                               placeholder="Enter description">
                    </td>
                    <td class="text-center">
                        <select class="unit-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center text-sm">
                            ${window.unitTypes.map(u => `<option value="${u}" ${u === 'SFT' ? 'selected' : ''}>${u}</option>`).join('')}
                        </select>
                    </td>
                    <td class="text-center">
                        <input type="number" class="qty-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                               value="" 
                               step="1" 
                               placeholder="0" 
                               data-row-index="${newRowIndex}">
                    </td>
                    <td class="text-center">
                        <input type="number" class="rate-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                               value="" 
                               step="1" 
                               placeholder="0" 
                               data-row-index="${newRowIndex}">
                    </td>
                    <td class="text-center">
                        <input type="number" class="amount-input w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center" 
                               value="0" 
                               step="1" 
                               placeholder="0" 
                               readonly>
                    </td>
                </tr>
            `);

            // Append the new row to the table
            $('#summaryTableBody').append(newRow);

            // Initialize autocomplete for the new row's description input
            newRow.find('.description-input').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route('api.invoice-rates') }}',
                        method: 'GET',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            const suggestions = data.map(function(item) {
                                return {
                                    label: item.label,
                                    value: item.value,
                                    id: item.id,
                                    unit: item.unit,
                                    rate: item.rate
                                };
                            });
                            response(suggestions);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    const $input = $(this);
                    const $row = $input.closest('tr');
                    
                    // Set description value
                    $input.val(ui.item.value);
                    
                    // Set rate ID if it exists
                    if (ui.item.id) {
                        $input.attr('data-rate-id', ui.item.id);
                    } else {
                        $input.attr('data-rate-id', '');
                    }
                    
                    // Auto-fill unit and rate if available
                    if (ui.item.unit) {
                        $row.find('.unit-input').val(ui.item.unit);
                    }
                    if (ui.item.rate) {
                        $row.find('.rate-input').val(ui.item.rate);
                    }
                    
                    // Calculate amount
                    calculateAmount($row);
                    
                    return false;
                }
            }).on('blur', function() {
                // When user leaves the field, check if we need to create a new rate
                const $input = $(this);
                const $row = $input.closest('tr');
                const description = $input.val().trim();
                const rateId = $input.attr('data-rate-id');
                
                // If description is entered but no rate ID, mark it for rate creation
                if (description && !rateId) {
                    $input.attr('data-needs-rate', 'true');
                } else {
                    $input.removeAttr('data-needs-rate');
                }
            });

            // Focus on the description input of the new row
            newRow.find('.description-input').focus();
        }

        function submitInvoiceSummary() {
            if (!currentItemId) {
                alert('Please select an item first');
                return;
            }

            const submitBtn = $('#submitSummaryBtn');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Saving...');

            // Collect all summary data from the table
            const summaries = [];
            $('#summaryTableBody tr').each(function(index) {
                const $row = $(this);
                const description = $row.find('.description-input').val().trim();
                const rateId = $row.find('.description-input').attr('data-rate-id');
                const unit = $row.find('.unit-input').val().trim();
                const qty = $row.find('.qty-input').val();
                const rate = $row.find('.rate-input').val();
                const amount = $row.find('.amount-input').val();
                const summaryId = $row.attr('data-summary-id');

                // Only include rows with description
                if (description) {
                    const needsRate = $row.find('.description-input').attr('data-needs-rate') === 'true';
                    
                    // If rate doesn't exist, we'll create it
                    if (needsRate || !rateId) {
                        // Store rate data to create - we'll handle this in async function
                        summaries.push({
                            id: summaryId || null,
                            rate_id: null, // Will be created
                            description: description,
                            unit: unit,
                            quantity: qty ? parseFloat(qty) : 0,
                            rate: rate ? parseFloat(rate) : 0,
                            amount: amount ? parseFloat(amount) : 0,
                            needsRate: true
                        });
                    } else {
                        // Use existing rate
                        summaries.push({
                            id: summaryId || null,
                            rate_id: rateId,
                            description: description,
                            unit: unit,
                            quantity: qty ? parseFloat(qty) : 0,
                            rate: rate ? parseFloat(rate) : 0,
                            amount: amount ? parseFloat(amount) : 0,
                            needsRate: false
                        });
                    }
                }
            });

            if (summaries.length === 0) {
                alert('Please fill in at least one row with a description');
                submitBtn.prop('disabled', false).text(originalText);
                return;
            }

            // Process summaries - create rates for those that need it
            processSummariesWithRates(summaries);
        }
        
        async function processSummariesWithRates(summaries) {
            const submitBtn = $('#submitSummaryBtn');
            const originalText = submitBtn.text();
            
            try {
                // First, create any new rates that are needed
                for (let i = 0; i < summaries.length; i++) {
                    const summary = summaries[i];
                    if (summary.needsRate) {
                        try {
                            const rateResponse = await $.ajax({
                                url: '{{ route('api.create-invoice-rate') }}',
                                method: 'POST',
                                data: {
                                    name: summary.description,
                                    unit: summary.unit,
                                    rate: summary.rate
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (rateResponse.success) {
                                summary.rate_id = rateResponse.rate.id;
                                // Update the input's data-rate-id for future reference
                                const $row = $('#summaryTableBody tr').eq(i);
                                $row.find('.description-input').attr('data-rate-id', rateResponse.rate.id);
                                $row.find('.description-input').removeAttr('data-needs-rate');
                            }
                        } catch (error) {
                            console.error('Error creating rate:', error);
                            alert('Error creating rate for "' + summary.description + '": ' + (error.responseJSON?.error || 'Unknown error'));
                            submitBtn.prop('disabled', false).text(originalText);
                            return;
                        }
                    }
                }
                
                // Remove the needsRate flag before submitting
                const finalSummaries = summaries.map(s => {
                    const { needsRate, ...rest } = s;
                    return rest;
                });
                
                // Submit all summaries
                $.ajax({
                    url: '{{ route('api.save-invoice-summaries', $invoice->id) }}',
                    method: 'POST',
                    data: {
                        item_id: currentItemId,
                        summaries: finalSummaries
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        submitBtn.prop('disabled', false).text('Submit');
                        
                        if (response.success) {
                            // Show success message
                            showSuccessMessage(response.message || 'Invoice summary saved successfully!');
                            
                            // Reload the summaries to get updated IDs
                            loadItemSummaries(currentItemId);
                        } else {
                            alert('Error: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).text('Submit');
                        
                        let errorMessage = 'An error occurred. Please try again.';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage = errors.join(', ');
                            }
                        }
                        alert('Error saving invoice summary: ' + errorMessage);
                    }
                });
            } catch (error) {
                console.error('Error in processSummariesWithRates:', error);
                submitBtn.prop('disabled', false).text('Submit');
                alert('An unexpected error occurred. Please try again.');
            }
        }

        function showSuccessMessage(message) {
            const successMsg = $(`
                <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-5 py-4 rounded-r-lg shadow-md animate-slide-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">${message}</span>
                    </div>
                </div>
            `);

            $('main').prepend(successMsg);

            setTimeout(function() {
                successMsg.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    </script>
</body>

</html>

