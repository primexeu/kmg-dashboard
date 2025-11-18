<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>KI für Küchenabgleich Dashboard</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col antialiased bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <!-- Top nav -->
    <header class="sticky top-0 z-30 backdrop-blur supports-[backdrop-filter]:bg-white/60 bg-white/90 dark:bg-gray-900/80 border-b border-gray-100 dark:border-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('welcome') }}" class="flex items-center gap-2 font-semibold">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-tr from-red-600 via-orange-500 to-red-700 shadow-sm"></span>
                    <span>KI für Küchenabgleich</span>
                </a>
                <nav class="hidden md:flex items-center gap-6"></nav>
                <div class="flex items-center gap-3">
                    <!-- Language Toggle -->
                    <x-language-toggle />
                    
                    <a href="{{ url('/admin/login') }}"
                       class="inline-flex items-center rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800">
                        {{ __('messages.nav.log_in') }}
                    </a>
                    <a href="{{ url('/admin/register') }}"
                       class="inline-flex items-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-400">
                        {{ __('messages.nav.sign_up') }}
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="flex-1">
        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-red-50 via-orange-50 to-red-50 dark:from-red-900/20 dark:via-orange-900/10 dark:to-red-900/10"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
                <div class="grid gap-12 lg:grid-cols-2 items-center">
                    <div>
                        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight">
                            {{ __('messages.hero.title') }}
                            <span class="bg-gradient-to-tr from-red-600 via-orange-600 to-red-600 bg-clip-text text-transparent">OrderMatch</span>
                        </h1>
                        <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                            {{ __('messages.hero.subtitle') }}
                        </p>
                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <a href="{{ url('/admin') }}"
                               class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-red-500">
                                {{ __('messages.nav.go_to_dashboard') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 12h14m-7-7l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Feature cards -->
                    <div id="features" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Orders -->
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-xl bg-red-50 dark:bg-red-900/30">
                                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 14l6-6M9 8h.01M15 14h.01M7 3h10a2 2 0 012 2v13.5a.5.5 0 01-.8.4L16 17l-2.2 1.9a1 1 0 01-1.2 0L10.4 17 8 18.9a.5.5 0 01-.8-.4V5a2 2 0 012-2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">{{ __('messages.features.orders.title') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.features.orders.description') }}</p>
                                    </div>
                                </div>
                                <a href="{{ url('/admin/orders') }}" class="text-sm font-medium text-red-600 hover:underline dark:text-red-400">{{ __('messages.features.orders.open') }}</a>
                            </div>
                        </div>

                        <!-- Order Confirmations -->
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-xl bg-orange-50 dark:bg-orange-900/30">
                                        <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12l2 2 4-4M7 3h6l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">{{ __('messages.features.confirmations.title') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.features.confirmations.description') }}</p>
                                    </div>
                                </div>
                                <a href="{{ url('/admin/order-confirmations') }}" class="text-sm font-medium text-red-600 hover:underline dark:text-red-400">{{ __('messages.features.confirmations.open') }}</a>
                            </div>
                        </div>

                        <!-- OrderMatches -->
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30">
                                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">{{ __('messages.features.matches.title') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.features.matches.description') }}</p>
                                    </div>
                                </div>
                                <a href="{{ url('/admin/matches') }}" class="text-sm font-medium text-red-600 hover:underline dark:text-red-400">{{ __('messages.features.matches.open') }}</a>
                            </div>
                        </div>

                        <!-- Exceptions -->
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-900/30">
                                        <svg class="h-6 w-6 text-rose-600 dark:text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 9v4m0 4h.01M10.29 3.86l-8.48 14.7A2 2 0 003.53 22h16.94a2 2 0 001.72-3.44l-8.48-14.7a2 2 0 00-3.42 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">{{ __('messages.features.exceptions.title') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.features.exceptions.description') }}</p>
                                    </div>
                                </div>
                                <a href="{{ url('/admin/exceptions') }}" class="text-sm font-medium text-red-600 hover:underline dark:text-red-400">{{ __('messages.features.exceptions.open') }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Docs / CTA -->
                <div id="docs" class="mt-16">
                    <div class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 p-6 lg:p-8 bg-white/70 dark:bg-gray-950/70">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold">{{ __('messages.docs.title') }}</h3>
                            </div>
                            <a href="{{ url('/docs') }}"
                               class="inline-flex items-center rounded-xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 px-4 py-2.5 text-sm font-semibold hover:opacity-90">
                                {{ __('messages.docs.open') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-200 dark:border-gray-800 bg-white/70 dark:bg-gray-950/70">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <!-- Partner row -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-6">
                <div class="flex items-center gap-3">
                    
                    <span class="text-sm font-semibold tracking-wide" style="display:none;">PrimEx</span>
                    
                    <span class="text-gray-400"> PrimEx × KMG</span>
                    
                    <span class="text-sm font-semibold tracking-wide" style="display:none;">KMG</span>
                    
                </div>

                <!-- "Excellence" badge -->
                <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 17.27l6.18 3.73-1.64-7.03L21 9.24l-7.19-.61L12 2 10.19 8.63 3 9.24l4.46 4.73-1.64 7.03L12 17.27z"/>
                    </svg>
                    <span class="font-medium">{{ __('messages.footer.excellence') }}</span>
                </div>

            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
                <p>{{ __('messages.footer.copyright', ['year' => now()->year]) }}</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-red-600 dark:hover:text-red-400">{{ __('messages.footer.terms') }}</a>
                    <a href="#" class="hover:text-red-600 dark:hover:text-red-400">{{ __('messages.footer.privacy') }}</a>
                    <a href="{{ url('/admin/login') }}" class="hover:text-red-600 dark:hover:text-red-400">{{ __('messages.nav.agent_login') }}</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
