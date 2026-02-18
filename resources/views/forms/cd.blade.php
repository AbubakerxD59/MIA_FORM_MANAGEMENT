<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Credit/Debit - {{ config('app.name', 'Laravel') }}</title>

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
        .field-row {
            transition: background-color 0.2s;
        }

        .field-row:hover {
            background-color: rgba(59, 130, 246, 0.05);
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

        .error-highlight {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
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

        /* Animation for slide-in messages */
        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }

        .sidebar-head-item.active {
            background-color: rgba(99, 102, 241, 0.1);
            border-color: #6366f1;
        }

        .dark .sidebar-head-item.active {
            background-color: rgba(99, 102, 241, 0.2);
            border-color: #6366f1;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Credit/Debit</h1>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('forms.index') }}"
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
        <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-32">
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
                <!-- Left Sidebar -->
                <aside class="w-80 flex-shrink-0">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">
                            Credit/Debit
                        </h2>

                        <!-- Add Head and Export Buttons -->
                        <div class="mb-4 space-y-2">
                            <button type="button" id="addHeadBtn"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Head
                            </button>
                            <a href="{{ route('forms.cd.export', $form->id) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Export Excel
                            </a>
                        </div>

                        <!-- Sidebar Items List -->
                        <div id="sidebarItemsList" class="space-y-3 max-h-[calc(100vh-300px)] overflow-y-auto">
                            @if ($cdHeads->count() > 0)
                                @foreach ($cdHeads as $head)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors sidebar-head-item"
                                        data-head-id="{{ $head->id }}">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1 cursor-pointer" onclick="loadHead({{ $head->id }})">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $head->name ?: 'No Name' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Created: {{ $head->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                            <button type="button"
                                                onclick="event.stopPropagation(); confirmDeleteHead({{ $head->id }}, '{{ addslashes($head->name ?: 'No Name') }}')"
                                                class="ml-2 p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                                title="Delete head">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div id="emptySidebarMessage" class="text-center py-8">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        No heads found.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <div class="flex-1">
                    <!-- Form Details -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Form Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Client Name
                                </label>
                                <div
                                    class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
                                    {{ $form->client_name }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Project Name
                                </label>
                                <div
                                    class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white">
                                    {{ $form->project_name }}
                                </div>
                            </div>
                        </div>
                        <!-- Total Income and Total Balance Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 flex flex-wrap gap-6 items-end">
                            <div class="flex-1 min-w-[200px] max-w-md">
                                <label for="total_income"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Total Income
                                </label>
                                <input type="number" id="total_income" name="income" step="0.01"
                                    min="0" value="{{ $totalIncome }}" placeholder="Total income" readonly
                                    class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white cursor-not-allowed">
                            </div>
                            <div class="flex-1 min-w-[200px] max-w-md">
                                <label for="total_expense"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Total Expense
                                </label>
                                <input type="number" id="total_expense" step="0.01" readonly
                                    placeholder="Total expense" value=""
                                    class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white cursor-not-allowed">
                            </div>
                            <div class="flex-1 min-w-[200px] max-w-md">
                                <label for="total_balance"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Total Balance
                                </label>
                                <input type="number" id="total_balance" step="0.01" readonly
                                    placeholder="Total balance" value=""
                                    class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <!-- Create Head Section (Hidden by default) -->
                    <div id="createHeadSection"
                        class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Head</h2>
                            <button type="button" id="cancelCreateHeadBtn"
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <form id="createHeadForm">
                            @csrf
                            <div class="mb-4">
                                <label for="head_name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Head Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="head_name" name="name" required
                                    placeholder="Enter head name"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" id="cancelHeadBtn"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors duration-200">
                                    Cancel
                                </button>
                                <button type="submit" id="saveHeadBtn"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors duration-200">
                                    Save Head
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Edit Head Section (Hidden by default, shows when head is selected) -->
                    <div id="editHeadSection"
                        class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Manage Head</h2>
                        </div>

                        <!-- Edit Head Form -->
                        <form id="editHeadForm">
                            @csrf
                            <input type="hidden" id="edit_head_id" name="head_id">
                            <div class="flex items-end gap-3">
                                <div class="flex-1 max-w-md">
                                    <label for="edit_head_name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Head Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="edit_head_name" name="name" required
                                        placeholder="Enter head name"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <button type="submit" id="updateHeadBtn"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors duration-200">
                                    Update Head
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Credit/Debit Content Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Credit/Debit Management
                        </h2>

                        <!-- Table Section -->
                        <form id="cdSummaryForm">
                            @csrf
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" style="width: 8%;"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                S. No
                                            </th>
                                            <th scope="col" style="width: 25%;"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Account
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                DATED
                                            </th>
                                            <th scope="col" style="width: 40%;"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                DESCRIPTION
                                            </th>
                                            <th scope="col" style="width: 15%;"
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                DEB
                                            </th>
                                            <th scope="col" style="width: 15%;"
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                CRD
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                TOTAL
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                ACTION
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="cdSummaryTableBody"
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <!-- 50 rows with input fields, populated with existing data if available -->
                                        @php
                                            $existingSummaries = $groupedSummaries ?? [];
                                            $summaryCount = count($existingSummaries);
                                        @endphp
                                        @for ($i = 1; $i <= 50; $i++)
                                            @php
                                                $summaryIndex = $i - 1;
                                                $summary = isset($existingSummaries[$summaryIndex])
                                                    ? $existingSummaries[$summaryIndex]
                                                    : null;
                                                $snoValue = $summary ? $summary['head_name'] : '';
                                                $datedValue = $summary
                                                    ? $summary['created_at']->format('Y-m-d')
                                                    : date('Y-m-d');
                                                $descriptionValue = $summary ? $summary['description'] ?? '' : '';
                                                $debitValue = $summary ? (int) $summary['debit'] : '';
                                                $creditValue = $summary ? (int) $summary['credit'] : '';
                                            @endphp
                                            <tr class="cd-summary-row" data-row-index="{{ $i }}">
                                                <td class="px-6 py-2 text-center" style="width: 8%;">
                                                    <span
                                                        class="text-sm text-gray-900 dark:text-white">{{ $i }}</span>
                                                </td>
                                                <td class="px-6 py-2" style="width: 25%;">
                                                    <input type="text" name="sno[]" value="{{ $snoValue }}"
                                                        placeholder="Enter head name"
                                                        class="sno-autocomplete w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                                </td>
                                                <td class="px-6 py-2 whitespace-nowrap">
                                                    <input type="date" name="dated[]" value="{{ $datedValue }}"
                                                        class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                                </td>
                                                <td class="px-6 py-2" style="width: 40%;">
                                                    <input type="text" name="description[]"
                                                        value="{{ $descriptionValue }}"
                                                        placeholder="Enter description"
                                                        class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                                </td>
                                                <td class="px-6 py-2 whitespace-nowrap" style="width: 15%;">
                                                    <input type="number" name="debit[]" step="1"
                                                        min="0" placeholder="0" value="{{ $debitValue }}"
                                                        class="w-full px-2 py-1 text-sm text-right border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white debit-input"
                                                        onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                </td>
                                                <td class="px-6 py-2 whitespace-nowrap" style="width: 15%;">
                                                    <input type="number" name="credit[]" step="1"
                                                        min="0" placeholder="0" value="{{ $creditValue }}"
                                                        class="w-full px-2 py-1 text-sm text-right border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white credit-input"
                                                        onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                </td>
                                                <td
                                                    class="px-6 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white total-cell">
                                                    0
                                                </td>
                                                <td class="px-6 py-2 whitespace-nowrap text-center">
                                                    <button type="button" onclick="deleteRow(this)"
                                                        class="delete-row-btn p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                                        title="Delete row">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <!-- Fixed Form Actions -->
        <div
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-center space-x-4">
                    <button type="button" id="addRowBtn"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Add Row
                    </button>
                    <a href="{{ route('forms.index') }}"
                        class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" form="cdSummaryForm" id="submitCdSummaryBtn"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const currentFormId = {{ $form->id }};
        let currentHeadId = null;

        $(document).ready(function() {
            // Handle Add Head button click
            $('#addHeadBtn').on('click', function() {
                // Hide edit section if open
                $('#editHeadSection').addClass('hidden');
                // Remove active state from sidebar
                $('.sidebar-head-item').removeClass('active');
                currentHeadId = null;

                $('#createHeadSection').removeClass('hidden').addClass('animate-slide-in');
                $('#head_name').focus();
            });

            // Handle Cancel Create Head button
            $('#cancelCreateHeadBtn, #cancelHeadBtn').on('click', function() {
                $('#createHeadSection').addClass('hidden');
                $('#createHeadForm')[0].reset();
            });

            // Handle Create Head form submission
            $('#createHeadForm').on('submit', function(e) {
                e.preventDefault();

                const headName = $('#head_name').val().trim();
                if (!headName) {
                    alert('Please enter a head name');
                    return;
                }

                const saveBtn = $('#saveHeadBtn');
                const originalText = saveBtn.text();
                saveBtn.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: `/api/forms/${currentFormId}/cd-heads`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    data: {
                        name: headName,
                        form_id: currentFormId
                    },
                    success: function(response) {
                        saveBtn.prop('disabled', false).text(originalText);
                        $('#createHeadSection').addClass('hidden');
                        $('#createHeadForm')[0].reset();

                        // Refresh sidebar
                        location.reload();
                    },
                    error: function(xhr) {
                        saveBtn.prop('disabled', false).text(originalText);

                        let errorMessage = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join('\n');
                        }

                        alert(errorMessage);
                    }
                });
            });

            // Handle Edit Head form submission
            $('#editHeadForm').on('submit', function(e) {
                e.preventDefault();

                const headId = $('#edit_head_id').val();
                const headName = $('#edit_head_name').val().trim();
                if (!headName) {
                    alert('Please enter a head name');
                    return;
                }

                const updateBtn = $('#updateHeadBtn');
                const originalText = updateBtn.text();
                updateBtn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: `/api/cd-heads/${headId}`,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    data: {
                        name: headName
                    },
                    success: function(response) {
                        updateBtn.prop('disabled', false).text(originalText);

                        // Update the head name in the sidebar without reloading
                        const headElement = $(`.sidebar-head-item[data-head-id="${headId}"]`);
                        if (headElement.length > 0) {
                            headElement.find('p.text-sm.font-semibold').text(headName ||
                                'No Name');
                        }

                        // Show success message
                        alert('Head updated successfully!');

                        // Optionally refresh sidebar to ensure consistency
                        refreshSidebar(headId);
                    },
                    error: function(xhr) {
                        updateBtn.prop('disabled', false).text(originalText);

                        let errorMessage = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join('\n');
                        }

                        alert(errorMessage);
                    }
                });
            });

            updateTotalBalance();
        });

        // Function to load head and make it active
        function loadHead(headId) {
            // Remove active state from all heads
            $('.sidebar-head-item').removeClass('active');
            // Hide create head section
            $('#createHeadSection').addClass('hidden');

            // Make selected head active
            $(`.sidebar-head-item[data-head-id="${headId}"]`).addClass('active');

            currentHeadId = headId;

            // Fetch head details
            $.ajax({
                url: `/api/cd-heads/${headId}`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    // Populate edit head form
                    $('#edit_head_id').val(response.head.id);
                    $('#edit_head_name').val(response.head.name);
                    $('#editHeadSection').removeClass('hidden').addClass('animate-slide-in');
                },
                error: function(xhr) {
                    alert('Failed to load head details');
                }
            });
        }

        // Function to refresh sidebar
        function refreshSidebar(selectedHeadId = null) {
            $.ajax({
                url: `/api/forms/${currentFormId}/cd-heads`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(heads) {
                    const sidebarList = $('#sidebarItemsList');
                    sidebarList.empty();

                    if (heads.length === 0) {
                        sidebarList.html(
                            '<div class="text-center py-8"><p class="text-sm text-gray-500 dark:text-gray-400">No heads found. Click "Add Head" to create one.</p></div>'
                        );
                        // Reinitialize autocomplete
                        setupSnoAutocomplete();
                        return;
                    }

                    heads.forEach(function(head) {
                        const headHtml = `
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors sidebar-head-item"
                                data-head-id="${head.id}">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1 cursor-pointer" onclick="loadHead(${head.id})">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            ${head.name || 'No Name'}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Created: ${head.created_at}
                                        </p>
                                    </div>
                                    <button type="button"
                                        onclick="event.stopPropagation(); confirmDeleteHead(${head.id}, '${head.name.replace(/'/g, "\\'")}')"
                                        class="ml-2 p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                        title="Delete head">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        `;
                        sidebarList.append(headHtml);
                    });

                    // If a head was selected before refresh, restore its active state
                    if (selectedHeadId) {
                        loadHead(selectedHeadId);
                    }

                    // Reinitialize autocomplete with updated heads
                    setupSnoAutocomplete();
                },
                error: function() {
                    console.error('Failed to refresh sidebar');
                    // Reinitialize autocomplete anyway
                    setupSnoAutocomplete();
                }
            });
        }

        function confirmDeleteHead(headId, headName) {
            if (confirm(
                    `Are you sure you want to delete "${headName}"?`)) {
                // TODO: Implement delete functionality
                console.log('Deleting head:', headId);
            }
        }

        // Get initial total income and base income
        window.baseIncome = parseFloat({{ $baseIncome ?? 0 }}) || 0;
        window.initialTotalIncome = parseFloat({{ $totalIncome }}) || 0;

        // Function to update displayed total income
        function updateDisplayedTotalIncome() {
            // Calculate total credit from current summaries
            let totalCredit = 0;
            $('.credit-input').each(function() {
                const creditVal = $(this).val();
                const creditValue = creditVal ? Number(creditVal) || 0 : 0;
                totalCredit = Number(totalCredit) + Number(creditValue);
            });

            // Get current base income (may have been updated)
            const currentBaseIncome = window.baseIncome !== undefined ? window.baseIncome : parseFloat(
                {{ $baseIncome ?? 0 }}) || 0;

            // Update displayed total income (base income + total credits)
            const newTotalIncome = totalCredit;
            $('#total_income').val(newTotalIncome);

            // Update the initial total income for calculations
            window.initialTotalIncome = newTotalIncome;

            updateTotalBalance();
        }

        // Function to update Total Balance (Total Income - Total Expense from all debit amounts in summary table)
        function updateTotalBalance() {
            // Total Expense = sum of all debit values from the summary table
            let totalExpense = 0;
            $('.debit-input').each(function() {
                const debitVal = $(this).val();
                totalExpense += debitVal ? (Number(debitVal) || 0) : 0;
            });
            const totalIncome = parseFloat($('#total_income').val()) || 0;
            const totalBalance = totalIncome - totalExpense;
            $('#total_expense').val(totalExpense);
            $('#total_balance').val(totalBalance);
        }

        // Function to get the base total for a row (previous row's total or total income for first row)
        function getBaseTotalForRow(row) {
            const rowIndex = parseInt(row.data('row-index')) || 1;

            // Use the current initialTotalIncome (which may have been updated)
            const currentTotalIncome = window.initialTotalIncome !== undefined ? Number(window.initialTotalIncome) : Number(
                {{ $totalIncome }}) || 0;

            if (rowIndex === 1) {
                // First row uses total income as base
                return currentTotalIncome;
            } else {
                // Get previous row's total
                const prevRow = $(`.cd-summary-row[data-row-index="${rowIndex - 1}"]`);

                // Check if previous row exists
                if (prevRow.length === 0) {
                    return currentTotalIncome;
                }

                const prevTotalText = prevRow.find('.total-cell').text();
                const prevTotal = prevTotalText ? Number(prevTotalText.trim().replace(/[^0-9.-]/g, '')) || 0 : 0;

                // If previous row has no data, use total income
                if (prevTotal === 0) {
                    // Check if previous row has any data
                    const prevDebitVal = prevRow.find('.debit-input').val();
                    const prevCreditVal = prevRow.find('.credit-input').val();
                    const prevDebit = prevDebitVal ? Number(prevDebitVal) || 0 : 0;
                    const prevCredit = prevCreditVal ? Number(prevCreditVal) || 0 : 0;
                    const prevSnoVal = prevRow.find('input[name="sno[]"]').val();
                    const prevSno = prevSnoVal ? prevSnoVal.trim() : '';
                    const prevDatedVal = prevRow.find('input[name="dated[]"]').val();
                    const prevDated = prevDatedVal ? prevDatedVal.trim() : '';
                    const prevDescriptionVal = prevRow.find('input[name="description[]"]').val();
                    const prevDescription = prevDescriptionVal ? prevDescriptionVal.trim() : '';

                    if (prevDebit === 0 && prevCredit === 0 && prevSno === '' && prevDated === '' && prevDescription ===
                        '') {
                        // Previous row is empty, use total income
                        return currentTotalIncome;
                    }
                }

                return prevTotal;
            }
        }

        // Function to recalculate all rows from a given row index
        function recalculateFromRow(startRowIndex) {
            // Calculate from scratch - process all rows in order
            $('.cd-summary-row').each(function() {
                const row = $(this);
                const rowIndex = parseInt(row.data('row-index')) || 1;

                if (rowIndex < startRowIndex) {
                    return; // Skip rows before the changed row
                }

                const debitVal = row.find('.debit-input').val();
                const creditVal = row.find('.credit-input').val();
                const debit = debitVal ? Number(debitVal) || 0 : 0;
                const credit = creditVal ? Number(creditVal) || 0 : 0;

                // Check if this row has any values entered
                const hasData = debit > 0 || credit > 0;

                if (hasData) {
                    let rowTotal = 0;

                    if (rowIndex === 1) {
                        // First row: total amount equals debit or credit amount
                        // If both exist, use credit - debit (net)
                        if (credit > 0 && debit > 0) {
                            rowTotal = credit - debit;
                        } else if (credit > 0) {
                            rowTotal = credit;
                        } else if (debit > 0) {
                            rowTotal = -debit;
                        }
                    } else {
                        // Get previous row's total
                        const prevRow = $(`.cd-summary-row[data-row-index="${rowIndex - 1}"]`);

                        if (prevRow.length > 0) {
                            const prevTotalText = prevRow.find('.total-cell').text();
                            const prevTotal = prevTotalText ? Number(prevTotalText.trim().replace(/[^0-9.-]/g,
                                '')) || 0 : 0;

                            if (prevTotal > 0) {
                                // If previous total > 0: subtract debit and add credit
                                rowTotal = prevTotal - debit + credit;
                            } else {
                                // If previous total <= 0: still apply the same logic
                                rowTotal = prevTotal - debit + credit;
                            }
                        } else {
                            // No previous row found, treat as first row
                            if (credit > 0 && debit > 0) {
                                rowTotal = credit - debit;
                            } else if (credit > 0) {
                                rowTotal = credit;
                            } else if (debit > 0) {
                                rowTotal = -debit;
                            }
                        }
                    }

                    row.find('.total-cell').text(Math.round(rowTotal));
                } else {
                    // Reset to 0 if row is empty
                    row.find('.total-cell').text('0');
                }
            });
        }

        // Calculate total for each row: Previous row's total - DEB + CRD
        // First row uses Total Income as base
        $(document).on('keyup', '.debit-input, .credit-input', function() {
            // Remove any non-numeric characters except digits
            let value = $(this).val().replace(/[^0-9]/g, '');
            if (value !== $(this).val()) {
                $(this).val(value);
            }

            const row = $(this).closest('tr');
            const rowIndex = parseInt(row.data('row-index')) || 1;

            // Recalculate from this row onwards
            recalculateFromRow(rowIndex);

            // Update displayed total income when credit values change
            if ($(this).hasClass('credit-input')) {
                updateDisplayedTotalIncome();
            }

            updateTotalBalance();
        });

        // Reset total to 0 when row fields are cleared and recalculate subsequent rows
        $(document).on('input', 'input[name="sno[]"], input[name="dated[]"], input[name="description[]"]', function() {
            const row = $(this).closest('tr');
            const rowIndex = parseInt(row.data('row-index')) || 1;
        });

        // Function to setup autocomplete for Account fields (using CD heads)
        function setupSnoAutocomplete() {
            // Destroy existing autocomplete instances only if they exist
            $('.sno-autocomplete').each(function() {
                if ($(this).data('ui-autocomplete')) {
                    $(this).autocomplete('destroy');
                }
            });

            $('.sno-autocomplete').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: `/api/forms/${currentFormId}/cd-heads/autocomplete`,
                        method: 'GET',
                        data: {
                            term: request.term
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(data) {
                            response(data);
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 0,
                delay: 300
            }).on('focus', function() {
                $(this).autocomplete('search', '');
            });
        }

        // Function to get the next row index
        function getNextRowIndex() {
            let maxIndex = 0;
            $('.cd-summary-row').each(function() {
                const index = parseInt($(this).data('row-index')) || 0;
                if (index > maxIndex) {
                    maxIndex = index;
                }
            });
            return maxIndex + 1;
        }

        // Function to generate a new row HTML
        function getNewRowHTML(rowIndex) {
            const currentDate = '{{ date('Y-m-d') }}';
            return `
                <tr class="cd-summary-row" data-row-index="${rowIndex}">
                    <td class="px-6 py-2 text-center" style="width: 8%;">
                        <span class="text-sm text-gray-900 dark:text-white">${rowIndex}</span>
                    </td>
                    <td class="px-6 py-2" style="width: 25%;">
                        <input type="text" name="sno[]" value=""
                            placeholder="Enter head name"
                            class="sno-autocomplete w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-6 py-2 whitespace-nowrap">
                        <input type="date" name="dated[]" value="${currentDate}"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-6 py-2" style="width: 40%;">
                        <input type="text" name="description[]" value=""
                            placeholder="Enter description"
                            class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-6 py-2 whitespace-nowrap" style="width: 15%;">
                        <input type="number" name="debit[]" step="1"
                            min="0" placeholder="0" value=""
                            class="w-full px-2 py-1 text-sm text-right border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white debit-input"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    </td>
                    <td class="px-6 py-2 whitespace-nowrap" style="width: 15%;">
                        <input type="number" name="credit[]" step="1"
                            min="0" placeholder="0" value=""
                            class="w-full px-2 py-1 text-sm text-right border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white credit-input"
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                    </td>
                    <td class="px-6 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white total-cell">
                        0
                    </td>
                    <td class="px-6 py-2 whitespace-nowrap text-center">
                        <button type="button" 
                            onclick="deleteRow(this)"
                            class="delete-row-btn p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                            title="Delete row">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
        }

        // Function to add a new row
        function addNewRow() {
            const tbody = $('#cdSummaryTableBody');
            const rowIndex = getNextRowIndex();
            const newRowHTML = getNewRowHTML(rowIndex);
            const $newRow = $(newRowHTML);

            // Append the new row
            tbody.append($newRow);

            // Initialize autocomplete for the new row's Account field
            $newRow.find('.sno-autocomplete').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: `/api/forms/${currentFormId}/cd-heads/autocomplete`,
                        method: 'GET',
                        data: {
                            term: request.term
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(data) {
                            response(data);
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 0,
                delay: 300
            }).on('focus', function() {
                $(this).autocomplete('search', '');
            });
        }

        // Function to delete a row
        function deleteRow(button) {
            const row = $(button).closest('tr');

            // Confirm deletion
            if (!confirm('Are you sure you want to delete this row?')) {
                return;
            }

            // Remove the row from the table
            row.remove();

            // Recalculate totals starting from the first row
            console.log('2');
            recalculateFromRow(1);
        }

        // Setup autocomplete on page load
        $(document).ready(function() {
            setupSnoAutocomplete();

            // Handle Add Row button click
            $('#addRowBtn').on('click', function() {
                addNewRow();
            });

            // Handle form submission
            $('#cdSummaryForm').on('submit', function(e) {
                e.preventDefault();
                submitCdSummary();
            });

            // Recalculate totals for existing data on page load
            @if (isset($groupedSummaries) && count($groupedSummaries) > 0)
                // Recalculate all rows to set correct totals
                recalculateFromRow(1);
                // Update displayed total income to reflect current credits
                updateDisplayedTotalIncome();
            @endif
            updateTotalBalance();
        });

        // Function to submit CD summary form
        function submitCdSummary() {
            const submitBtn = $('#submitCdSummaryBtn');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Saving...');

            // Collect all form data
            const summaries = [];
            $('.cd-summary-row').each(function(index) {
                const row = $(this);
                const headName = row.find('input[name="sno[]"]').val().trim();
                const dated = row.find('input[name="dated[]"]').val();
                const description = row.find('input[name="description[]"]').val().trim();
                const debitVal = row.find('input[name="debit[]"]').val();
                const creditVal = row.find('input[name="credit[]"]').val();
                const debit = debitVal ? Number(debitVal) || 0 : 0;
                const credit = creditVal ? Number(creditVal) || 0 : 0;

                // Only include rows with at least one field filled
                if (headName || dated || description || debit > 0 || credit > 0) {
                    // If debit has value, add debit entry
                    if (debit > 0) {
                        summaries.push({
                            head_name: headName,
                            dated: dated || null,
                            description: description || null,
                            cd_type: 'debit',
                            amount: debit
                        });
                    }

                    // If credit has value, add credit entry
                    if (credit > 0) {
                        summaries.push({
                            head_name: headName,
                            dated: dated || null,
                            description: description || null,
                            cd_type: 'credit',
                            amount: credit
                        });
                    }
                }
            });

            if (summaries.length === 0) {
                submitBtn.prop('disabled', false).text(originalText);
                alert('Please enter at least one debit or credit amount to save.');
                return;
            }

            $.ajax({
                url: `/api/forms/${currentFormId}/cd-summary`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                data: {
                    summaries: summaries
                },
                success: function(response) {
                    submitBtn.prop('disabled', false).text(originalText);
                    alert('Credit/Debit summary saved successfully!');

                    // Refresh sidebar to show newly created heads
                    refreshSidebar();
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text(originalText);

                    let errorMessage = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('\n');
                    }

                    alert(errorMessage);
                }
            });
        }
    </script>
</body>

</html>
