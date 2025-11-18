<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Auto-refresh notification -->
        <div id="auto-refresh-status" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <span id="auto-refresh-message">Automatische Aktualisierung aktiviert</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Main content -->
        {{ $this->table }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let autoRefreshInterval;
            let isAutoRefreshEnabled = false;
            const statusDiv = document.getElementById('auto-refresh-status');
            const messageSpan = document.getElementById('auto-refresh-message');

            // Function to fetch new results directly (like the test page)
            function fetchNewResults() {
                fetch('/fetch-api-results')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show notification
                            messageSpan.textContent = 'Neue Ergebnisse abgerufen! Die Seite wird aktualisiert...';
                            statusDiv.className = 'bg-green-50 border border-green-200 rounded-lg p-4';
                            
                            // Refresh the page after a short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else if (data.is_duplicate) {
                            // Update status message for duplicates
                            messageSpan.textContent = `Letzte Prüfung: ${new Date().toLocaleTimeString()} - Bereits verarbeitet (keine neuen Daten)`;
                            statusDiv.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-4';
                        } else {
                            // Update status message
                            messageSpan.textContent = `Letzte Prüfung: ${new Date().toLocaleTimeString()} - ${data.message || 'Keine neuen Ergebnisse'}`;
                            statusDiv.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching new results:', error);
                        messageSpan.textContent = 'Fehler beim Abrufen neuer Ergebnisse';
                        statusDiv.className = 'bg-red-50 border border-red-200 rounded-lg p-4';
                    });
            }

            // Function to start auto-refresh
            function startAutoRefresh() {
                if (isAutoRefreshEnabled) return;
                
                isAutoRefreshEnabled = true;
                statusDiv.classList.remove('hidden');
                messageSpan.textContent = 'Automatische Aktualisierung aktiviert - Prüfe alle 30 Sekunden...';
                
                // Fetch immediately
                fetchNewResults();
                
                // Then fetch every 30 seconds
                autoRefreshInterval = setInterval(fetchNewResults, 30000);
            }

            // Function to stop auto-refresh
            function stopAutoRefresh() {
                if (!isAutoRefreshEnabled) return;
                
                isAutoRefreshEnabled = false;
                clearInterval(autoRefreshInterval);
                statusDiv.classList.add('hidden');
            }

            // Add toggle button to the page
            const headerActions = document.querySelector('.fi-header-actions');
            if (headerActions) {
                const toggleButton = document.createElement('button');
                toggleButton.className = 'fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-outlined';
                toggleButton.innerHTML = `
                    <span class="fi-btn-label">Auto-Refresh</span>
                `;
                toggleButton.onclick = function() {
                    if (isAutoRefreshEnabled) {
                        stopAutoRefresh();
                        this.innerHTML = '<span class="fi-btn-label">Auto-Refresh starten</span>';
                    } else {
                        startAutoRefresh();
                        this.innerHTML = '<span class="fi-btn-label">Auto-Refresh stoppen</span>';
                    }
                };
                headerActions.appendChild(toggleButton);
            }

            // Auto-start after 5 seconds
            setTimeout(() => {
                startAutoRefresh();
                const button = document.querySelector('.fi-header-actions button:last-child');
                if (button) {
                    button.innerHTML = '<span class="fi-btn-label">Auto-Refresh stoppen</span>';
                }
            }, 5000);
        });
    </script>
</x-filament-panels::page>
