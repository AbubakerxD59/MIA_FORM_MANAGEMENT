<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BBS - {{ config('app.name', 'Laravel') }}</title>

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

        .toggle-icon {
            transition: transform 0.3s ease;
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
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Bar Bending Schedule</h1>
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

                        <!-- Add Item Button -->
                        <div class="mb-4">
                            <button type="button" id="addItemBtn"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <div id="sidebarItemsList" class="space-y-3 max-h-[calc(100vh-300px)] overflow-y-auto">
                            @if ($barBendingFormItems->count() > 0)
                                @foreach ($barBendingFormItems as $barBendingFormItem)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors sidebar-item"
                                        data-item-id="{{ $barBendingFormItem->id }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 cursor-pointer"
                                                onclick="loadBarBendingItem({{ $barBendingFormItem->id }})">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $barBendingFormItem->name ?: 'No Name' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Created: {{ $barBendingFormItem->created_at->format('M d, Y') }}
                                                </p>
                                            </div>
                                            <button type="button"
                                                onclick="event.stopPropagation(); confirmDeleteItem({{ $barBendingFormItem->id }}, '{{ addslashes($barBendingFormItem->name ?: 'No Name') }}')"
                                                class="ml-2 p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                                title="Delete item">
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

                        <!-- Item Section (for adding new bar bending form items) -->
                        <div id="itemSection" class="hidden">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                <div class="mb-4">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Item</h2>
                                    <div class="max-w-md">
                                        <label for="bar_bending_item_name"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Name <span class="text-red-500">*</span>
                                        </label>
                                        <div class="flex gap-2">
                                            <input type="text" id="bar_bending_item_name"
                                                name="bar_bending_item_name" placeholder="Enter item name" required
                                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                            <button type="button" id="saveItemNameBtnAdd"
                                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 whitespace-nowrap">
                                                Save Item
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Name and Item Name Fields (shown when item is selected) -->
                        <div id="itemNameField" class="hidden">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="fields_item_name"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Item Name
                                        </label>
                                        <div class="flex gap-2">
                                            <input type="text" id="fields_item_name" name="fields_item_name"
                                                placeholder="Enter item name"
                                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                            <button type="button" id="saveItemNameBtn"
                                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 whitespace-nowrap">
                                                Save Item
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="fields_location_name"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Location Name
                                        </label>
                                        <div class="flex gap-2 relative">
                                            <input type="text" id="fields_location_name"
                                                name="fields_location_name" placeholder="Enter location name"
                                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                            <button type="button" id="addLocationBtn"
                                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 whitespace-nowrap">
                                                Add Location
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Details Table (shown when location is active) -->
                        <div id="locationDetailsTable" class="hidden">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Location Details
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-700">
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Span</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Number</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Width</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Height</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    Length</th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                    No. of Unit</th>
                                                <th
                                                    class="px-4 py-3 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-20">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="locationDetailsTableBody">
                                            <!-- Rows will be populated via JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <!-- Delete Item Confirmation Modal -->
        <div id="deleteItemModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div
                            class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="ml-4 text-xl font-bold text-gray-900 dark:text-white">Confirm Delete</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-6" id="deleteItemModalMessage">
                        Are you sure you want to delete this item? This action cannot be undone and all associated
                        fields will be deleted.
                    </p>
                    <div class="flex justify-end space-x-3">
                        <button id="cancelDeleteItem"
                            class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium">
                            Cancel
                        </button>
                        <button id="confirmDeleteItem"
                            class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all font-medium shadow-lg">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Form Actions -->
        <div
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-center space-x-4">
                    <button type="button" id="addSpanBtn"
                        class="hidden px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        <span>Add Span</span>
                    </button>
                    <a href="{{ route('forms.index') }}"
                        class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="button" id="saveBarBendingItemBtn"
                        class="hidden px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let fieldIndex = 30;
        let currentItemId = null;
        let currentLocationId = null;
        const currentFormId = {{ $form->id }};

        // Handle sidebar item clicks
        $(document).ready(function() {
            // Delegate click handler for sidebar items (works for dynamically added items)
            // Note: Click handling is now done via onclick in the item div to allow delete button clicks

            // Handle Add Item button click
            $('#addItemBtn').on('click', function() {
                loadEmptyBarBendingItem();
            });

            // Handle Save Bar Bending Item button click
            $('#saveBarBendingItemBtn').on('click', function() {
                saveBarBendingFormItem();
            });

            // Handle duplicate checkbox change
            $('#duplicateCheckbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#duplicateDropdownContainer').removeClass('hidden');
                    loadSidebarItemsForDuplicate();
                } else {
                    $('#duplicateDropdownContainer').addClass('hidden');
                    $('#duplicateItemSelect').val('');
                    $('#duplicateButton').prop('disabled', true);
                }
            });

            // Handle duplicate item select change
            $('#duplicateItemSelect').on('change', function() {
                const selectedValue = $(this).val();
                $('#duplicateButton').prop('disabled', !selectedValue);
            });

            // Handle duplicate button click
            $('#duplicateButton').on('click', function() {
                const selectedFormId = $('#duplicateItemSelect').val();
                if (selectedFormId) {
                    duplicateItemFields(selectedFormId);
                }
            });

        });

        function loadBarBendingItem(itemId) {
            currentItemId = itemId;
            currentLocationId = null; // Reset location when loading a new item

            // Hide location details table when loading a new item
            hideLocationDetailsTable();

            // Update active state
            $('.sidebar-item').removeClass(
                'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700');
            $('.sidebar-item').addClass('bg-gray-50 dark:bg-gray-700');
            $(`.sidebar-item[data-item-id="${itemId}"]`).removeClass('bg-gray-50 dark:bg-gray-700');
            $(`.sidebar-item[data-item-id="${itemId}"]`).addClass(
                'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700');

            // Hide item section (for adding new items) and show item name field section
            $('#itemSection').addClass('hidden');
            $('#messageSection').addClass('hidden');
            $('#itemNameField').removeClass('hidden');

            // Disable inputs while loading
            $('#fields_location_name, #fields_item_name').prop('disabled', true);

            // Load bar bending item details
            $.ajax({
                url: `/api/bar-bending-form-items/${itemId}`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    // Populate location name and item name fields
                    if (response.location && response.location.name) {
                        $('#fields_location_name').val(response.location.name);
                    } else {
                        $('#fields_location_name').val('');
                    }

                    if (response.name) {
                        $('#fields_item_name').val(response.name);
                    } else {
                        $('#fields_item_name').val('');
                    }

                    // Re-enable inputs
                    $('#fields_location_name, #fields_item_name').prop('disabled', false);

                    // Show item name field section
                    $('#messageSection').addClass('hidden');
                    $('#itemNameField').removeClass('hidden');

                    // Refresh sidebar to show locations submenu for the selected item
                    refreshSidebar(itemId, currentLocationId);
                },
                error: function(xhr) {
                    console.error('Error loading bar bending item:', xhr);
                    $('#messageSection').removeClass('hidden');
                    $('#itemNameField').addClass('hidden');
                    $('#messageSection').html(`
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-1">
                                    Error Loading Item
                                </h3>
                                <p class="text-sm text-red-800 dark:text-red-300">
                                    Failed to load item details. Please try again.
                                </p>
                            </div>
                        </div>
                    `);
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

        function loadEmptyBarBendingItem() {
            currentItemId = null;
            currentLocationId = null;

            // Hide message section and item name field section (for editing existing items)
            $('#messageSection').addClass('hidden');
            $('#itemNameField').addClass('hidden');

            // Hide location details table
            hideLocationDetailsTable();

            // Show item section (for adding new items)
            $('#itemSection').removeClass('hidden');
            $('#bar_bending_item_name').val('');

            // Show save button
            $('#saveBarBendingItemBtn').removeClass('hidden');

            // Clear active state from sidebar items
            $('.sidebar-item').removeClass('bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700');
            $('.sidebar-item').addClass('bg-gray-50 dark:bg-gray-700');
        }

        function saveBarBendingFormItem() {
            const itemName = $('#bar_bending_item_name').val().trim();

            if (!itemName) {
                alert('Please enter an item name');
                return;
            }

            const saveBtn = $('#saveBarBendingItemBtn');
            const originalText = saveBtn.text();
            saveBtn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: `/api/bar-bending-form-items`,
                method: 'POST',
                data: {
                    form_id: currentFormId,
                    name: itemName,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    saveBtn.prop('disabled', false).text(originalText);

                    // Clear the form and hide item section
                    $('#bar_bending_item_name').val('');
                    $('#itemSection').addClass('hidden');
                    $('#saveBarBendingItemBtn').addClass('hidden');
                    $('#messageSection').removeClass('hidden');

                    // Refresh sidebar to show the new item
                    refreshSidebar();

                    // Show success message
                    showSuccessMessage('Bar bending form item created successfully!');
                },
                error: function(xhr) {
                    saveBtn.prop('disabled', false).text(originalText);

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
                        refreshSidebar(null, null);
                        // Show success message
                        showSuccessMessage('Item created successfully!');
                    } else {
                        // Refresh sidebar to show updated item name
                        refreshSidebar(currentItemId, currentLocationId);
                        // Show success message
                        showSuccessMessage(response.message || 'Item updated successfully!');
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

        function refreshSidebar(selectedItemId = null, selectedLocationId = null, callback = null) {
            $.ajax({
                url: `/api/forms/${currentFormId}/bar-bending-items`,
                method: 'GET',
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
                        if (callback) callback();
                        return;
                    }

                    items.forEach(function(item) {
                        const itemName = item.name || 'No Name';
                        const createdAt = new Date(item.created_at).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                        const isSelected = selectedItemId && item.id == selectedItemId;
                        const bgClass = isSelected ?
                            'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700' :
                            'bg-gray-50 dark:bg-gray-700';

                        // Build locations submenu HTML
                        let locationsHtml = '';
                        if (isSelected && item.locations && item.locations.length > 0) {
                            locationsHtml =
                                '<div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">';
                            locationsHtml += '<ul class="space-y-1">';
                            item.locations.forEach(function(location) {
                                const isLocationActive = selectedLocationId && location.id ==
                                    selectedLocationId;
                                const locationBgClass = isLocationActive ?
                                    'bg-blue-200 dark:bg-blue-800 text-blue-900 dark:text-blue-100 font-medium' :
                                    'text-gray-700 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900/30';

                                locationsHtml += `
                                    <li class="location-submenu-item cursor-pointer text-xs pl-4 py-1.5 rounded transition-colors ${locationBgClass}"
                                        data-location-id="${location.id}"
                                        data-location-name="${location.name.replace(/'/g, "\\'")}"
                                        onclick="event.stopPropagation(); loadLocation(${location.id}, '${location.name.replace(/'/g, "\\'")}')">
                                        ${location.name}
                                    </li>
                                `;
                            });
                            locationsHtml += '</ul>';
                            locationsHtml += '</div>';
                        } else if (isSelected && (!item.locations || item.locations.length === 0)) {
                            locationsHtml =
                                '<div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">';
                            locationsHtml +=
                                '<p class="text-xs text-gray-500 dark:text-gray-400 italic pl-4">No locations added</p>';
                            locationsHtml += '</div>';
                        }

                        const itemHtml = `
                            <div class="p-3 ${bgClass} rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors sidebar-item"
                                 data-item-id="${item.id}">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 cursor-pointer" onclick="loadBarBendingItem(${item.id})">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            ${itemName}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Created: ${createdAt}
                                        </p>
                                        ${locationsHtml}
                                    </div>
                                    <button type="button" 
                                        onclick="event.stopPropagation(); confirmDeleteItem(${item.id}, '${itemName.replace(/'/g, "\\'")}')"
                                        class="ml-2 p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                        title="Delete item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        `;

                        sidebarList.append(itemHtml);
                    });

                    // Execute callback after sidebar is refreshed
                    if (callback) {
                        callback();
                    }
                },
                error: function() {
                    console.error('Failed to refresh sidebar');
                    if (callback) callback();
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

        function loadSidebarItemsForDuplicate() {
            const clientName = $('#client_name').val();
            const projectName = $('#project_name').val();

            if (!clientName || !projectName) {
                alert('Please fill in Client Name and Project Name first');
                $('#duplicateCheckbox').prop('checked', false);
                $('#duplicateDropdownContainer').addClass('hidden');
                return;
            }

            const select = $('#duplicateItemSelect');
            select.html('<option value="">Loading items...</option>');

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
                    select.html('<option value="">-- Select an item --</option>');

                    if (items.length === 0) {
                        select.html('<option value="">No items available</option>');
                        return;
                    }

                    items.forEach(function(item) {
                        const itemName = item.item_name || 'No Item Name';
                        const option = $('<option></option>')
                            .attr('value', item.id)
                            .text(itemName);
                        select.append(option);
                    });
                },
                error: function() {
                    select.html('<option value="">Error loading items</option>');
                    console.error('Failed to load sidebar items for duplicate');
                }
            });
        }

        function duplicateItemFields(formId) {
            // Show loading state
            const duplicateButton = $('#duplicateButton');
            const originalText = duplicateButton.text();
            duplicateButton.prop('disabled', true).text('Loading...');

            $.ajax({
                url: `/api/forms/${formId}/fields`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Populate item name and unit
                    if (response.item_name) {
                        $('#fields_item_name').val(response.item_name);
                    }
                    if (response.unit) {
                        $('#fields_unit').val(response.unit);
                    }

                    // Remove field IDs from duplicated fields so they're treated as new fields
                    const duplicatedResponse = {
                        form_id: null,
                        item_name: response.item_name,
                        unit: response.unit,
                        fields: response.fields.map(function(field) {
                            return {
                                id: null, // Remove ID so it's treated as a new field
                                index: field.index,
                                description: field.description || '',
                                quantity: field.quantity || '',
                                length: field.length || '',
                                width: field.width || '',
                                height: field.height || '',
                                product: field.product || ''
                            };
                        })
                    };

                    // Render the fields table with duplicated data (without IDs)
                    renderFieldsTable(duplicatedResponse);

                    duplicateButton.prop('disabled', false).text(originalText);

                    // Show success message
                    showSuccessMessage('Fields duplicated successfully!');
                },
                error: function(xhr) {
                    duplicateButton.prop('disabled', false).text(originalText);
                    alert('Failed to load fields from selected item. Please try again.');
                    console.error('Error loading form fields:', xhr);
                }
            });
        }

        function addFieldRow() {
            if (!currentItemId) {
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

        // Delete item functionality
        let itemToDeleteId = null;

        function confirmDeleteItem(formId, itemName) {
            itemToDeleteId = formId;
            const modal = document.getElementById('deleteItemModal');
            const message = document.getElementById('deleteItemModalMessage');
            message.textContent =
                `Are you sure you want to delete the item "${itemName}"? This action cannot be undone and all associated fields will be deleted.`;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.querySelector('div').classList.add('scale-100');
            }, 10);
        }

        // Cancel delete item
        document.getElementById('cancelDeleteItem').addEventListener('click', function() {
            const modal = document.getElementById('deleteItemModal');
            modal.querySelector('div').classList.remove('scale-100');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                itemToDeleteId = null;
            }, 200);
        });

        // Close modal on outside click
        document.getElementById('deleteItemModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.querySelector('div').classList.remove('scale-100');
                setTimeout(() => {
                    this.classList.add('hidden');
                    this.classList.remove('flex');
                    itemToDeleteId = null;
                }, 200);
            }
        });

        // Confirm delete item
        document.getElementById('confirmDeleteItem').addEventListener('click', function() {
            if (!itemToDeleteId) {
                return;
            }

            const confirmBtn = this;
            const originalText = confirmBtn.textContent;
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Deleting...';

            $.ajax({
                url: `/api/bar-bending-form-items/${itemToDeleteId}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    const modal = document.getElementById('deleteItemModal');
                    modal.querySelector('div').classList.remove('scale-100');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }, 200);

                    // Refresh sidebar
                    refreshSidebar(null, null);

                    // If the deleted item was currently being edited, clear the form
                    if (currentItemId === itemToDeleteId) {
                        currentItemId = null;
                        currentLocationId = null;
                        $('#messageSection').removeClass('hidden');
                        $('#itemNameField').addClass('hidden');
                    }

                    // Show success message
                    showSuccessMessage('Item deleted successfully!');

                    itemToDeleteId = null;
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = originalText;
                },
                error: function(xhr) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = originalText;

                    let errorMessage = 'Failed to delete item. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    alert(errorMessage);
                    console.error('Error deleting item:', xhr);
                }
            });
        });

        // Save Item Name button handler (for Fields section - only works when item is active)
        document.getElementById('saveItemNameBtn').addEventListener('click', function() {
            // Only allow saving when an item is active from sidebar
            if (!currentItemId) {
                alert('Please select an item from the sidebar first');
                return;
            }

            const itemName = $('#fields_item_name').val().trim();
            const formId = {{ $form->id }};

            if (!itemName) {
                alert('Please enter an item name');
                return;
            }

            const saveBtn = this;
            const originalText = saveBtn.textContent;
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            // Store the current item ID before refresh
            const itemIdToReselect = currentItemId;

            $.ajax({
                url: '/api/bar-bending-form-items/update-name',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    form_id: formId,
                    name: itemName,
                    item_id: currentItemId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;

                    // Refresh sidebar and reselect the activated item
                    refreshSidebar(itemIdToReselect, currentLocationId, function() {
                        // After sidebar refresh, reload the item to update the fields
                        loadBarBendingItem(itemIdToReselect);
                    });

                    showSuccessMessage(response.message || 'Item name saved successfully!');
                },
                error: function(xhr) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;

                    let errorMessage = 'Failed to save item name. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    alert(errorMessage);
                    console.error('Error saving item name:', xhr);
                }
            });
        });

        // Save Item Name button handler for Add Item section
        document.getElementById('saveItemNameBtnAdd').addEventListener('click', function() {
            const itemName = $('#bar_bending_item_name').val().trim();
            const formId = {{ $form->id }};

            if (!itemName) {
                alert('Please enter an item name');
                return;
            }

            const saveBtn = this;
            const originalText = saveBtn.textContent;
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            $.ajax({
                url: '/api/bar-bending-form-items/update-name',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    form_id: formId,
                    name: itemName
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;

                    // Clear the input field
                    $('#bar_bending_item_name').val('');

                    // Refresh sidebar to show the new item
                    refreshSidebar();

                    showSuccessMessage(response.message || 'Item name saved successfully!');
                },
                error: function(xhr) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalText;

                    let errorMessage = 'Failed to save item name. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    alert(errorMessage);
                    console.error('Error saving item name:', xhr);
                }
            });
        });

        // Location Name Autocomplete
        let locationAutocompleteTimeout;
        let locationAutocompleteList = null;

        $('#fields_location_name').on('input', function() {
            const input = $(this);
            const query = input.val().trim();

            // Clear previous timeout
            clearTimeout(locationAutocompleteTimeout);

            // Remove existing autocomplete list
            if (locationAutocompleteList) {
                locationAutocompleteList.remove();
                locationAutocompleteList = null;
            }

            // If query is empty or too short, don't search
            if (query.length < 1) {
                return;
            }

            // Debounce the search
            locationAutocompleteTimeout = setTimeout(function() {
                $.ajax({
                    url: '/api/locations',
                    method: 'GET',
                    data: {
                        q: query
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(locations) {
                        // Remove existing autocomplete list
                        if (locationAutocompleteList) {
                            locationAutocompleteList.remove();
                        }

                        if (locations.length === 0) {
                            return;
                        }

                        // Create autocomplete dropdown
                        const inputContainer = input.closest('.flex');
                        const inputHeight = input.outerHeight();
                        const inputWidth = input.outerWidth();

                        locationAutocompleteList = $(
                            '<ul class="location-autocomplete-list absolute z-50 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"></ul>'
                        );
                        locationAutocompleteList.css({
                            top: inputHeight + 2,
                            left: 0,
                            width: inputWidth
                        });

                        locations.forEach(function(location) {
                            const item = $(
                                '<li class="px-4 py-2 hover:bg-blue-50 dark:hover:bg-gray-700 cursor-pointer text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 last:border-b-0"></li>'
                            );
                            item.text(location.name);
                            item.data('location-id', location.id);
                            item.data('location-name', location.name);

                            item.on('click', function() {
                                input.val(location.name);
                                input.data('location-id', location.id);
                                locationAutocompleteList.remove();
                                locationAutocompleteList = null;
                            });

                            locationAutocompleteList.append(item);
                        });

                        inputContainer.append(locationAutocompleteList);
                    },
                    error: function(xhr) {
                        console.error('Error fetching locations:', xhr);
                    }
                });
            }, 300); // 300ms debounce
        });

        // Close autocomplete when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#fields_location_name, .location-autocomplete-list').length) {
                if (locationAutocompleteList) {
                    locationAutocompleteList.remove();
                    locationAutocompleteList = null;
                }
            }
        });

        // Close autocomplete on escape key
        $('#fields_location_name').on('keydown', function(e) {
            if (e.key === 'Escape' && locationAutocompleteList) {
                locationAutocompleteList.remove();
                locationAutocompleteList = null;
            }
        });

        // Add Location button handler
        document.getElementById('addLocationBtn').addEventListener('click', function() {
            // Only allow adding location when an item is active from sidebar
            if (!currentItemId) {
                alert('Please select an item from the sidebar first');
                return;
            }

            const locationName = $('#fields_location_name').val().trim();
            const formId = {{ $form->id }};

            if (!locationName) {
                alert('Please enter a location name');
                return;
            }

            const addBtn = this;
            const originalText = addBtn.textContent;
            addBtn.disabled = true;
            addBtn.textContent = 'Adding...';

            $.ajax({
                url: '/api/bar-bending-form-locations',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    form_id: formId,
                    item_id: currentItemId,
                    location_name: locationName
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    addBtn.disabled = false;
                    addBtn.textContent = originalText;

                    // Set the newly added location as active
                    if (response.location && response.location.id) {
                        currentLocationId = response.location.id;
                        // Set the location name in the input field
                        $('#fields_location_name').val(response.location.name);
                        $('#fields_location_name').data('location-id', response.location.id);

                        // Show location details table
                        showLocationDetailsTable();
                    } else {
                        // Clear the location name input if no location was returned
                        $('#fields_location_name').val('');
                    }

                    // Refresh sidebar to show the new location in submenu and make it active
                    refreshSidebar(currentItemId, currentLocationId);

                    showSuccessMessage(response.message || 'Location added successfully!');
                },
                error: function(xhr) {
                    addBtn.disabled = false;
                    addBtn.textContent = originalText;

                    let errorMessage = 'Failed to add location. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }

                    // Show error message (could be "location already added to the active item")
                    if (xhr.status === 400 && xhr.responseJSON && xhr.responseJSON.message) {
                        alert(xhr.responseJSON.message);
                    } else {
                        alert(errorMessage);
                    }
                    console.error('Error adding location:', xhr);
                }
            });
        });

        // Load location function (for clicking on location submenu items)
        function loadLocation(locationId, locationName) {
            // Set current location as active
            currentLocationId = locationId;

            // Update location name field
            $('#fields_location_name').val(locationName);
            $('#fields_location_name').data('location-id', locationId);

            // Show location details table
            showLocationDetailsTable();

            // Refresh sidebar to highlight the active location
            refreshSidebar(currentItemId, currentLocationId);
        }

        // Show location details table with default 10 rows
        function showLocationDetailsTable() {
            const tbody = $('#locationDetailsTableBody');
            tbody.empty();

            // Create 10 default rows with child tables
            for (let i = 0; i < 10; i++) {
                const row = `
                    <tr class="border-b border-gray-200 dark:border-gray-700 parent-row" data-row-index="${i}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        class="toggle-child-table-btn flex-shrink-0 w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                        data-row-index="${i}"
                                        title="Toggle child table">
                                    <svg class="w-5 h-5 toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <input type="text" 
                                       name="location_details[${i}][span]"
                                       placeholder="Enter span"
                                       class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="location_details[${i}][number]"
                                   step="1"
                                   placeholder="0"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="location_details[${i}][width]"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="location_details[${i}][height]"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" 
                                   name="location_details[${i}][length]"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                    <td class="px-4 py-3">
                        <input type="number" 
                               name="location_details[${i}][no_of_units]"
                               step="1"
                               min="0"
                               placeholder="0"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-4 py-3">
                        <button type="button" 
                                class="delete-span-btn w-8 h-8 flex items-center justify-center text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                data-row-index="${i}"
                                title="Delete span">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 child-table-row" data-row-index="${i}" style="display: none;">
                        <td colspan="7" class="px-4 py-4">
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs border border-gray-300 dark:border-gray-600">
                                    <thead>
                                        <tr class="bg-gray-200 dark:bg-gray-700">
                                            <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">CHILD SPAN</th>
                                            <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">SPACING</th>
                                            <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">DIA</th>
                                            <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">NO OF UNIT</th>
                                            <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">NO PER UNIT</th>
                                            <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">TOTAL NO</th>
                                            <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">CUT LENGTH</th>
                                            <th class="px-2 py-2 text-center font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600" colspan="5">TOTAL LENGTH</th>
                                        </tr>
                                        <tr class="bg-gray-200 dark:bg-gray-700">
                                            <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                            <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                            <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                            <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                            <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                            <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                            <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                            <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">3/8"<br><span class="text-xs">10</span></th>
                                            <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">1/2"<br><span class="text-xs">12</span></th>
                                            <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">5/8"<br><span class="text-xs">16</span></th>
                                            <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">3/4"<br><span class="text-xs">20</span></th>
                                            <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">1"<br><span class="text-xs">25</span></th>
                                        </tr>
                                    </thead>
                                    <tbody id="childTableBody_${i}">
                                        <!-- Child table rows will be added here -->
                                    </tbody>
                                </table>
                                <button type="button" 
                                        class="add-child-row-btn mt-2 px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 flex items-center gap-1"
                                        data-row-index="${i}"
                                        title="Add row">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span>Add Row</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);

                // Add 5 default rows to the child table
                const childTableBody = $(`#childTableBody_${i}`);
                for (let j = 0; j < 5; j++) {
                    const childRow = `
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="text" 
                                   name="location_details[${i}][child_rows][${j}][location]"
                                   placeholder="Child Span"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="text" 
                                   name="location_details[${i}][child_rows][0][spacing]"
                                   placeholder="Spacing"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="text" 
                                   name="location_details[${i}][child_rows][${j}][dia]"
                                   placeholder="DIA"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][no_of_unit]"
                                   step="1"
                                   placeholder="0"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][no_per_unit]"
                                   step="1"
                                   placeholder="0"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][total_no]"
                                   step="1"
                                   placeholder="0"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][cut_length]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][total_length_3_8]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][total_length_1_2]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][total_length_5_8]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][total_length_3_4]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${i}][child_rows][${j}][total_length_1]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                    </tr>
                `;
                    childTableBody.append(childRow);
                }

                // Show the table
                $('#locationDetailsTable').removeClass('hidden');

                // Show floating Add Span button
                $('#addSpanBtn').removeClass('hidden');

                // Attach toggle handlers to all toggle buttons
                attachToggleHandlers();
            }
        }

        // Attach toggle handlers to child table toggle buttons
        function attachToggleHandlers() {
            $(document).off('click', '.toggle-child-table-btn').on('click', '.toggle-child-table-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const rowIndex = $(this).data('row-index');
                const childTableRow = $(`.child-table-row[data-row-index="${rowIndex}"]`);
                const toggleIcon = $(this).find('.toggle-icon');

                if (childTableRow.is(':visible')) {
                    // Slide up (close)
                    childTableRow.slideUp(300, function() {
                        toggleIcon.css('transform', 'rotate(0deg)');
                    });
                } else {
                    // Slide down (open)
                    childTableRow.slideDown(300);
                    toggleIcon.css('transform', 'rotate(180deg)');
                }
            });
        }

        // Hide location details table
        function hideLocationDetailsTable() {
            $('#locationDetailsTable').addClass('hidden');
            // Hide floating Add Span button
            $('#addSpanBtn').addClass('hidden');
        }

        // Add a new parent row (span) to the location details table
        function addNewSpanRow() {
            const tbody = $('#locationDetailsTableBody');
            const currentRowCount = tbody.find('.parent-row').length;
            const newRowIndex = currentRowCount;

            // Create parent row with child table
            const row = `
                <tr class="border-b border-gray-200 dark:border-gray-700 parent-row" data-row-index="${newRowIndex}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    class="toggle-child-table-btn flex-shrink-0 w-8 h-8 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                    data-row-index="${newRowIndex}"
                                    title="Toggle child table">
                                <svg class="w-5 h-5 toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <input type="text" 
                                   name="location_details[${newRowIndex}][span]"
                                   placeholder="Enter span"
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" 
                               name="location_details[${newRowIndex}][number]"
                               step="1"
                               placeholder="0"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" 
                               name="location_details[${newRowIndex}][width]"
                               step="0.01"
                               min="0"
                               placeholder="0.00"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" 
                               name="location_details[${newRowIndex}][height]"
                               step="0.01"
                               min="0"
                               placeholder="0.00"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" 
                               name="location_details[${newRowIndex}][length]"
                               step="0.01"
                               min="0"
                               placeholder="0.00"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" 
                               name="location_details[${newRowIndex}][no_of_units]"
                               step="1"
                               min="0"
                               placeholder="0"
                               class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-4 py-3">
                        <button type="button" 
                                class="delete-span-btn w-8 h-8 flex items-center justify-center text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                data-row-index="${newRowIndex}"
                                title="Delete span">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-900/50 child-table-row" data-row-index="${newRowIndex}" style="display: none;">
                    <td colspan="7" class="px-4 py-4">
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border border-gray-300 dark:border-gray-600">
                                <thead>
                                    <tr class="bg-gray-200 dark:bg-gray-700">
                                        <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">LOCATION</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">SPACING</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">DIA</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">NO OF</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">NO PER</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">TOTAL NO</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">CUT LENGTH</th>
                                        <th class="px-2 py-2 text-center font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600" colspan="5">TOTAL LENGTH</th>
                                    </tr>
                                    <tr class="bg-gray-200 dark:bg-gray-700">
                                        <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                        <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                        <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                        <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                        <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                        <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                        <th class="px-2 py-1 border border-gray-300 dark:border-gray-600"></th>
                                        <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">3/8"<br><span class="text-xs">10</span></th>
                                        <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">1/2"<br><span class="text-xs">12</span></th>
                                        <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">5/8"<br><span class="text-xs">16</span></th>
                                        <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">3/4"<br><span class="text-xs">20</span></th>
                                        <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600">1"<br><span class="text-xs">25</span></th>
                                    </tr>
                                </thead>
                                <tbody id="childTableBody_${newRowIndex}">
                                    <!-- Child table rows will be added here -->
                                </tbody>
                            </table>
                            <button type="button" 
                                    class="add-child-row-btn mt-2 px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 flex items-center gap-1"
                                    data-row-index="${newRowIndex}"
                                    title="Add row">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Add Row</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);

            // Add 5 default rows to the child table
            const childTableBody = $(`#childTableBody_${newRowIndex}`);
            for (let j = 0; j < 5; j++) {
                const childRow = `
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="text" 
                               name="location_details[${newRowIndex}][child_rows][${j}][location]"
                               placeholder="Child Span"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="text" 
                               name="location_details[${newRowIndex}][child_rows][${j}][spacing]"
                               placeholder="Spacing"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="text" 
                               name="location_details[${newRowIndex}][child_rows][${j}][dia]"
                               placeholder="DIA"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][no_of_unit]"
                               step="1"
                               placeholder="0"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][no_per_unit]"
                               step="1"
                               placeholder="0"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][total_no]"
                               step="1"
                               placeholder="0"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][cut_length]"
                               step="0.01"
                               placeholder="0.00"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][total_length_3_8]"
                               step="0.01"
                               placeholder="0.00"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][total_length_1_2]"
                               step="0.01"
                               placeholder="0.00"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][total_length_5_8]"
                               step="0.01"
                               placeholder="0.00"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][total_length_3_4]"
                               step="0.01"
                               placeholder="0.00"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                        <input type="number" 
                               name="location_details[${newRowIndex}][child_rows][${j}][total_length_1]"
                               step="0.01"
                               placeholder="0.00"
                               class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </td>
                </tr>
            `;
                childTableBody.append(childRow);
            }

            // Re-attach toggle handlers to include the new button
            attachToggleHandlers();
        }

        // Handle Add Span button click
        $(document).on('click', '#addSpanBtn', function(e) {
            e.preventDefault();
            addNewSpanRow();
        });

        // Handle Delete Span button click
        $(document).on('click', '.delete-span-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const rowIndex = $(this).data('row-index');
            const parentRow = $(`.parent-row[data-row-index="${rowIndex}"]`);
            const childTableRow = $(`.child-table-row[data-row-index="${rowIndex}"]`);

            // Show confirmation alert
            if (confirm(
                    'Are you sure you want to delete this span and its child table? This action cannot be undone.'
                )) {
                // Remove both parent row and child table row
                parentRow.fadeOut(300, function() {
                    $(this).remove();
                });
                childTableRow.fadeOut(300, function() {
                    $(this).remove();
                });

                // Re-index remaining rows
                reindexSpanRows();
            }
        });

            // Add a new row to a child table
            function addChildTableRow(rowIndex) {
                const childTableBody = $(`#childTableBody_${rowIndex}`);
                const currentRowCount = childTableBody.find('tr').length;
                const newRowIndex = currentRowCount;

                // Use the rowIndex as the span index (parent row index)
                const spanIndex = rowIndex;

                const childRow = `
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="text" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][location]"
                                   placeholder="Child Span"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="text" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][spacing]"
                                   placeholder="Spacing"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="text" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][dia]"
                                   placeholder="DIA"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][no_of_unit]"
                                   step="1"
                                   placeholder="0"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][no_per_unit]"
                                   step="1"
                                   placeholder="0"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][total_no]"
                                   step="1"
                                   placeholder="0"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][cut_length]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][total_length_3_8]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][total_length_1_2]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][total_length_5_8]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][total_length_3_4]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                        <td class="px-2 py-2 border border-gray-300 dark:border-gray-600">
                            <input type="number" 
                                   name="location_details[${spanIndex}][child_rows][${newRowIndex}][total_length_1]"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </td>
                    </tr>
                `;
                childTableBody.append(childRow);
            }

            // Handle Add Child Row button click
            $(document).on('click', '.add-child-row-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const rowIndex = $(this).data('row-index');
                addChildTableRow(rowIndex);
            });

            // Re-index span rows after deletion
            function reindexSpanRows() {
            const tbody = $('#locationDetailsTableBody');
            const parentRows = tbody.find('.parent-row');

            parentRows.each(function(index) {
                const newIndex = index;
                const $row = $(this);
                const $childRow = $row.next('.child-table-row');

                // Update data attributes
                $row.attr('data-row-index', newIndex);
                $childRow.attr('data-row-index', newIndex);

                // Update toggle button
                $row.find('.toggle-child-table-btn').attr('data-row-index', newIndex);

                // Update delete button
                $row.find('.delete-span-btn').attr('data-row-index', newIndex);

                // Update input field names
                $row.find('input').each(function() {
                    const $input = $(this);
                    const name = $input.attr('name');
                    if (name) {
                        $input.attr('name', name.replace(/location_details\[\d+\]/,
                            `location_details[${newIndex}]`));
                    }
                });

                // Update child table input field names
                $childRow.find('input').each(function() {
                    const $input = $(this);
                    const name = $input.attr('name');
                    if (name) {
                        $input.attr('name', name.replace(/location_details\[\d+\]/,
                            `location_details[${newIndex}]`));
                    }
                });

                // Update child table body ID
                const childTableBody = $childRow.find('tbody');
                if (childTableBody.length) {
                    childTableBody.attr('id', `childTableBody_${newIndex}`);
                }
            });
        }
    </script>
</body>

</html>
