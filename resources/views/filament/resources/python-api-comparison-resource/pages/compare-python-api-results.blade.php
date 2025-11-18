<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Uploaded Files Section - ALWAYS AT THE TOP -->
        <div class="mb-6">
            @php
                // Get all files from multiple sources using the model method
                $files = $record->getUploadedFiles();
                $formatBytes = function ($bytes) {
                    if (!is_numeric($bytes)) return 'N/A';
                    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                    $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
                    $pow = min($pow, count($units) - 1);
                    $value = $bytes / pow(1024, $pow);
                    return number_format($value, $pow >= 2 ? 2 : 0) . ' ' . $units[$pow];
                };
                $getFileIcon = function($filename, $type) {
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (in_array($ext, ['pdf'])) return 'M7 16h10M7 16l4-4m-4 4l4 4m6-8h2M17 8V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m8 0V6a2 2 0 012-2h2a2 2 0 012 2v2M7 16V8m0 0H5a2 2 0 00-2 2v6a2 2 0 002 2h2M7 8h2';
                    if (in_array($ext, ['doc', 'docx'])) return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
                    if (in_array($ext, ['xls', 'xlsx'])) return 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) return 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z';
                    return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
                };
                $getSourceBadge = function($source) {
                    return match($source) {
                        'api' => ['label' => 'API', 'color' => 'bg-blue-100 text-blue-800'],
                        'upload' => ['label' => 'Upload', 'color' => 'bg-green-100 text-green-800'],
                        'document' => ['label' => 'Document', 'color' => 'bg-purple-100 text-purple-800'],
                        'document_url' => ['label' => 'External URL', 'color' => 'bg-orange-100 text-orange-800'],
                        default => ['label' => $source, 'color' => 'bg-gray-100 text-gray-800'],
                    };
                };
            @endphp
            @if(!empty($files))
            @php
                // Separate previewable files from others
                $previewableFiles = [];
                $otherFiles = [];
                foreach($files as $index => $file) {
                    $filename = $file['filename'] ?? 'Datei';
                    $fileUrl = $file['url'] ?? '';
                    $fileType = $file['type'] ?? '';
                    $isPdf = str_contains(strtolower($fileType), 'pdf') || str_ends_with(strtolower($filename), '.pdf');
                    $isImage = str_starts_with(strtolower($fileType), 'image/') || in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $canPreview = ($isPdf || $isImage) && !empty($fileUrl);
                    
                    if ($canPreview) {
                        $previewableFiles[] = array_merge($file, [
                            'index' => $index,
                            'isPdf' => $isPdf,
                            'isImage' => $isImage,
                        ]);
                    } else {
                        $otherFiles[] = array_merge($file, ['index' => $index]);
                    }
                }
                $hasSideBySide = count($previewableFiles) >= 2;
            @endphp
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Hochgeladene Dateien</h2>
                        <span class="ml-3 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ count($files) }} Datei(en)</span>
                    </div>
                    @if($hasSideBySide)
                        <button 
                            type="button"
                            id="viewer-toggle-btn"
                            onclick="toggleSideBySideViewer()"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg id="viewer-toggle-icon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span id="viewer-toggle-text">Viewer schließen</span>
                        </button>
                    @endif
                </div>

                @if($hasSideBySide)
                    <!-- Side-by-Side Viewer -->
                    <div id="side-by-side-viewer" class="mb-6 border border-gray-300 rounded-lg overflow-hidden bg-gray-100" style="height: 700px;">
                        <div class="flex h-full" style="position: relative; align-items: stretch;">
                            <!-- Left Panel -->
                            <div id="left-panel" class="bg-white overflow-hidden" style="width: 50%; flex-shrink: 0;">
                                <div class="bg-gray-50 border-b border-gray-200 px-4 py-2 flex items-center justify-between h-12">
                                    <span class="text-sm font-medium text-gray-700 truncate">{{ $previewableFiles[0]['filename'] ?? 'Datei 1' }}</span>
                                    <a href="{{ $previewableFiles[0]['url'] ?? '#' }}" target="_blank" class="text-gray-400 hover:text-gray-600 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </div>
                                <div class="h-full overflow-auto" style="height: calc(100% - 48px);">
                                    @if($previewableFiles[0]['isPdf'])
                                        <iframe 
                                            src="{{ $previewableFiles[0]['url'] }}#toolbar=1&navpanes=0&scrollbar=1" 
                                            class="w-full h-full" 
                                            frameborder="0">
                                        </iframe>
                                    @elseif($previewableFiles[0]['isImage'])
                                        <div class="flex justify-center items-center h-full p-4">
                                            <img 
                                                src="{{ $previewableFiles[0]['url'] }}" 
                                                alt="{{ $previewableFiles[0]['filename'] }}"
                                                class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Resizable Splitter -->
                            <div 
                                id="splitter" 
                                class="bg-gray-300 hover:bg-blue-500 cursor-col-resize transition-colors flex items-center justify-center relative z-10"
                                style="width: 8px; min-width: 8px; flex-shrink: 0;"
                                onmousedown="startResize(event)">
                                <div class="w-1 h-16 bg-gray-400 rounded pointer-events-none"></div>
                            </div>

                            <!-- Right Panel -->
                            <div id="right-panel" class="bg-white overflow-hidden" style="width: 50%; flex-shrink: 0;">
                                <div class="bg-gray-50 border-b border-gray-200 px-4 py-2 flex items-center justify-between h-12">
                                    <span class="text-sm font-medium text-gray-700 truncate">{{ $previewableFiles[1]['filename'] ?? 'Datei 2' }}</span>
                                    <a href="{{ $previewableFiles[1]['url'] ?? '#' }}" target="_blank" class="text-gray-400 hover:text-gray-600 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </div>
                                <div class="h-full overflow-auto" style="height: calc(100% - 48px);">
                                    @if($previewableFiles[1]['isPdf'])
                                        <iframe 
                                            src="{{ $previewableFiles[1]['url'] }}#toolbar=1&navpanes=0&scrollbar=1" 
                                            class="w-full h-full" 
                                            frameborder="0">
                                        </iframe>
                                    @elseif($previewableFiles[1]['isImage'])
                                        <div class="flex justify-center items-center h-full p-4">
                                            <img 
                                                src="{{ $previewableFiles[1]['url'] }}" 
                                                alt="{{ $previewableFiles[1]['filename'] }}"
                                                class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Files List (if more than 2 previewable files or other files exist) -->
                    @if(count($previewableFiles) > 2 || !empty($otherFiles))
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Weitere Dateien</h3>
                            <ul class="divide-y divide-gray-200">
                                @foreach(array_slice($previewableFiles, 2) as $file)
                                    @php
                                        $fileId = 'file-' . $file['index'];
                                        $filename = $file['filename'] ?? 'Datei';
                                        $fileUrl = $file['url'] ?? '';
                                        $badge = $getSourceBadge($file['source'] ?? 'unknown');
                                    @endphp
                                    <li class="py-3 hover:bg-gray-50 transition-colors rounded-lg px-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3 flex-1">
                                                <span class="text-sm font-medium text-gray-900">{{ $filename }}</span>
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badge['color'] }}">{{ $badge['label'] }}</span>
                                            </div>
                                            @if(!empty($fileUrl))
                                                <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                                    Öffnen
                                                </a>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                                @foreach($otherFiles as $file)
                                    @php
                                        $fileId = 'file-' . $file['index'];
                                        $filename = $file['filename'] ?? 'Datei';
                                        $fileUrl = $file['url'] ?? '';
                                        $badge = $getSourceBadge($file['source'] ?? 'unknown');
                                    @endphp
                                    <li class="py-3 hover:bg-gray-50 transition-colors rounded-lg px-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3 flex-1">
                                                <span class="text-sm font-medium text-gray-900">{{ $filename }}</span>
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badge['color'] }}">{{ $badge['label'] }}</span>
                                            </div>
                                            @if(!empty($fileUrl))
                                                <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                                    Öffnen
                                                </a>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <!-- Regular List View (when less than 2 previewable files) -->
                    <ul class="divide-y divide-gray-200">
                        @foreach($files as $index => $file)
                            @php
                                $fileId = 'file-' . $index;
                                $filename = $file['filename'] ?? 'Datei';
                                $fileUrl = $file['url'] ?? '';
                                $fileType = $file['type'] ?? '';
                                $isPdf = str_contains(strtolower($fileType), 'pdf') || str_ends_with(strtolower($filename), '.pdf');
                                $isImage = str_starts_with(strtolower($fileType), 'image/') || in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $canPreview = $isPdf || $isImage;
                            @endphp
                            <li class="py-3 hover:bg-gray-50 transition-colors rounded-lg px-2">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 mr-4 flex items-center gap-3 flex-1">
                                        <div class="flex-shrink-0">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-sm font-medium text-gray-900 truncate">{{ $filename }}</span>
                                                @php
                                                    $badge = $getSourceBadge($file['source'] ?? 'unknown');
                                                @endphp
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $badge['color'] }}">{{ $badge['label'] }}</span>
                                                @if(!empty($fileType))
                                                    @php
                                                        $mime = (string) $fileType;
                                                        $parts = explode('/', $mime);
                                                        $extLabel = $parts[count($parts)-1] ?? $mime;
                                                        $extLabel = strtoupper($extLabel);
                                                    @endphp
                                                    <span class="text-xs text-gray-500">({{ $extLabel }})</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 flex items-center gap-3">
                                                @if(!empty($file['field']))
                                                    <span>Feld: {{ $file['field'] }}</span>
                                                @endif
                                                @if(isset($file['size']))
                                                    <span>Größe: {{ $formatBytes($file['size']) }}</span>
                                                @endif
                                                @if(isset($file['document_id']))
                                                    <span>ID: {{ $file['document_id'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($canPreview && !empty($fileUrl))
                                            <button 
                                                type="button"
                                                onclick="togglePreview('{{ $fileId }}')"
                                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                id="{{ $fileId }}-toggle">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <span id="{{ $fileId }}-toggle-text">Ausblenden</span>
                                            </button>
                                        @endif
                                        @if(!empty($fileUrl))
                                            <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                Öffnen
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @if($canPreview && !empty($fileUrl))
                                    <div id="{{ $fileId }}-preview" class="mt-4 border border-gray-200 rounded-lg overflow-hidden bg-gray-50" style="max-height: 600px;">
                                        <div class="bg-white border-b border-gray-200 px-4 py-2 flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700">{{ $filename }}</span>
                                            <button 
                                                type="button"
                                                onclick="togglePreview('{{ $fileId }}')"
                                                class="text-gray-400 hover:text-gray-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="overflow-auto" style="max-height: 560px;">
                                            @if($isPdf)
                                                <iframe 
                                                    src="{{ $fileUrl }}#toolbar=1&navpanes=0&scrollbar=1" 
                                                    class="w-full" 
                                                    style="height: 600px; min-height: 400px;"
                                                    frameborder="0">
                                                </iframe>
                                            @elseif($isImage)
                                                <div class="flex justify-center items-center p-4">
                                                    <img 
                                                        src="{{ $fileUrl }}" 
                                                        alt="{{ $filename }}"
                                                        class="max-w-full h-auto rounded-lg shadow-lg"
                                                        style="max-height: 560px;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @else
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
                <div class="flex items-center text-gray-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm">Keine Dateien für diesen Vergleich gefunden</span>
                </div>
            </div>
            @endif
        </div>
        <!-- Combined Statistics Widget -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-blue-100 rounded-lg mr-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 
                                00-2 2v6a2 2 0 002 2h2a2 2 0 
                                002-2zm0 0V9a2 2 0 012-2h2a2 2 0 
                                012 2v10m-6 0a2 2 0 002 2h2a2 2 0 
                                002-2m0 0V5a2 2 0 012-2h2a2 2 0 
                                012 2v14a2 2 0 01-2 2h-2a2 2 0 
                                01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Zusammenfassung der Statistiken</h2>
                </div>
                @php
                    $summary = $record->full_payload['data']['summary'] ?? [];
                    $totalItems = $summary['total_items'] ?? 0;
                    $itemsComparison = $record->full_payload['data']['items_comparison'] ?? [];
                    $groupedItems = collect($itemsComparison)->groupBy('verdict');
                    $totalItemsComparison = count($itemsComparison);
                @endphp
    
                <!-- 3x2 Layout: Using flexbox with explicit rows -->
                <div class="space-y-3">
                    <!-- First Row: 3 items -->
                    <div class="flex gap-3">
                        <!-- Gesamtanzahl -->
                        <div class="text-center p-3 rounded border flex-1 flex flex-col justify-center" style="background-color: white; border-color: #d1d5db; min-height: 100px;">
                            <div class="text-2xl font-bold" style="color: #2563eb;">{{ $totalItems }}</div>
                            <div class="text-xs font-medium text-gray-600">Gesamtanzahl</div>
                        </div>
                        <!-- Übereinstimmung -->
                        <div class="text-center p-3 rounded border flex-1 flex flex-col justify-center" style="background-color: white; border-color: #d1d5db; min-height: 100px;">
                            <div class="text-2xl font-bold" style="color: #16a34a;">{{ $groupedItems->get('Übereinstimmung', collect())->count() }}</div>
                            <div class="text-xs font-medium text-gray-600">Übereinstimmung</div>
                            <div class="text-xs" style="color: #16a34a;">{{ $totalItemsComparison > 0 ? round(($groupedItems->get('Übereinstimmung', collect())->count() / $totalItemsComparison) * 100, 1) : 0 }}%</div>
                        </div>
                        <!-- Keine Übereinstimmung -->
                        <div class="text-center p-3 rounded border flex-1 flex flex-col justify-center" style="background-color: white; border-color: #d1d5db; min-height: 100px;">
                            <div class="text-2xl font-bold" style="color: #dc2626;">{{ $groupedItems->get('Keine Übereinstimmung', collect())->count() }}</div>
                            <div class="text-xs font-medium text-gray-600">Keine Übereinstimmung</div>
                            <div class="text-xs" style="color: #dc2626;">{{ $totalItemsComparison > 0 ? round(($groupedItems->get('Keine Übereinstimmung', collect())->count() / $totalItemsComparison) * 100, 1) : 0 }}%</div>
                        </div>
                    </div>
                    <!-- Second Row: 3 items -->
                    <div class="flex gap-3">
                        <!-- Prüfung erforderlich -->
                        <div class="text-center p-3 rounded border flex-1 flex flex-col justify-center" style="background-color: white; border-color: #d1d5db; min-height: 100px;">
                            <div class="text-2xl font-bold" style="color: #d97706;">{{ $groupedItems->get('Prüfung erforderlich', collect())->count() }}</div>
                            <div class="text-xs font-medium text-gray-600">Prüfung erforderlich</div>
                            <div class="text-xs" style="color: #d97706;">{{ $totalItemsComparison > 0 ? round(($groupedItems->get('Prüfung erforderlich', collect())->count() / $totalItemsComparison) * 100, 1) : 0 }}%</div>
                        </div>
                        <!-- Fehlt in Bestellung -->
                        <div class="text-center p-3 rounded border flex-1 flex flex-col justify-center" style="background-color: white; border-color: #d1d5db; min-height: 100px;">
                            <div class="text-2xl font-bold" style="color: #3b82f6;">{{ $groupedItems->get('Fehlt in Bestellung', collect())->count() }}</div>
                            <div class="text-xs font-medium text-gray-600">Fehlt in Bestellung</div>
                            <div class="text-xs" style="color: #3b82f6;">{{ $totalItemsComparison > 0 ? round(($groupedItems->get('Fehlt in Bestellung', collect())->count() / $totalItemsComparison) * 100, 1) : 0 }}%</div>
                        </div>
                        <!-- Fehlt in Auftragsbestätigung -->
                        <div class="text-center p-3 rounded border flex-1 flex flex-col justify-center" style="background-color: white; border-color: #d1d5db; min-height: 100px;">
                            <div class="text-2xl font-bold" style="color: #8b5cf6;">{{ $groupedItems->get('Fehlt in Auftragsbestätigung', collect())->count() }}</div>
                            <div class="text-xs font-medium text-gray-600">Fehlt in Auftragsbestätigung</div>
                            <div class="text-xs" style="color: #8b5cf6;">{{ $totalItemsComparison > 0 ? round(($groupedItems->get('Fehlt in Auftragsbestätigung', collect())->count() / $totalItemsComparison) * 100, 1) : 0 }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Widgets Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            @php
                $matched = $groupedItems->get('Übereinstimmung', collect())->count();
                $mismatched = $groupedItems->get('Keine Übereinstimmung', collect())->count();
                $review = $groupedItems->get('Prüfung erforderlich', collect())->count();
                $missingOrder = $groupedItems->get('Fehlt in Bestellung', collect())->count();
                $missingAB = $groupedItems->get('Fehlt in Auftragsbestätigung', collect())->count();
                
                $headerComparison = $record->full_payload['data']['header_comparison'] ?? [];
                $headerRows = $headerComparison['rows'] ?? [];
                $headerGrouped = collect($headerRows)->groupBy('verdict');
                $headerMatched = $headerGrouped->get('Übereinstimmung', collect())->count();
                $headerMismatched = $headerGrouped->get('Keine Übereinstimmung', collect())->count();
                $headerReview = $headerGrouped->get('Prüfung erforderlich', collect())->count();
                $headerTotal = count($headerRows);
            @endphp

            <!-- Widget 1: Items Comparison Progress -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-indigo-100 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Artikelvergleich</h3>
                </div>
                <div class="space-y-4">
                    <div class="text-sm text-gray-600 mb-3">
                        <span class="font-semibold">{{ $totalItemsComparison }}</span> von <span class="font-semibold">{{ $totalItems }}</span> Positionen geprüft
                    </div>
                    
                    @if($totalItemsComparison > 0)
                        <!-- Matched -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-700">Übereinstimmung</span>
                                <span class="text-sm font-semibold" style="color: #16a34a;">{{ $matched }} ({{ round(($matched / $totalItemsComparison) * 100, 1) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="background-color: #16a34a; width: {{ ($matched / $totalItemsComparison) * 100 }}%"></div>
                            </div>
                        </div>

                        <!-- Mismatched -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-700">Keine Übereinstimmung</span>
                                <span class="text-sm font-semibold" style="color: #dc2626;">{{ $mismatched }} ({{ round(($mismatched / $totalItemsComparison) * 100, 1) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="background-color: #dc2626; width: {{ ($mismatched / $totalItemsComparison) * 100 }}%"></div>
                            </div>
                        </div>

                        <!-- Review Required -->
                        @if($review > 0)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-700">Prüfung erforderlich</span>
                                <span class="text-sm font-semibold" style="color: #d97706;">{{ $review }} ({{ round(($review / $totalItemsComparison) * 100, 1) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" style="background-color: #d97706; width: {{ ($review / $totalItemsComparison) * 100 }}%"></div>
                            </div>
                        </div>
                        @endif

                        <!-- Missing Items -->
                        @if($missingOrder > 0 || $missingAB > 0)
                        <div class="pt-2 border-t border-gray-200">
                            <div class="text-xs text-gray-500 mb-2">Fehlende Positionen:</div>
                            @if($missingOrder > 0)
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-gray-600">Fehlt in Bestellung</span>
                                <span class="text-xs font-semibold" style="color: #3b82f6;">{{ $missingOrder }}</span>
                            </div>
                            @endif
                            @if($missingAB > 0)
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-gray-600">Fehlt in Auftragsbestätigung</span>
                                <span class="text-xs font-semibold" style="color: #8b5cf6;">{{ $missingAB }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    @else
                        <div class="text-sm text-gray-500 text-center py-4">Keine Vergleichsdaten verfügbar</div>
                    @endif
                </div>
            </div>

            <!-- Widget 2: Header Comparison Status -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-cyan-100 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Kopfvergleich</h3>
                </div>
                <div class="space-y-4">
                    @if($headerTotal > 0)
                        <div class="text-sm text-gray-600 mb-3">
                            <span class="font-semibold">{{ $headerTotal }}</span> Felder verglichen
                        </div>

                        <!-- Header Status Overview -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <!-- Matched -->
                            <div class="text-center p-3 rounded-lg" style="background-color: #dcfce7; border: 2px solid #16a34a;">
                                <div class="text-2xl font-bold" style="color: #166534;">{{ $headerMatched }}</div>
                                <div class="text-xs font-medium" style="color: #166534;">Übereinst.</div>
                            </div>
                            <!-- Mismatched -->
                            <div class="text-center p-3 rounded-lg" style="background-color: #fecaca; border: 2px solid #dc2626;">
                                <div class="text-2xl font-bold" style="color: #991b1b;">{{ $headerMismatched }}</div>
                                <div class="text-xs font-medium" style="color: #991b1b;">Abweichung</div>
                            </div>
                            <!-- Review -->
                            <div class="text-center p-3 rounded-lg" style="background-color: #fef3c7; border: 2px solid #d97706;">
                                <div class="text-2xl font-bold" style="color: #92400e;">{{ $headerReview }}</div>
                                <div class="text-xs font-medium" style="color: #92400e;">Prüfung</div>
                            </div>
                        </div>

                        <!-- Header Progress Bar -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Gesamtstatus</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    @if($headerMismatched == 0 && $headerReview == 0)
                                        <span style="color: #16a34a;">✓ Vollständig</span>
                                    @elseif($headerMismatched > 0)
                                        <span style="color: #dc2626;">⚠ Abweichungen</span>
                                    @else
                                        <span style="color: #d97706;">⏳ Prüfung nötig</span>
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden flex">
                                @if($headerMatched > 0)
                                    <div class="h-full transition-all duration-500" style="background-color: #16a34a; width: {{ ($headerMatched / $headerTotal) * 100 }}%"></div>
                                @endif
                                @if($headerMismatched > 0)
                                    <div class="h-full transition-all duration-500" style="background-color: #dc2626; width: {{ ($headerMismatched / $headerTotal) * 100 }}%"></div>
                                @endif
                                @if($headerReview > 0)
                                    <div class="h-full transition-all duration-500" style="background-color: #d97706; width: {{ ($headerReview / $headerTotal) * 100 }}%"></div>
                                @endif
                            </div>
                        </div>

                        <!-- Header Overall Verdict -->
                        @php
                            $headerOverall = $headerComparison['overall']['verdict'] ?? null;
                        @endphp
                        @if($headerOverall)
                        <div class="mt-4 p-3 rounded-lg" style="background-color: {{ $headerOverall === 'Übereinstimmung' ? '#dcfce7' : ($headerOverall === 'Keine Übereinstimmung' ? '#fecaca' : '#fef3c7') }}; border-left: 4px solid {{ $headerOverall === 'Übereinstimmung' ? '#16a34a' : ($headerOverall === 'Keine Übereinstimmung' ? '#dc2626' : '#d97706') }};">
                            <div class="text-sm font-semibold" style="color: {{ $headerOverall === 'Übereinstimmung' ? '#166534' : ($headerOverall === 'Keine Übereinstimmung' ? '#991b1b' : '#92400e') }};">
                                Gesamturteil: {{ $headerOverall }}
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="text-sm text-gray-500 text-center py-4">Keine Kopfvergleichsdaten verfügbar</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Header Table -->
        <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-purple-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    Bestellkopf-Informationen
                    </h2>
                @php
                    $orderHeaderData = $record->full_payload['data']['order_header'] ?? [];
                    $orderHeaderKeys = array_keys($orderHeaderData);
                @endphp
                </div>
                <!-- Show first 3 rows by default -->
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-purple-200">
                            <tr>
                                @php
                                    $orderHeaderData = $record->full_payload['data']['order_header'] ?? [];
                                    $orderHeaderKeys = array_keys($orderHeaderData);
                                @endphp
                                @foreach($orderHeaderKeys as $key)
                                    <th class="px-4 py-3 text-left text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap bg-purple-200">
                                        {{ str_replace('_', ' ', $key) }}
                                    </th>
                                @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                    <tr class="hover:bg-purple-50 transition-colors">
                                @foreach($orderHeaderKeys as $key)
                                    <td class="px-4 py-4 text-sm text-gray-900 whitespace-nowrap">
                                        {{ $orderHeaderData[$key] ?? 'N/A' }}
                                        </td>
                                @endforeach
                            </tr>
                            </tbody>
                        </table>
                </div>
            </div>

        <!-- AB Header Table -->
        <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-orange-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    AB-Kopf-Informationen
                    </h2>
                @php
                    $abHeaderData = $record->full_payload['data']['ab_header'] ?? [];
                    $abHeaderKeys = array_keys($abHeaderData);
                @endphp
                </div>
                <!-- Show first 3 rows by default -->
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-orange-200">
                            <tr>
                                @php
                                    $abHeaderData = $record->full_payload['data']['ab_header'] ?? [];
                                    $abHeaderKeys = array_keys($abHeaderData);
                                @endphp
                                @foreach($abHeaderKeys as $key)
                                    <th class="px-4 py-3 text-left text-sm font-bold text-orange-800 uppercase tracking-wider whitespace-nowrap bg-orange-200">
                                        {{ str_replace('_', ' ', $key) }}
                                    </th>
                                @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="hover:bg-orange-50 transition-colors">
                                    @foreach($abHeaderKeys as $key)
                                        <td class="px-4 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            {{ $abHeaderData[$key] ?? 'N/A' }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                </div>
            </div>

        <!-- Header Comparison Details Table -->
        <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-cyan-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    Details der Kopf-Feldvergleiche
                    </h2>
                @php
                    $headerComparison = $record->full_payload['data']['header_comparison'] ?? [];
                    $headerComparisonRows = $headerComparison['rows'] ?? [];
                @endphp
                <button onclick="toggleHeaderComparisonDropdown()" class="flex items-center text-cyan-600 hover:text-cyan-800 text-sm font-medium bg-cyan-50 hover:bg-cyan-100 px-3 py-2 rounded-lg border border-cyan-200 transition-all duration-200">
                        <span id="header-comparison-toggle">Dropdown öffnen</span>
                        <svg id="header-comparison-icon" class="w-4 h-4 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                <!-- Collapsible table content -->
                <div id="header-comparison-content" class="overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0px; opacity: 0;">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-cyan-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-bold text-cyan-800 uppercase tracking-wider">Field</th>
                                <th class="px-6 py-3 text-left text-sm font-bold text-cyan-800 uppercase tracking-wider">Order Value</th>
                                <th class="px-6 py-3 text-left text-sm font-bold text-cyan-800 uppercase tracking-wider">Confirmation Value</th>
                                <th class="px-6 py-3 text-left text-sm font-bold text-cyan-800 uppercase tracking-wider">Verdict</th>
                                <th class="px-6 py-3 text-left text-sm font-bold text-cyan-800 uppercase tracking-wider">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($headerComparisonRows as $row)
                                <tr class="hover:bg-cyan-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800">
                                            {{ str_replace('_', ' ', $row['field']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $row['order_value'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $row['confirmation_value'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                              style="{{ $row['verdict'] === 'Übereinstimmung' ? 'background-color: #dcfce7; color: #166534;' : ($row['verdict'] === 'Keine Übereinstimmung' ? 'background-color: #fecaca; color: #991b1b;' : ($row['verdict'] === 'Prüfung erforderlich' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #e0e7ff; color: #1e3a8a;')) }}">
                                            {{ $row['verdict'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $row['reason'] }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        <!-- Items Comparison Details Table -->
        <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-indigo-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    Details der Artikelvergleiche
                    </h2>
                
            </div>
            
            <!-- Filter Section -->
            <div class="mb-6 p-4 bg-white rounded-lg border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-700">Nach Urteil filtern</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterItems('all')" class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        Alle
                    </button>
                    <button onclick="filterItems('Übereinstimmung')" class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        Übereinstimmung
                    </button>
                    <button onclick="filterItems('Keine Übereinstimmung')" class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        Keine Übereinstimmung
                    </button>
                    <button onclick="filterItems('Prüfung erforderlich')" class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        Prüfung erforderlich
                    </button>
                    <button onclick="filterItems('Fehlt in Bestellung')" class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        Fehlt in Bestellung
                    </button>
                    <button onclick="filterItems('Fehlt in Auftragsbestätigung')" class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        Fehlt in Auftragsbestätigung
                    </button>
                </div>
            </div>
                <!-- Show first 2 rows by default -->
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-indigo-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-bold text-indigo-800 uppercase tracking-wider">Urteil</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-indigo-800 uppercase tracking-wider">Position</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-indigo-800 uppercase tracking-wider">Typnummer</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-indigo-800 uppercase tracking-wider">Beschreibung</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-indigo-800 uppercase tracking-wider">Abmessungen</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-indigo-800 uppercase tracking-wider">Preis</th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-indigo-800 uppercase tracking-wider">Grund</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                $firstTwoItems = array_slice($itemsComparison, 0, 2);
                                @endphp
                            @foreach($firstTwoItems as $item)
                                <tr class="hover:bg-indigo-50 transition-colors" data-verdict="{{ $item['verdict'] }}">
                                    <td class="px-4 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                              style="{{ $item['verdict'] === 'Übereinstimmung' ? 'background-color: #dcfce7; color: #166534;' : ($item['verdict'] === 'Keine Übereinstimmung' ? 'background-color: #fecaca; color: #991b1b;' : ($item['verdict'] === 'Prüfung erforderlich' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #e0e7ff; color: #1e3a8a;')) }}">
                                            {{ $item['verdict'] }}
                                            </span>
                                        </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>
                                            <div class="font-medium">Order: {{ $item['pos_f1'] ?? $item['Pos. (Bestellung)'] ?? 'N/A' }}</div>
                                            <div class="text-gray-500">AB: {{ $item['pos_f2'] ?? $item['Pos. (AB)'] ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>
                                            <div class="font-medium">Order: {{ $item['typ_nr_f1'] ?? $item['Typ-Nr. (Bestellung)'] ?? 'N/A' }}</div>
                                            <div class="text-gray-500">AB: {{ $item['typ_nr_f2'] ?? $item['Typ-Nr. (AB)'] ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        <div>
                                            <div class="font-medium">Order: {{ $item['artikeltext_f1'] ?? $item['Artikeltext (Bestellung)'] ?? 'N/A' }}</div>
                                            <div class="text-gray-500">AB: {{ $item['artikeltext_f2'] ?? $item['Artikeltext (AB)'] ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>
                                            <div>W: {{ $item['breite_f1'] ?? $item['Breite (Bestellung)'] ?? 'N/A' }} / {{ $item['breite_f2'] ?? $item['Breite (AB)'] ?? 'N/A' }}</div>
                                            <div>D: {{ $item['tiefe_f1'] ?? $item['Tiefe (Bestellung)'] ?? 'N/A' }} / {{ $item['tiefe_f2'] ?? $item['Tiefe (AB)'] ?? 'N/A' }}</div>
                                            <div>H: {{ $item['hoehe_f1'] ?? $item['Höhe (Bestellung)'] ?? 'N/A' }} / {{ $item['hoehe_f2'] ?? $item['Höhe (AB)'] ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>
                                            <div>Net: {{ $item['price_netto_f1'] ?? $item['Preis Netto (AB)'] ?? 'N/A' }}</div>
                                            <div>Gross: {{ $item['price_f1'] ?? $item['Preis Brutto (AB)'] ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{ $item['reason'] ?? $item['Begründung'] ?? 'N/A' }}
                                    </td>
                                    </tr>
                                @endforeach
                            <!-- Show remaining rows when expanded -->
                            @if(count($itemsComparison) > 2)
                <div id="items-details-content" class="hidden">
                                @php
                                    $remainingItems = array_slice($itemsComparison, 2);
                                @endphp
                                @foreach($remainingItems as $item)
                                    <tr class="hover:bg-indigo-50 transition-colors" data-verdict="{{ $item['verdict'] }}">
                                        <td class="px-4 py-4">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" 
                                                  style="{{ $item['verdict'] === 'Übereinstimmung' ? 'background-color: #dcfce7; color: #166534;' : ($item['verdict'] === 'Keine Übereinstimmung' ? 'background-color: #fecaca; color: #991b1b;' : ($item['verdict'] === 'Prüfung erforderlich' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #e0e7ff; color: #1e3a8a;')) }}">
                                                {{ $item['verdict'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">Order: {{ $item['pos_f1'] ?? $item['Pos. (Bestellung)'] ?? 'N/A' }}</div>
                                                <div class="text-gray-500">AB: {{ $item['pos_f2'] ?? $item['Pos. (AB)'] ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">Order: {{ $item['typ_nr_f1'] ?? $item['Typ-Nr. (Bestellung)'] ?? 'N/A' }}</div>
                                                <div class="text-gray-500">AB: {{ $item['typ_nr_f2'] ?? $item['Typ-Nr. (AB)'] ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">Order: {{ $item['artikeltext_f1'] ?? $item['Artikeltext (Bestellung)'] ?? 'N/A' }}</div>
                                                <div class="text-gray-500">AB: {{ $item['artikeltext_f2'] ?? $item['Artikeltext (AB)'] ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div>W: {{ $item['breite_f1'] ?? $item['Breite (Bestellung)'] ?? 'N/A' }} / {{ $item['breite_f2'] ?? $item['Breite (AB)'] ?? 'N/A' }}</div>
                                                <div>D: {{ $item['tiefe_f1'] ?? $item['Tiefe (Bestellung)'] ?? 'N/A' }} / {{ $item['tiefe_f2'] ?? $item['Tiefe (AB)'] ?? 'N/A' }}</div>
                                                <div>H: {{ $item['hoehe_f1'] ?? $item['Höhe (Bestellung)'] ?? 'N/A' }} / {{ $item['hoehe_f2'] ?? $item['Höhe (AB)'] ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div>Net: {{ $item['price_netto_f1'] ?? $item['Preis Netto (AB)'] ?? 'N/A' }}</div>
                                                <div>Gross: {{ $item['price_f1'] ?? $item['Preis Brutto (AB)'] ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">
                                            {{ $item['reason'] ?? $item['Begründung'] ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </div>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>

    </div>

    <!-- JavaScript for Show More/Less functionality and filtering -->
    <script>
        function togglePreview(fileId) {
            const preview = document.getElementById(fileId + '-preview');
            const toggleText = document.getElementById(fileId + '-toggle-text');
            
            if (preview) {
                if (preview.classList.contains('hidden')) {
                    // Show preview
                    preview.classList.remove('hidden');
                    if (toggleText) {
                        toggleText.textContent = 'Ausblenden';
                    }
                } else {
                    // Hide preview
                    preview.classList.add('hidden');
                    if (toggleText) {
                        toggleText.textContent = 'Anzeigen';
                    }
                }
            }
        }

        // Side-by-side viewer functions
        let isResizing = false;
        let leftPanel = null;
        let rightPanel = null;
        let container = null;
        let splitter = null;

        function startResize(event) {
            event.preventDefault();
            event.stopPropagation();
            
            isResizing = true;
            leftPanel = document.getElementById('left-panel');
            rightPanel = document.getElementById('right-panel');
            container = document.getElementById('side-by-side-viewer');
            splitter = document.getElementById('splitter');
            
            if (!leftPanel || !rightPanel || !container || !splitter) return;
            
            // Get initial positions
            const containerRect = container.getBoundingClientRect();
            const leftWidth = leftPanel.offsetWidth;
            const splitterWidth = splitter.offsetWidth;
            
            // Add global event listeners
            document.addEventListener('mousemove', handleResize);
            document.addEventListener('mouseup', stopResize);
            
            // Prevent text selection and default behaviors
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            document.body.style.pointerEvents = 'none';
            
            // Make splitter more visible during resize
            splitter.style.backgroundColor = '#3b82f6';
            
            // Prevent iframe interactions
            const iframes = container.querySelectorAll('iframe');
            iframes.forEach(iframe => {
                iframe.style.pointerEvents = 'none';
            });
        }

        function handleResize(event) {
            if (!isResizing || !leftPanel || !rightPanel || !container || !splitter) return;
            
            event.preventDefault();
            
            const containerRect = container.getBoundingClientRect();
            const containerWidth = containerRect.width;
            const splitterWidth = splitter.offsetWidth;
            const mouseX = event.clientX - containerRect.left;
            
            // Calculate available width (container minus splitter)
            const availableWidth = containerWidth - splitterWidth;
            
            // Calculate new widths (with minimum 20% and maximum 80% for each panel)
            const minWidth = availableWidth * 0.2;
            const maxWidth = availableWidth * 0.8;
            
            // Ensure mouse position is within bounds (account for splitter position)
            const clampedX = Math.max(minWidth, Math.min(maxWidth, mouseX - (splitterWidth / 2)));
            
            // Calculate percentages based on available width
            const leftPercent = (clampedX / availableWidth) * 100;
            const rightPercent = ((availableWidth - clampedX) / availableWidth) * 100;
            
            // Apply new widths
            leftPanel.style.width = leftPercent + '%';
            leftPanel.style.flexShrink = '0';
            rightPanel.style.width = rightPercent + '%';
            rightPanel.style.flexShrink = '0';
        }

        function stopResize() {
            if (!isResizing) return;
            
            isResizing = false;
            
            // Remove event listeners
            document.removeEventListener('mousemove', handleResize);
            document.removeEventListener('mouseup', stopResize);
            
            // Restore styles
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            document.body.style.pointerEvents = '';
            
            // Restore splitter color
            if (splitter) {
                splitter.style.backgroundColor = '';
            }
            
            // Restore iframe interactions
            if (container) {
                const iframes = container.querySelectorAll('iframe');
                iframes.forEach(iframe => {
                    iframe.style.pointerEvents = '';
                });
            }
        }

        function toggleSideBySideViewer() {
            const viewer = document.getElementById('side-by-side-viewer');
            const toggleBtn = document.getElementById('viewer-toggle-btn');
            const toggleText = document.getElementById('viewer-toggle-text');
            const toggleIcon = document.getElementById('viewer-toggle-icon');
            
            if (!viewer || !toggleText || !toggleIcon) return;
            
            if (viewer.style.display === 'none') {
                // Show viewer
                viewer.style.display = '';
                toggleText.textContent = 'Viewer schließen';
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
            } else {
                // Hide viewer
                viewer.style.display = 'none';
                toggleText.textContent = 'Viewer öffnen';
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }

        function toggleHeaderComparisonDropdown() {
            const content = document.getElementById('header-comparison-content');
            const toggle = document.getElementById('header-comparison-toggle');
            const icon = document.getElementById('header-comparison-icon');
            
            if (content) {
                if (content.style.maxHeight && content.style.maxHeight !== '0px') {
                    // Collapse
                    content.style.maxHeight = '0px';
                    content.style.opacity = '0';
                    toggle.textContent = 'Dropdown öffnen';
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    // Expand
                    content.style.maxHeight = content.scrollHeight + 'px';
                    content.style.opacity = '1';
                    toggle.textContent = 'Dropdown schließen';
                    icon.style.transform = 'rotate(180deg)';
                }
            }
        }

        function toggleSection(sectionId) {
            const content = document.getElementById(sectionId + '-content');
            const toggle = document.getElementById(sectionId + '-toggle');
            const icon = toggle.parentElement.querySelector('svg');
            
            if (content) {
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                    toggle.textContent = 'Weniger anzeigen';
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                    toggle.textContent = 'Mehr anzeigen';
                icon.style.transform = 'rotate(0deg)';
                }
            }
        }
        
        function filterItems(verdict) {
            const rows = document.querySelectorAll('[data-verdict]');
            const buttons = document.querySelectorAll('[onclick^="filterItems"]');
            
            // Update button styles
            buttons.forEach(button => {
                const buttonText = button.textContent.trim();
                const isActive = buttonText === (verdict === 'all' ? 'Alle' : verdict);
                
                // Reset all buttons to default state
                button.style.boxShadow = '0 1px 2px 0 rgb(0 0 0 / 0.05)';
                button.style.transform = 'translateY(0px)';
                
                if (buttonText === 'Alle') {
                    button.style.borderColor = isActive ? '#6366f1' : '#6366f1';
                    button.style.backgroundColor = isActive ? '#e0e7ff' : 'white';
                    button.style.color = isActive ? '#4338ca' : '#4338ca';
                } else if (buttonText === 'Übereinstimmung') {
                    button.style.borderColor = isActive ? '#16a34a' : '#86efac';
                    button.style.backgroundColor = isActive ? '#dcfce7' : 'white';
                    button.style.color = isActive ? '#15803d' : '#166534';
                } else if (buttonText === 'Keine Übereinstimmung') {
                    button.style.borderColor = isActive ? '#dc2626' : '#fca5a5';
                    button.style.backgroundColor = isActive ? '#fecaca' : 'white';
                    button.style.color = isActive ? '#b91c1c' : '#991b1b';
                } else if (buttonText === 'Prüfung erforderlich') {
                    button.style.borderColor = isActive ? '#d97706' : '#fde047';
                    button.style.backgroundColor = isActive ? '#fef3c7' : 'white';
                    button.style.color = isActive ? '#b45309' : '#92400e';
                } else if (buttonText === 'Fehlt in Bestellung') {
                    button.style.borderColor = isActive ? '#1e40af' : '#93c5fd';
                    button.style.backgroundColor = isActive ? '#dbeafe' : 'white';
                    button.style.color = isActive ? '#1e3a8a' : '#1e40af';
                } else if (buttonText === 'Fehlt in Auftragsbestätigung') {
                    button.style.borderColor = isActive ? '#7c3aed' : '#c4b5fd';
                    button.style.backgroundColor = isActive ? '#ede9fe' : 'white';
                    button.style.color = isActive ? '#6d28d9' : '#7c3aed';
                }
                
                // Add active state styling
                if (isActive) {
                    button.style.boxShadow = '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -1px rgb(0 0 0 / 0.06)';
                    button.style.transform = 'translateY(-2px)';
                }
            });
            
            // Show/hide rows
            rows.forEach(row => {
                if (verdict === 'all' || row.getAttribute('data-verdict') === verdict) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</x-filament-panels::page>