<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forms Management - {{ config('app.name', 'Laravel') }}</title>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper {
            padding: 0;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
            margin-bottom: 1.5rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            margin-left: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: white;
        }

        .dark .dataTables_wrapper .dataTables_filter input {
            background: #1f2937;
            border-color: #4b5563;
            color: white;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .dataTables_wrapper .dataTables_length {
            margin-bottom: 1.5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            background: white;
        }

        .dark .dataTables_wrapper .dataTables_length select {
            background: #1f2937;
            border-color: #4b5563;
            color: white;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 1rem;
            color: #6b7280;
        }

        .dark .dataTables_wrapper .dataTables_info {
            color: #9ca3af;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            min-width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
        }

        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: #1f2937;
            border-color: #4b5563;
            color: #d1d5db;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
            background: #f3f4f6;
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
            background: #374151;
            border-color: #60a5fa;
            color: #60a5fa;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
            font-weight: 600;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            font-size: 1.125rem;
            font-weight: 600;
        }

        /* Table Styling */
        #formsTable {
            border-collapse: separate;
            border-spacing: 0;
        }

        #formsTable thead th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .dark #formsTable thead th {
            background: #111827;
            color: #d1d5db;
            border-bottom-color: #374151;
        }

        #formsTable tbody td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }

        .dark #formsTable tbody td {
            border-bottom-color: #374151;
            color: #d1d5db;
        }

        #formsTable tbody tr:hover {
            background: #f9fafb;
        }

        .dark #formsTable tbody tr:hover {
            background: #1f2937;
        }

        /* Action Buttons */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.2s;
            gap: 0.375rem;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-action svg {
            width: 1rem;
            height: 1rem;
        }

        /* Loading Spinner */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.9) !important;
            border-radius: 0.5rem;
            padding: 2rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .dark .dataTables_processing {
            background: rgba(31, 41, 55, 0.9) !important;
        }

        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="min-h-screen">
        <!-- Header -->
        <header
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg shadow-lg border-b border-gray-200/50 dark:border-gray-700/50 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">Forms Management</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Manage and organize your forms</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('forms.deleted') }}"
                            class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Restore Forms
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

            <!-- Create Form -->
            <div
                class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Create New Form</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Enter client name and project name to create a
                        new form</p>
                </div>
                <form id="createForm" class="flex flex-col sm:flex-row gap-4 items-end">
                    @csrf
                    <div class="flex-1 w-full sm:w-auto">
                        <label for="create_client_name"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Client Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="create_client_name" name="client_name" required
                            placeholder="Enter client name"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="flex-1 w-full sm:w-auto">
                        <label for="create_project_name"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Project Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="create_project_name" name="project_name" required
                            placeholder="Enter project name"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="w-full sm:w-auto">
                        <button type="submit" id="createFormBtn"
                            class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                            Create Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- DataTable Card -->
            <div
                class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-lg rounded-2xl shadow-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">All Forms</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">View and manage all your forms in one place
                        </p>
                    </div>

                    <table id="formsTable" class="w-full" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th class="text-center">Client Name</th>
                                <th class="text-center">Project Name</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal"
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
                <p class="text-gray-600 dark:text-gray-300 mb-6">Are you sure you want to delete this form? This action
                    cannot be undone and all associated fields will be deleted.</p>
                <div class="flex justify-end space-x-3">
                    <button id="cancelDelete"
                        class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium">
                        Cancel
                    </button>
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all font-medium shadow-lg">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        let formsTable;

        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Handle create form submission
            $('#createForm').on('submit', function(e) {
                e.preventDefault();

                const submitBtn = $('#createFormBtn');
                const originalText = submitBtn.text();
                submitBtn.prop('disabled', true).text('Creating...');

                const formData = {
                    client_name: $('#create_client_name').val(),
                    project_name: $('#create_project_name').val(),
                    item_name: null,
                    fields: []
                };

                $.ajax({
                    url: '{{ route('forms.store') }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        submitBtn.prop('disabled', false).text(originalText);

                        // Reset form
                        $('#createForm')[0].reset();

                        // Show success message
                        showSuccessMessage('Form created successfully!');

                        // Reload DataTable to show the new form (latest first)
                        if (formsTable) {
                            formsTable.ajax.reload(null, false);
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
            });

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

            // Initialize DataTable
            formsTable = $('#formsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('forms.index') }}",
                    type: 'GET',
                    data: function(d) {
                        // No filter parameters needed
                    }
                },
                columns: [{
                        data: null,
                        name: 'row_number',
                        orderable: false,
                        searchable: false,
                        width: '5%',
                        render: function(data, type, row, meta) {
                            // Calculate row number based on current page and row index
                            return `<span class="font-semibold text-blue-600 dark:text-blue-400">#${meta.row + meta.settings._iDisplayStart + 1}</span>`;
                        }
                    },
                    {
                        data: 'client_name',
                        name: 'client_name',
                        render: function(data) {
                            return `<span class="font-medium text-gray-900 dark:text-white">${data || '-'}</span>`;
                        }
                    },
                    {
                        data: 'project_name',
                        name: 'project_name',
                        render: function(data) {
                            return `<span class="text-gray-700 dark:text-gray-300">${data || '-'}</span>`;
                        }
                    },
                    {
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        width: '15%',
                        className: 'text-center',
                        render: function(data, type, row) {
                            const clientName = encodeURIComponent(row.client_name || '');
                            const projectName = encodeURIComponent(row.project_name || '');
                            return `
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="/forms/project/edit?client_name=${clientName}&project_name=${projectName}" 
                                       class="btn-action bg-blue-600 hover:bg-blue-700 text-white shadow-md">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        <span>Edit</span>
                                    </a>
                                     <a href="/forms/project/export?client_name=${clientName}&project_name=${projectName}" 
                                       class="btn-action bg-green-600 hover:bg-green-700 text-white shadow-md export-btn"
                                       data-client-name="${row.client_name || ''}"
                                       data-project-name="${row.project_name || ''}">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span>Export</span>
                                    </a>
                                </div>
                            `;
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ], // Order by ID (row number) - latest first
                pageLength: 25,
                lengthMenu: [
                    [25, 50, 100, 200],
                    [25, 50, 100, 200]
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No entries to show",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "«",
                        last: "»",
                        next: "›",
                        previous: "‹"
                    }
                },
                dom: '<"flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6"<"mb-4 sm:mb-0"l><"w-full sm:w-auto"f>>rt<"flex flex-col sm:flex-row justify-between items-start sm:items-center mt-6"<"mb-4 sm:mb-0"i><"w-full sm:w-auto"p>>',
                drawCallback: function() {
                    // Apply dark mode classes if needed
                    if (document.documentElement.classList.contains('dark')) {
                        $('.dataTables_wrapper').addClass('dark');
                    }

                    // Ensure pagination buttons are inline and styled
                    $('.dataTables_paginate').css({
                        'display': 'flex',
                        'flex-direction': 'row',
                        'align-items': 'center',
                        'justify-content': 'center',
                        'gap': '0.5rem',
                        'flex-wrap': 'wrap'
                    });

                    // Style pagination buttons
                    $('.dataTables_paginate .paginate_button').each(function() {
                        if ($(this).hasClass('previous') || $(this).hasClass('next')) {
                            $(this).css({
                                'font-size': '1.125rem',
                                'font-weight': '600',
                                'min-width': '2.5rem',
                                'height': '2.5rem'
                            });
                        }
                    });
                }
            });

            // Delete confirmation function
            window.confirmDelete = function(id) {
                const form = document.getElementById('deleteForm');
                form.action = `/forms/${id}`;
                const modal = document.getElementById('deleteModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    modal.querySelector('div').classList.add('scale-100');
                }, 10);
            };

            // Cancel delete
            document.getElementById('cancelDelete').addEventListener('click', function() {
                const modal = document.getElementById('deleteModal');
                modal.querySelector('div').classList.remove('scale-100');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 200);
            });

            // Close modal on outside click
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.querySelector('div').classList.remove('scale-100');
                    setTimeout(() => {
                        this.classList.add('hidden');
                        this.classList.remove('flex');
                    }, 200);
                }
            });

            // Handle form submission
            document.getElementById('deleteForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;

                submitBtn.disabled = true;
                submitBtn.textContent = 'Deleting...';

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const modal = document.getElementById('deleteModal');
                        modal.querySelector('div').classList.remove('scale-100');
                        setTimeout(() => {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        }, 200);

                        table.ajax.reload();

                        // Show success message
                        const successMsg = document.createElement('div');
                        successMsg.className =
                            'mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-5 py-4 rounded-r-lg shadow-md';
                        successMsg.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-medium">Form deleted successfully.</span>
                        </div>
                    `;
                        document.querySelector('main').insertBefore(successMsg, document.querySelector(
                            'main').firstChild);

                        setTimeout(() => {
                            successMsg.style.transition = 'opacity 0.3s';
                            successMsg.style.opacity = '0';
                            setTimeout(() => successMsg.remove(), 300);
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        alert('An error occurred while deleting the form.');
                    });
            });
        });
    </script>
</body>

</html>
