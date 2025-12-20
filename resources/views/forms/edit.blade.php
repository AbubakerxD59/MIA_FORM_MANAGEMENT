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
        input[type="number"], textarea {
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input[type="number"]:focus, textarea:focus {
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24">
            @if($errors->any())
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('forms.update', $form->id) }}" method="POST" id="formForm">
                @csrf
                @method('PUT')
                
                <!-- Form Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Form Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="item_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Item Name
                            </label>
                            <input type="text" 
                                   id="item_name" 
                                   name="item_name" 
                                   value="{{ old('item_name', $form->item_name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label for="client_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Client Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="client_name" 
                                   name="client_name" 
                                   value="{{ old('client_name', $form->client_name) }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Project Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="project_name" 
                                   name="project_name" 
                                   value="{{ old('project_name', $form->project_name) }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Fields Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Fields</h2>
                    </div>

                    <!-- Fields Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-12">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider min-w-[300px]">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32">Length</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32">Width</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32">Height</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-32 whitespace-nowrap">Product Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider w-20">Action</th>
                                </tr>
                            </thead>
                            <tbody id="fieldsTableBody">
                                @php
                                    $fieldCount = $fields->count();
                                    $maxRows = max(30, $fieldCount);
                                @endphp
                                @for($i = 0; $i < $maxRows; $i++)
                                    @php
                                        $field = $fields->get($i);
                                    @endphp
                                <tr class="field-row border-b border-gray-200 dark:border-gray-700">
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        @if($field && $field->id)
                                            <input type="hidden" name="fields[{{ $i }}][id]" value="{{ $field->id }}">
                                        @endif
                                        <textarea name="fields[{{ $i }}][description]" 
                                                  rows="1"
                                                  placeholder="Enter description"
                                                  oninput="calculateProduct(this)"
                                                  class="field-description w-full min-w-[300px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none">{{ old("fields.$i.description", $field->description ?? '') }}</textarea>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" 
                                               name="fields[{{ $i }}][quantity]" 
                                               step="1" 
                                               min="0"
                                               placeholder="0"
                                               oninput="calculateProduct(this)"
                                               value="{{ old("fields.$i.quantity", $field->quantity ?? '') }}"
                                               class="field-quantity w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" 
                                               name="fields[{{ $i }}][length]" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0"
                                               oninput="calculateProduct(this)"
                                               value="{{ old("fields.$i.length", $field->length ?? '') }}"
                                               class="field-length w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" 
                                               name="fields[{{ $i }}][width]" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0"
                                               oninput="calculateProduct(this)"
                                               value="{{ old("fields.$i.width", $field->width ?? '') }}"
                                               class="field-width w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" 
                                               name="fields[{{ $i }}][height]" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0"
                                               oninput="calculateProduct(this)"
                                               value="{{ old("fields.$i.height", $field->height ?? '') }}"
                                               class="field-height w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" 
                                               name="fields[{{ $i }}][product]" 
                                               step="0.01" 
                                               min="0"
                                               placeholder="0"
                                               readonly
                                               value="{{ old("fields.$i.product", $field->product ?? '') }}"
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
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </main>

        <!-- Fixed Form Actions -->
        <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-center space-x-4">
                    <a href="{{ route('forms.index') }}" 
                       class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="button" 
                            onclick="addFieldRow()" 
                            class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Row
                    </button>
                    <button type="submit" 
                            form="formForm"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        Update Form
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let fieldIndex = {{ $maxRows }};

        function calculateProduct(input) {
            const row = input.closest('tr');
            const quantity = parseFloat(row.querySelector('.field-quantity').value) || 0;
            const length = parseFloat(row.querySelector('.field-length').value) || 0;
            const width = parseFloat(row.querySelector('.field-width').value) || 0;
            const height = parseFloat(row.querySelector('.field-height').value) || 0;
            const productInput = row.querySelector('.field-product');
            
            // Calculate product only if all values are greater than 0
            if (quantity > 0 && length > 0 && width > 0 && height > 0) {
                const product = quantity * length * width * height;
                productInput.value = product.toFixed(2);
            } else {
                productInput.value = '';
            }
        }

        function addFieldRow() {
            const tbody = document.getElementById('fieldsTableBody');
            const row = document.createElement('tr');
            row.className = 'field-row border-b border-gray-200 dark:border-gray-700';
            row.innerHTML = `
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${fieldIndex + 1}</td>
                <td class="px-4 py-3">
                    <textarea name="fields[${fieldIndex}][description]" 
                              rows="1"
                              placeholder="Enter description"
                              oninput="calculateProduct(this)"
                              class="field-description w-full min-w-[300px] px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white resize-none"></textarea>
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${fieldIndex}][quantity]" 
                           step="1" 
                           min="0"
                           placeholder="0"
                           oninput="calculateProduct(this)"
                           class="field-quantity w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${fieldIndex}][length]" 
                           step="0.01" 
                           min="0"
                           placeholder="0"
                           oninput="calculateProduct(this)"
                           class="field-length w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${fieldIndex}][width]" 
                           step="0.01" 
                           min="0"
                           placeholder="0"
                           oninput="calculateProduct(this)"
                           class="field-width w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${fieldIndex}][height]" 
                           step="0.01" 
                           min="0"
                           placeholder="0"
                           oninput="calculateProduct(this)"
                           class="field-height w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="fields[${fieldIndex}][product]" 
                           step="0.01" 
                           min="0"
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
            fieldIndex++;
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
                if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key) ||
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
                if (e.key === '.') {
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

        // Filter non-numeric characters on input (safety measure)
        document.addEventListener('input', function(e) {
            if (e.target.type === 'number' && e.target.readOnly === false) {
                // Remove any non-numeric characters except decimal point
                let value = e.target.value;
                // Remove all non-numeric characters except decimal point
                value = value.replace(/[^0-9.]/g, '');
                // Ensure only one decimal point
                const parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }
                e.target.value = value;
            }
        });

        // Calculate product for existing rows on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.field-quantity, .field-length, .field-width, .field-height').forEach(input => {
                if (input.value) {
                    calculateProduct(input);
                }
            });
        });
    </script>
</body>
</html>

