<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deleted Forms - {{ config('app.name', 'Laravel') }}</title>
    
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg shadow-lg border-b border-gray-200/50 dark:border-gray-700/50 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">Deleted Forms</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Restore deleted forms and their fields</p>
                    </div>
                    <a href="{{ route('forms.index') }}" 
                       class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Forms
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-5 py-4 rounded-r-lg shadow-md animate-slide-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="mb-6 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/20 border-l-4 border-red-500 text-red-800 dark:text-red-200 px-5 py-4 rounded-r-lg shadow-md animate-slide-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- DataTable Card -->
            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-lg rounded-2xl shadow-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Deleted Forms</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Restore deleted forms to make them active again</p>
                    </div>
                    
                    <table id="formsTable" class="w-full" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client Name</th>
                                <th>Project Name</th>
                                <th>Created At</th>
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

    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            var table = $('#formsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('forms.deleted') }}",
                    type: 'GET',
                    data: function(d) {
                        // Add any additional parameters here if needed
                    }
                },
                columns: [
                    { 
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
                        data: 'created_at', 
                        name: 'created_at',
                        render: function(data) {
                            if (data) {
                                const date = new Date(data);
                                return `
                                    <div class="text-sm">
                                        <div class="text-gray-900 dark:text-white font-medium">${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs">${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                                    </div>
                                `;
                            }
                            return '<span class="text-gray-400">-</span>';
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
                            return `
                                <div class="flex items-center justify-center">
                                    <form action="/forms/${data}/restore" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn-action bg-orange-600 hover:bg-orange-700 text-white shadow-md">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <span>Restore</span>
                                        </button>
                                    </form>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
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

            // Handle restore form submission
            $(document).on('submit', 'form[action*="/restore"]', function(e) {
                e.preventDefault();
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.find('span').text();
                
                submitBtn.prop('disabled', true);
                submitBtn.find('span').text('Restoring...');
                
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        table.ajax.reload();
                        
                        // Show success message
                        const successMsg = $('<div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-200 px-5 py-4 rounded-r-lg shadow-md"><div class="flex items-center"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg><span class="font-medium">Form restored successfully.</span></div></div>');
                        $('main').prepend(successMsg);
                        
                        setTimeout(() => {
                            successMsg.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 3000);
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to restore form.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        
                        // Show error message
                        const errorMsgDiv = $('<div class="mb-6 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/20 border-l-4 border-red-500 text-red-800 dark:text-red-200 px-5 py-4 rounded-r-lg shadow-md"><div class="flex items-center"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg><span class="font-medium">' + errorMsg + '</span></div></div>');
                        $('main').prepend(errorMsgDiv);
                        
                        setTimeout(() => {
                            errorMsgDiv.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 3000);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        submitBtn.find('span').text(originalText);
                    }
                });
            });
        });
    </script>
</body>
</html>

