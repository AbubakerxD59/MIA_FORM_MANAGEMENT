@auth
    <!-- User Dropdown -->
    <div class="relative inline-block" id="userDropdownContainer">
        <!-- Dropdown Toggle Button -->
        <button id="userDropdownToggle"
            class="inline-flex items-center gap-3 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <!-- User Avatar Circle -->
            <div class="flex-shrink-0 w-8 h-8 bg-white/20 rounded-full flex items-center justify-center border-2 border-white/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                    </path>
                </svg>
            </div>
            <!-- User Name -->
            <span class="text-sm font-semibold">{{ Auth::user()->name }}</span>
            <!-- Dropdown Arrow -->
            <svg class="w-4 h-4 fill-current transition-transform duration-200" id="dropdownArrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div id="userDropdownMenu"
            class="absolute right-0 mt-2 w-56 rounded-xl shadow-2xl bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 z-50 hidden overflow-hidden">
            <!-- User Info Header -->
            <div class="px-4 py-3 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border-b border-gray-200 dark:border-gray-700">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ Auth::user()->email }}</p>
            </div>
            <!-- Menu Items -->
            <div class="py-2">
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" onclick="event.preventDefault(); closeUserDropdown(); if(confirm('Are you sure you want to log out? You will need to log in again to access your account.')) { this.closest('form').submit(); }"
                        class="flex items-center w-full text-left px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-150">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Dropdown JavaScript -->
    <script>
        (function() {
            // User dropdown toggle
            const toggleBtn = document.getElementById('userDropdownToggle');
            const dropdownMenu = document.getElementById('userDropdownMenu');

            if (toggleBtn && dropdownMenu) {
                const dropdownArrow = document.getElementById('dropdownArrow');
                
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isHidden = dropdownMenu.classList.contains('hidden');
                    
                    if (isHidden) {
                        dropdownMenu.classList.remove('hidden');
                        if (dropdownArrow) {
                            dropdownArrow.style.transform = 'rotate(180deg)';
                        }
                    } else {
                        dropdownMenu.classList.add('hidden');
                        if (dropdownArrow) {
                            dropdownArrow.style.transform = 'rotate(0deg)';
                        }
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    const container = document.getElementById('userDropdownContainer');
                    if (container && !container.contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                        if (dropdownArrow) {
                            dropdownArrow.style.transform = 'rotate(0deg)';
                        }
                    }
                });
            }

            // Close dropdown function
            window.closeUserDropdown = function() {
                const dropdownMenu = document.getElementById('userDropdownMenu');
                const dropdownArrow = document.getElementById('dropdownArrow');
                if (dropdownMenu) {
                    dropdownMenu.classList.add('hidden');
                }
                if (dropdownArrow) {
                    dropdownArrow.style.transform = 'rotate(0deg)';
                }
            };
        })();
    </script>
@endauth
