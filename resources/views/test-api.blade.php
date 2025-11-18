<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Python API Integration - KMG Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Test Python API Integration
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Fetch results from Python API and display in Laravel
                </p>
            </div>
            
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-center space-x-4">
                    <button id="fetchResults" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Fetch API Results
                    </button>
                    <button id="debugApi" 
                            class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Debug API
                    </button>
                    <button id="clearResults" 
                            class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Clear Results
                    </button>
                </div>
                
                <div id="status" class="mt-4 text-center"></div>
                <div id="results" class="mt-6" style="display: none;">
                    <h3 class="text-lg font-semibold mb-4">API Results:</h3>
                    <div id="resultsContent" class="bg-gray-100 p-4 rounded overflow-auto max-h-96"></div>
                </div>
                
                <div id="databaseResult" class="mt-6" style="display: none;">
                    <h3 class="text-lg font-semibold mb-4">Database Result:</h3>
                    <div id="databaseContent" class="bg-green-100 p-4 rounded"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('fetchResults').addEventListener('click', async function() {
            const statusDiv = document.getElementById('status');
            const resultsDiv = document.getElementById('results');
            const resultsContent = document.getElementById('resultsContent');
            const databaseDiv = document.getElementById('databaseResult');
            const databaseContent = document.getElementById('databaseContent');
            
            statusDiv.innerHTML = '<div class="text-blue-600">Fetching results from Python API...</div>';
            resultsDiv.style.display = 'none';
            databaseDiv.style.display = 'none';
            
            try {
                const response = await fetch('/fetch-api-results', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    statusDiv.innerHTML = '<div class="text-green-600">✅ Successfully fetched results!</div>';
                    
                    // Display API results
                    resultsContent.innerHTML = '<pre>' + JSON.stringify(data.data, null, 2) + '</pre>';
                    resultsDiv.style.display = 'block';
                    
                    // Display database result
                    if (data.database_result) {
                        databaseContent.innerHTML = `
                            <p><strong>Type:</strong> ${data.database_result.type}</p>
                            <p><strong>ID:</strong> ${data.database_result.comparison_id || data.database_result.match_id || data.database_result.mismatch_id}</p>
                            ${data.database_result.redirect_url ? `<p><strong>View:</strong> <a href="${data.database_result.redirect_url}" class="text-blue-600 hover:underline" target="_blank">Open in Filament</a></p>` : ''}
                        `;
                        databaseDiv.style.display = 'block';
                    }
                } else {
                    statusDiv.innerHTML = `<div class="text-red-600">❌ Error: ${data.error || data.message}</div>`;
                }
            } catch (error) {
                statusDiv.innerHTML = `<div class="text-red-600">❌ Connection Error: ${error.message}</div>`;
            }
        });
        
        document.getElementById('debugApi').addEventListener('click', async function() {
            const statusDiv = document.getElementById('status');
            const resultsDiv = document.getElementById('results');
            const resultsContent = document.getElementById('resultsContent');
            const databaseDiv = document.getElementById('databaseResult');
            
            statusDiv.innerHTML = '<div class="text-yellow-600">Debugging API connection...</div>';
            resultsDiv.style.display = 'none';
            databaseDiv.style.display = 'none';
            
            try {
                const response = await fetch('/debug-api', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    statusDiv.innerHTML = '<div class="text-green-600">✅ Debug info retrieved!</div>';
                    
                    // Display debug results
                    resultsContent.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    resultsDiv.style.display = 'block';
                } else {
                    statusDiv.innerHTML = `<div class="text-red-600">❌ Debug Error: ${data.error}</div>`;
                }
            } catch (error) {
                statusDiv.innerHTML = `<div class="text-red-600">❌ Debug Connection Error: ${error.message}</div>`;
            }
        });
        
        document.getElementById('clearResults').addEventListener('click', function() {
            document.getElementById('status').innerHTML = '';
            document.getElementById('results').style.display = 'none';
            document.getElementById('databaseResult').style.display = 'none';
        });
    </script>
</body>
</html>
