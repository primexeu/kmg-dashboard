<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Küchenabgleich Genauigkeit
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Genauigkeitsrate Gauge -->
            <div class="text-center">
                <div class="relative inline-flex items-center justify-center w-32 h-32">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
                        <!-- Hintergrund Kreis -->
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            stroke="currentColor"
                            stroke-width="8"
                            fill="transparent"
                            class="text-gray-200"
                        />
                        <!-- Fortschritts Kreis -->
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            stroke="currentColor"
                            stroke-width="8"
                            fill="transparent"
                            stroke-dasharray="{{ 2 * pi() * 40 }}"
                            stroke-dashoffset="{{ 2 * pi() * 40 * (1 - $this->getAccuracyData()['accuracy_rate'] / 100) }}"
                            class="text-{{ $this->getAccuracyData()['accuracy_rate'] >= 80 ? 'green' : ($this->getAccuracyData()['accuracy_rate'] >= 60 ? 'yellow' : 'red') }}-500 transition-all duration-300"
                        />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-gray-900">
                            {{ $this->getAccuracyData()['accuracy_rate'] }}%
                        </span>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-600">Genauigkeitsrate</p>
            </div>

            <!-- Statistiken -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">
                            {{ $this->getAccuracyData()['successful_comparisons'] }}
                        </div>
                        <div class="text-sm text-green-700">Übereinstimmungen</div>
                    </div>
                    
                    <div class="text-center p-3 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">
                            {{ $this->getAccuracyData()['mismatched_comparisons'] }}
                        </div>
                        <div class="text-sm text-red-700">Abweichungen</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-yellow-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ $this->getAccuracyData()['needs_review_comparisons'] }}
                        </div>
                        <div class="text-sm text-yellow-700">Prüfung erforderlich</div>
                    </div>
                    
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-600">
                            {{ $this->getAccuracyData()['pending_comparisons'] }}
                        </div>
                        <div class="text-sm text-gray-700">Ausstehend</div>
                    </div>
                </div>
                
                <div class="pt-2 border-t">
                    <div class="text-center">
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $this->getAccuracyData()['total_comparisons'] }}
                        </div>
                        <div class="text-sm text-gray-600">Gesamt Küchenabgleiche</div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
