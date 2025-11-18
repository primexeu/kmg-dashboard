{{-- Shared Language Toggle Component --}}
<div class="relative">
    <button id="language-toggle" class="inline-flex items-center rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
        </svg>
        {{ app()->getLocale() === 'de' ? 'DE' : 'EN' }}
    </button>
    <div id="language-dropdown" class="hidden absolute right-0 mt-2 w-32 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50">
        <a href="{{ route('language.switch', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-t-xl">
            🇺🇸 English
        </a>
        <a href="{{ route('language.switch', 'de') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-b-xl">
            🇩🇪 Deutsch
        </a>
    </div>
</div>

<script>
// Language dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const languageToggle = document.getElementById('language-toggle');
    const languageDropdown = document.getElementById('language-dropdown');

    if (languageToggle && languageDropdown) {
        languageToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            languageDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            languageDropdown.classList.add('hidden');
        });

        // Prevent dropdown from closing when clicking inside it
        languageDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
</script>
