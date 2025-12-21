<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Form - {{ config('app.name', 'Laravel') }}</title>

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
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Form</h1>
                    <a href="{{ route('forms.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24">
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

            @if ($errors->any())
                <div
                    class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex gap-6">
                <!-- Sidebar -->
                <aside class="w-80 flex-shrink-0">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">
                            Related Items
                        </h2>

                        <!-- Add Item and Export Buttons -->
                        <div class="mb-4 flex gap-2">
                            <button type="button" id="addItemBtn"
                                class="w-1/2 inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Item
                            </button>
                            <a type="button" id="exportBtn"
                                href="{{ route('forms.export-by-project', ['client_name' => $form->client_name, 'project_name' => $form->project_name]) }}"
                                class="w-1/2 inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Export
                            </a>
                        </div>

                        <div id="sidebarItemsList" class="space-y-3 max-h-[calc(100vh-300px)] overflow-y-auto">
                            @if ($relatedForms->count() > 0)
                                @foreach ($relatedForms as $relatedForm)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors cursor-pointer sidebar-item"
                                        data-form-id="{{ $relatedForm->id }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $relatedForm->item_name ?: 'No Item Name' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Created: {{ $relatedForm->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div id="emptySidebarMessage" class="text-center py-8">
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
                    <form action="{{ route('forms.store') }}" method="POST" id="formForm">
                        @csrf
                        <input type="hidden" name="item_name" id="item_name_input" value="">
                        <input type="hidden" name="is_new_item" id="is_new_item" value="0">

                        <!-- Form Details -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Form Details</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="client_name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Client Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="client_name" name="client_name"
                                        value="{{ old('client_name', $form->client_name) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="project_name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Project Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="project_name" name="project_name"
                                        value="{{ old('project_name', $form->project_name) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <!-- Message Section -->
                        <div id="messageSection"
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 mt-0.5 flex-shrink-0"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">
                                        Select an Item to Edit
                                    </h3>
                                    <p class="text-sm text-blue-800 dark:text-blue-300">
                                        Please select an item from the sidebar to edit it, or click on the <strong>"Add
                                            Item"</strong> button in the sidebar to create a new item.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Fields Section (will be populated via AJAX) -->
                        <div id="fieldsSection" class="hidden">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <div class="mb-4">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Fields</h2>

                                    <!-- Item Name and Unit Fields -->
                                    <div id="itemNameField" class="mb-6">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="fields_item_name"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Item Name
                                                </label>
                                                <input type="text" id="fields_item_name" name="fields_item_name"
                                                    placeholder="Enter item name"
                                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                            </div>
                                            <div>
                                                <label for="fields_unit"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Unit
                                                </label>
                                                <select id="fields_unit" name="fields_unit"
                                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select Unit</option>
                                                    <option value="CFT">CFT</option>
                                                    <option value="SFT">SFT</option>
                                                    <option value="RFT">RFT</option>
                                                    <option value="CUM">CUM</option>
                                                    <option value="SQM">SQM</option>
                                                    <option value="RM">RM</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fields Table -->
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-700">
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-12">
                                                    #</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider min-w-[300px]">
                                                    Description</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32">
                                                    Number</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32">
                                                    Length</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32">
                                                    Width</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32">
                                                    Height</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32 whitespace-nowrap">
                                                    T  QTY</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-20">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="fieldsTableBody">
                                            <!-- Fields will be populated here via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <!-- Fixed Form Actions -->
        <div
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-center space-x-4">
                    <a href="{{ route('forms.index') }}"
                        class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="button" onclick="addFieldRow()" id="addRowBtn"
                        class="hidden inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Add Row
                    </button>
                    <button type="submit" form="formForm" id="updateFormBtn"
                        class="hidden px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        Update Form
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let fieldIndex = 30;
        let currentFormId = null;

        // Handle sidebar item clicks
        $(document).ready(function() {
            // Delegate click handler for sidebar items (works for dynamically added items)
            $(document).on('click', '.sidebar-item', function() {
                const formId = $(this).data('form-id');
                loadFormFields(formId);

                // Update active state
                $('.sidebar-item').removeClass(
                    'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700');
                $('.sidebar-item').addClass('bg-gray-50 dark:bg-gray-700');
                $(this).removeClass('bg-gray-50 dark:bg-gray-700');
                $(this).addClass('bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700');
            });

            // Handle Add Item button click
            $('#addItemBtn').on('click', function() {
                loadEmptyFieldsForm();
            });

            // Handle form submission
            $('#formForm').on('submit', function(e) {
                e.preventDefault();
                submitForm();
            });
        });

        function loadFormFields(formId) {
            currentFormId = formId;

            // Show loading state
            $('#fieldsSection').addClass('hidden');
            $('#messageSection').html(`
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-blue-600 dark:text-blue-400">Loading fields...</span>
                </div>
            `);

            $.ajax({
                url: `/api/forms/${formId}/fields`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    renderFieldsTable(response);
                    $('#messageSection').addClass('hidden');
                    $('#fieldsSection').removeClass('hidden');

                    // Show action buttons
                    $('#addRowBtn').removeClass('hidden');
                    $('#updateFormBtn').removeClass('hidden');
                    $('#updateFormBtn').text('Update Form');

                    // Show and populate item name and unit fields for existing items
                    $('#itemNameField').removeClass('hidden');
                    $('#fields_item_name').val(response.item_name || '');
                    $('#fields_unit').val(response.unit || '');

                    // Update form for editing
                    $('#formForm').attr('action', `/forms/${formId}`);
                    $('#formForm').find('input[name="_method"]').remove();
                    $('#formForm').append('<input type="hidden" name="_method" value="PUT">');
                    $('#item_name_input').val(response.item_name || '');
                    $('#is_new_item').val('0');
                    currentFormId = formId;
                },
                error: function(xhr) {
                    $('#messageSection').html(`
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-1">
                                    Error Loading Fields
                                </h3>
                                <p class="text-sm text-red-800 dark:text-red-300">
                                    Failed to load fields. Please try again.
                                </p>
                            </div>
                        </div>
                    `);
                    $('#fieldsSection').addClass('hidden');
                }
            });
        }

        function renderFieldsTable(data) {
            const tbody = $('#fieldsTableBody');
            tbody.empty();

            data.fields.forEach(function(field, index) {
                const rowNum = index + 1;
                const row = `
                    <tr class="field-row border-b border-gray-200 dark:border-gray-700">
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${rowNum}</td>
                        <td class="px-4 py-3">
                            ${field.id ? `<input type="hidden" name="fields[${index}][id]" value="${field.id}">` : ''}
                            <textarea name="fields[${index}][description]" 
                                      rows="1"
                                      placeholder="Enter description"
                                      oninput="calculateProduct(this)"
                                      class="field-description w-full min-w-[300px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none">${field.description || ''}</textarea>
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="fields[${index}][quantity]" 
                                   step="1" 
                                   placeholder="0"
                                   oninput="calculateProduct(this)"
                                   value="${field.quantity || ''}"
                                   class="field-quantity w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="fields[${index}][length]" 
                                   step="0.01" 
                                   min="0"
                                   placeholder="0"
                                   oninput="calculateProduct(this)"
                                   value="${field.length || ''}"
                                   class="field-length w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="fields[${index}][width]" 
                                   step="0.01" 
                                   min="0"
                                   placeholder="0"
                                   oninput="calculateProduct(this)"
                                   value="${field.width || ''}"
                                   class="field-width w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="fields[${index}][height]" 
                                   step="0.01" 
                                   min="0"
                                   placeholder="0"
                                   oninput="calculateProduct(this)"
                                   value="${field.height || ''}"
                                   class="field-height w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="fields[${index}][product]" 
                                   step="0.01"
                                   placeholder="0"
                                   readonly
                                   value="${field.product || ''}"
                                   class="field-product w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-600 text-gray-700 dark:text-gray-300 cursor-not-allowed">
                        </td>
                        <td class="px-4 py-3">
                            <button type="button" 
                                    onclick="removeFieldRow(this)" 
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            // Recalculate products for existing values
            tbody.find('.field-quantity, .field-length, .field-width, .field-height').each(function() {
                if ($(this).val()) {
                    calculateProduct(this);
                }
            });
        }

        function calculateProduct(input) {
            const row = input.closest('tr');
            const quantity = parseFloat(row.querySelector('.field-quantity').value) || 1;
            const length = parseFloat(row.querySelector('.field-length').value) || 1;
            const width = parseFloat(row.querySelector('.field-width').value) || 1;
            const height = parseFloat(row.querySelector('.field-height').value) || 1;
            const productInput = row.querySelector('.field-product');

            // Calculate product if length, width, and height are greater than 0 (quantity can be negative)
            if (length > 0 && width > 0 && height > 0 && quantity !== 0) {
                const product = quantity * length * width * height;
                // Show original product value with up to 2 decimal places
                productInput.value = product.toFixed(2);
            } else {
                productInput.value = '';
            }
        }

        function loadEmptyFieldsForm() {
            currentFormId = null;

            // Reset form for new item
            $('#formForm').attr('action', '{{ route('forms.store') }}');
            $('#formForm').find('input[name="_method"]').remove();
            $('#item_name_input').val('');
            $('#is_new_item').val('1');

            // Show item name and unit fields for new items
            $('#itemNameField').removeClass('hidden');
            $('#fields_item_name').val('');
            $('#fields_unit').val('');

            // Generate 30 empty rows
            const emptyFields = [];
            for (let i = 0; i < 30; i++) {
                emptyFields.push({
                    id: null,
                    index: i,
                    description: '',
                    quantity: '',
                    length: '',
                    width: '',
                    height: '',
                    product: ''
                });
            }

            const emptyData = {
                form_id: null,
                item_name: '',
                fields: emptyFields
            };

            renderFieldsTable(emptyData);
            $('#messageSection').addClass('hidden');
            $('#fieldsSection').removeClass('hidden');
            $('#addRowBtn').removeClass('hidden');
            $('#updateFormBtn').removeClass('hidden');
            $('#updateFormBtn').text('Create Form');

            // Clear active state from sidebar items
            $('.sidebar-item').removeClass('bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700');
            $('.sidebar-item').addClass('bg-gray-50 dark:bg-gray-700');
        }

        function submitForm() {
            const isNewItem = $('#is_new_item').val() === '1';
            const url = isNewItem ? '{{ route('forms.store') }}' : $('#formForm').attr('action');

            // Show loading state
            const submitBtn = $('#updateFormBtn');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Saving...');

            // Collect form data
            const formData = new FormData();
            formData.append('client_name', $('#client_name').val());
            formData.append('project_name', $('#project_name').val());

            // Get item name and unit from the visible fields (works for both new and existing items)
            const itemName = $('#fields_item_name').val();
            const unit = $('#fields_unit').val();
            formData.append('item_name', itemName);
            formData.append('unit', unit);

            // Collect fields data
            $('#fieldsTableBody tr').each(function(index) {
                const row = $(this);
                const description = row.find('.field-description').val() || '';
                const quantity = row.find('.field-quantity').val() || '';
                const length = row.find('.field-length').val() || '';
                const width = row.find('.field-width').val() || '';
                const height = row.find('.field-height').val() || '';
                const product = row.find('.field-product').val() || '';
                const fieldId = row.find('input[name*="[id]"]').val();

                // Only include fields with data
                if (description || quantity || length || width || height || product) {
                    if (fieldId) {
                        formData.append(`fields[${index}][id]`, fieldId);
                    }
                    formData.append(`fields[${index}][description]`, description);
                    formData.append(`fields[${index}][quantity]`, quantity);
                    formData.append(`fields[${index}][length]`, length);
                    formData.append(`fields[${index}][width]`, width);
                    formData.append(`fields[${index}][height]`, height);
                    formData.append(`fields[${index}][product]`, product);
                }
            });

            if (!isNewItem) {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    submitBtn.prop('disabled', false).text(originalText);

                    if (isNewItem) {
                        // Refresh sidebar to include new item
                        refreshSidebar();
                        // Load the newly created form
                        loadFormFields(response.id);
                        // Show success message
                        showSuccessMessage('Item created successfully!');
                    } else {
                        // Refresh sidebar to show updated item name
                        refreshSidebar(currentFormId);
                        // Show success message
                        showSuccessMessage(response.message || 'Form updated successfully!');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).text(originalText);
                    let errorMessage = 'An error occurred. Please try again.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('\\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }

                    alert(errorMessage);
                }
            });
        }

        function refreshSidebar(selectedFormId = null) {
            const clientName = $('#client_name').val();
            const projectName = $('#project_name').val();

            if (!clientName || !projectName) {
                return;
            }

            $.ajax({
                url: '{{ route('api.sidebar-items') }}',
                method: 'GET',
                data: {
                    client_name: clientName,
                    project_name: projectName
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(items) {
                    const sidebarList = $('#sidebarItemsList');
                    sidebarList.empty();

                    if (items.length === 0) {
                        sidebarList.html(
                            '<div id="emptySidebarMessage" class="text-center py-8"><p class="text-sm text-gray-500 dark:text-gray-400">No items found.</p></div>'
                        );
                        return;
                    }

                    items.forEach(function(item) {
                        const itemName = item.item_name || 'No Item Name';
                        const createdAt = new Date(item.created_at).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                        const isSelected = selectedFormId && item.id == selectedFormId;
                        const bgClass = isSelected ?
                            'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700' :
                            'bg-gray-50 dark:bg-gray-700';

                        const itemHtml = `
                            <div class="p-3 ${bgClass} rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors cursor-pointer sidebar-item"
                                 data-form-id="${item.id}">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            ${itemName}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Created: ${createdAt}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;

                        sidebarList.append(itemHtml);
                    });
                },
                error: function() {
                    console.error('Failed to refresh sidebar');
                }
            });
        }

        function showSuccessMessage(message) {
            const successMsg = $(`
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
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

        function addFieldRow() {
            if (!currentFormId && $('#is_new_item').val() !== '1') {
                alert('Please select an item first or click "Add Item"');
                return;
            }

            const tbody = document.getElementById('fieldsTableBody');
            const currentRowCount = tbody.querySelectorAll('tr').length;
            const newIndex = currentRowCount;
            const row = document.createElement('tr');
            row.className = 'field-row border-b border-gray-200 dark:border-gray-700';
            row.innerHTML = `
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${newIndex + 1}</td>
                <td class="px-4 py-3">
                    <textarea name="fields[${newIndex}][description]" 
                              rows="1"
                              placeholder="Enter description"
                              oninput="calculateProduct(this)"
                              class="field-description w-full min-w-[300px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"></textarea>
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${newIndex}][quantity]" 
                           step="1" 
                           placeholder="0"
                           oninput="calculateProduct(this)"
                           class="field-quantity w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${newIndex}][length]" 
                           step="0.01" 
                           min="0"
                           placeholder="0"
                           oninput="calculateProduct(this)"
                           class="field-length w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${newIndex}][width]" 
                           step="0.01" 
                           min="0"
                           placeholder="0"
                           oninput="calculateProduct(this)"
                           class="field-width w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${newIndex}][height]" 
                           step="0.01" 
                           min="0"
                           placeholder="0"
                           oninput="calculateProduct(this)"
                           class="field-height w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${newIndex}][product]" 
                           step="0.01"
                           placeholder="0"
                           readonly
                           class="field-product w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-600 text-gray-700 dark:text-gray-300 cursor-not-allowed">
                </td>
                <td class="px-4 py-3">
                    <button type="button" 
                            onclick="removeFieldRow(this)" 
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            updateRowNumbers();
        }

        function removeFieldRow(button) {
            const row = button.closest('tr');
            // Clear all inputs in the row
            row.querySelectorAll('input[type="number"], textarea').forEach(input => {
                input.value = '';
            });
            // If there's a hidden ID field, we'll keep the row but mark it for deletion
            const hiddenId = row.querySelector('input[type="hidden"][name*="[id]"]');
            if (hiddenId) {
                // Optionally, you could add a hidden delete flag here
            }
        }

        function updateRowNumbers() {
            const rows = document.querySelectorAll('#fieldsTableBody tr');
            rows.forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
            });
        }

        // Auto-resize textareas
        document.addEventListener('input', function(e) {
            if (e.target.tagName === 'TEXTAREA') {
                e.target.style.height = 'auto';
                e.target.style.height = e.target.scrollHeight + 'px';
            }
        });

        // Allow navigation and editing keys (backspace, delete, arrow keys, etc.)
        document.addEventListener('keydown', function(e) {
            if (e.target.type === 'number' && e.target.readOnly === false) {
                // Allow: backspace, delete, tab, escape, enter, arrow keys, home, end
                if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp',
                        'ArrowDown', 'Home', 'End'
                    ].includes(e.key) ||
                    // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    ((e.key === 'a' || e.key === 'c' || e.key === 'v' || e.key === 'x') && e.ctrlKey)) {
                    return;
                }
            }
        });

        // Ensure only numbers and "." can be entered in number inputs
        document.addEventListener('keypress', function(e) {
            if (e.target.type === 'number' && e.target.readOnly === false) {
                // Allow decimal point
                if (e.key === '.' || e.key === '-') {
                    // Prevent if there's already a decimal point in the value
                    if (e.target.value.includes('.')) {
                        e.preventDefault();
                    }
                    return;
                }
                // Allow numbers (0-9)
                if (e.key >= '0' && e.key <= '9') {
                    return;
                }
                // Prevent all other characters
                e.preventDefault();
            }
        });

        // Prevent non-numeric characters on paste
        document.addEventListener('paste', function(e) {
            if (e.target.type === 'number' && e.target.readOnly === false) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const numericValue = paste.replace(/[^0-9.]/g, '');
                e.target.value = numericValue;
                // Trigger input event to recalculate product if needed
                if (e.target.classList.contains('field-quantity') ||
                    e.target.classList.contains('field-length') ||
                    e.target.classList.contains('field-width') ||
                    e.target.classList.contains('field-height')) {
                    calculateProduct(e.target);
                }
            }
        });

        // Calculate product for existing rows on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.field-quantity, .field-length, .field-width, .field-height').forEach(
                input => {
                    if (input.value) {
                        calculateProduct(input);
                    }
                });
        });
    </script>
</body>

</html>
