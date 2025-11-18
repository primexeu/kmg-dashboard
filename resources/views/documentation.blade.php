<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>KMG · Documentation</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <!-- Top nav -->
    <header class="sticky top-0 z-30 backdrop-blur supports-[backdrop-filter]:bg-white/60 bg-white/90 dark:bg-gray-900/80 border-b border-gray-100 dark:border-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('welcome') }}" class="flex items-center gap-2 font-semibold">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-tr from-red-600 via-orange-500 to-red-700 shadow-sm"></span>
                    <span>KMG · Zumbrock</span>
                </a>
                <nav class="hidden md:flex items-center gap-6">
                    <a href="{{ route('welcome') }}" class="text-sm hover:text-red-600 dark:hover:text-red-400">{{ __('messages.nav.home') }}</a>
                    <a href="#user-guide" class="text-sm hover:text-red-600 dark:hover:text-red-400">{{ __('messages.docs.user_guide') }}</a>
                    <a href="#developer-guide" class="text-sm hover:text-red-600 dark:hover:text-red-400">{{ __('messages.docs.developer_guide') }}</a>
                    <a href="#faq" class="text-sm hover:text-red-600 dark:hover:text-red-400">{{ __('messages.docs.faq') }}</a>
                </nav>
                <div class="flex items-center gap-3">
                    <!-- Language Toggle -->
                    <x-language-toggle />
                    
                    <a href="{{ url('/admin/login') }}" class="inline-flex items-center rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800">{{ __('messages.nav.log_in') }}</a>
                    <a href="{{ url('/admin/register') }}" class="inline-flex items-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-red-500">{{ __('messages.nav.sign_up') }}</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="relative">
        <section class="relative">
            <div class="absolute inset-0 bg-gradient-to-b from-red-50 via-orange-50 to-red-50 dark:from-red-900/20 dark:via-orange-900/10 dark:to-red-900/10"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
                <!-- Title -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold tracking-tight">{{ __('messages.docs.title') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">KMG Zumbrock Dashboard · Laravel 11 · Filament v3 · Tailwind 3.4 · Vite</p>
                </div>

                <div class="grid gap-10 lg:grid-cols-[280px_minmax(0,1fr)]">
                    <!-- Sidebar / TOC -->
                    <aside class="lg:sticky lg:top-24 self-start">
                        <nav class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-4 text-sm">
                            <p class="font-semibold mb-3">{{ __('messages.docs.on_this_page') }}</p>
                            <ol class="space-y-2">
                                <li><a class="hover:text-red-600" href="#overview">{{ __('messages.docs.overview') }}</a></li>
                                <li><a class="hover:text-red-600" href="#user-guide">{{ __('messages.docs.user_guide') }}</a></li>
                                <li><a class="hover:text-red-600" href="#widgets">{{ __('messages.docs.widgets') }}</a></li>
                                <li><a class="hover:text-red-600" href="#data-model">{{ __('messages.docs.data_model') }}</a></li>
                                <li><a class="hover:text-red-600" href="#resources">{{ __('messages.docs.resources') }}</a></li>
                                <li><a class="hover:text-red-600" href="#developer-guide">{{ __('messages.docs.developer_guide') }}</a></li>
                                <li><a class="hover:text-red-600" href="#conventions">{{ __('messages.docs.conventions') }}</a></li>
                                <li><a class="hover:text-red-600" href="#troubleshooting">{{ __('messages.docs.troubleshooting') }}</a></li>
                                <li><a class="hover:text-red-600" href="#faq">{{ __('messages.docs.faq') }}</a></li>
                                <li><a class="hover:text-red-600" href="#changelog">{{ __('messages.docs.changelog') }}</a></li>
                            </ol>
                        </nav>
                        
                    </aside>

                    <!-- Content -->
                    <article class="space-y-12">
                        <!-- Overview -->
                        <section id="overview" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">Overview</h2>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">
                                KMG automates supplier <em>order confirmations → order matching</em> and provides a dashboard for agents to monitor auto-matched vs manual, resolve exceptions, and manage processing tasks.
                            </p>
                            
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="{{ url('/admin') }}" class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-500">Open Dashboard</a>
                                <a href="{{ route('welcome') }}#features" class="inline-flex items-center rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800">See Features</a>
                            </div>
                        </section>

                        <!-- User Guide -->
                        <section id="user-guide" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">User Guide (Agents)</h2>
                            <ol class="mt-4 space-y-3 text-gray-700 dark:text-gray-300 text-sm">
                                <li><strong>Log in:</strong> Go to <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/login') }}">/admin/login</a>. Your queue appears on the dashboard (MyQueue).</li>
                                <li><strong>Check widgets:</strong> StatsOverview for today’s throughput, ThroughputChart for trends, AgingByBucket for stale items, RecentExceptions for what needs attention.</li>
                                <li><strong>Handle Exceptions:</strong> Open <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/exceptions') }}">Exceptions</a>. Review mismatch details, take corrective action (edit data/attach docs), resolve or escalate.</li>
                                <li><strong>Work Processing Tasks:</strong> Open <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/processing-tasks') }}">ProcessingTasks</a>. Complete tasks in priority order, add notes if needed.</li>
                                <li><strong>Review OrderMatches:</strong> Open <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/order-matches') }}">OrderMatches</a> to verify auto-matched results and adjust manually if necessary.</li>
                                <li><strong>View Orders & Confirmations:</strong> <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/orders') }}">Orders</a> and <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/order-confirmations') }}">Order Confirmations</a> list raw inputs and parsed supplier responses.</li>
                                <li><strong>Attach Documents:</strong> Upload supporting files in <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/documents') }}">Documents</a> and link them to a task/exception/match.</li>
                                <li><strong>Read/Update Docs:</strong> In-app docs live under <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/documentation') }}">Documentation</a>. Use search, edit, and version notes (saved with author & updated_by).</li>
                            </ol>
                            <div class="mt-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-3 text-xs text-emerald-900 dark:text-emerald-200">
                                Tip: Clear your queue daily. Use AgingByBucket to identify items older than target SLAs.
                            </div>
                        </section>

                        <!-- Widgets -->
                        <section id="widgets" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">Dashboard Widgets</h2>
                            <ul class="mt-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li><strong>StatsOverview:</strong> At-a-glance counts (auto-matched, manual, exceptions, tasks today).</li>
                                <li><strong>ThroughputChart:</strong> Time-series of matches & tasks processed.</li>
                                <li><strong>AgingByBucketChart:</strong> Items bucketed by age (0-24h, 1-3d, 4-7d, 8-14d, 15d+).</li>
                                <li><strong>RecentExceptions:</strong> Latest exceptions requiring action.</li>
                                <li><strong>MyQueue:</strong> Your assigned tasks & exceptions.</li>
                            </ul>
                        </section>

                        <!-- Data Model -->
                        <section id="data-model" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">Data Model</h2>
                            <div class="mt-3 grid sm:grid-cols-2 gap-4 text-sm">
                                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                    <h3 class="font-semibold">matches (OrderMatches)</h3>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">PO ↔ supplier confirmation alignment; status, confidence, linked docs.</p>
                                </div>
                                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                    <h3 class="font-semibold">processing_tasks</h3>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">Queue of actionable items with priority, assignee, SLA timestamps.</p>
                                </div>
                                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                    <h3 class="font-semibold">exceptions</h3>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">Mismatches (qty/price/date/sku) to resolve or escalate.</p>
                                </div>
                                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                    <h3 class="font-semibold">documentation</h3>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">In-app docs; <span class="font-mono">author_id</span>, <span class="font-mono">updated_by</span> reference users.</p>
                                </div>
                                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                                    <h3 class="font-semibold">documents</h3>
                                    <p class="mt-1 text-gray-600 dark:text-gray-400">File store for attachments linked to tasks/exceptions/matches.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Filament Resources -->
                        <section id="resources" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">Filament Resources</h2>
                            <ul class="mt-4 grid sm:grid-cols-2 gap-3 text-sm">
                                <li><a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/orders') }}">Orders</a></li>
                                <li><a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/order-confirmations') }}">OrderConfirmations</a></li>
                                <li><a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/order-matches') }}">OrderMatches</a></li>
                                <li><a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/processing-tasks') }}">ProcessingTasks</a></li>
                                <li><a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/exceptions') }}">Exceptions</a></li>
                                <li><a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/documents') }}">Documents</a></li>
                                <li><a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/users') }}">Users</a></li>
                                <li><a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/documentation') }}">Documentation</a></li>
                            </ul>
                        </section>

                        <!-- Developer Guide -->
                        <section id="developer-guide" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">Developer Guide</h2>
                            <div class="mt-3 grid lg:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <h3 class="font-semibold">Local Setup</h3>
                                    <ol class="mt-2 space-y-2 text-gray-700 dark:text-gray-300">
                                        <li>Clone & install PHP deps: <span class="font-mono">composer install</span></li>
                                        <li>Install JS deps: <span class="font-mono">npm install</span></li>
                                        <li>Env & key: <span class="font-mono">cp .env.example .env && php artisan key:generate</span></li>
                                        <li>Migrate & seed: <span class="font-mono">php artisan migrate --seed</span> (seeds demo data for widgets)</li>
                                        <li>Run dev servers:
                                            <div class="mt-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-2 font-mono text-xs">php artisan serve<br>npm run dev</div>
                                        </li>
                                        <li>Assets are loaded via:
                                            <div class="mt-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-2 font-mono text-xs">@vite(['resources/css/app.css','resources/js/app.js'])</div>
                                        </li>
                                    </ol>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Filament</h3>
                                    <ul class="mt-2 space-y-2 text-gray-700 dark:text-gray-300">
                                        <li>Admin panel path default: <span class="font-mono">/admin</span></li>
                                        <li>Enable registration (optional): add <span class="font-mono">->registration()</span> to your Panel provider.</li>
                                        <li>Resources exist for all core entities listed above.</li>
                                        <li>Documentation is editable in-app and persisted to DB.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-4">
                                <h3 class="font-semibold">Build & Deploy</h3>
                                <ul class="mt-2 space-y-2 text-gray-700 dark:text-gray-300 text-sm">
                                    <li>Production assets: <span class="font-mono">npm run build</span></li>
                                    <li>Config cache: <span class="font-mono">php artisan config:cache && php artisan route:cache && php artisan view:cache</span></li>
                                </ul>
                            </div>
                        </section>

                        <!-- Conventions -->
                        <section id="conventions" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">Conventions</h2>
                            <ul class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li><strong>“OrderMatch” name:</strong> Use <em>OrderMatch</em> everywhere. “Model Match” is forbidden.</li>
                                <li><strong>PHP Exception alias:</strong> Always alias <span class="font-mono">Exception</span> → <span class="font-mono">ExceptionModel</span> to avoid conflicts.</li>
                                <li><strong>Auth IDs:</strong> Use <span class="font-mono">Auth::id()</span> (not <span class="font-mono">auth()->id()</span>) when filling <span class="font-mono">author_id</span> or <span class="font-mono">updated_by</span>.</li>
                                <li><strong>Widgets drive metrics:</strong> Exceptions, ProcessingTasks, and Matches are the foundations of all KPI widgets.</li>
                            </ul>
                        </section>

                        <!-- Troubleshooting -->
                        <section id="troubleshooting" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">Troubleshooting</h2>
                            <ul class="mt-3 space-y-3 text-sm text-gray-700 dark:text-gray-300">
                                <li><strong>Tailwind classes not recognized:</strong> Ensure Tailwind v3.4 is installed, Vite is running with <span class="font-mono">npm run dev</span>, and your Blade includes <span class="font-mono">@vite([])</span>.</li>
                                <li><strong>Welcome/docs not loading styles:</strong> Check that <span class="font-mono">resources/css/app.css</span> imports Tailwind and that Vite compiled successfully.</li>
                                <li><strong>Filament 404:</strong> Confirm panel provider is registered and panel path is <span class="font-mono">/admin</span> (or update links).</li>
                            </ul>
                        </section>

                        <!-- FAQ -->
                        <section id="faq" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">FAQ</h2>
                            <dl class="mt-4 space-y-4 text-sm">
                                <div>
                                    <dt class="font-medium">Can I edit docs without developer access?</dt>
                                    <dd class="mt-1 text-gray-700 dark:text-gray-300">Yes, via Filament → Documentation. Changes are tracked with author & updated_by.</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">Where do I see my work items?</dt>
                                    <dd class="mt-1 text-gray-700 dark:text-gray-300">Dashboard → MyQueue, plus <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/processing-tasks') }}">ProcessingTasks</a> & <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/exceptions') }}">Exceptions</a>.</dd>
                                </div>
                                <div>
                                    <dt class="font-medium">How do I verify an OrderMatch?</dt>
                                    <dd class="mt-1 text-gray-700 dark:text-gray-300">Open <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/order-matches') }}">OrderMatches</a> and review PO vs confirmation details; adjust or mark verified.</dd>
                                </div>
                            </dl>
                        </section>

                        <!-- Changelog placeholder -->
                        <section id="changelog" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-6">
                            <h2 class="text-2xl font-semibold">Changelog</h2>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Maintain release notes here or link to an entry in <a class="text-red-600 dark:text-red-400 underline" href="{{ url('/admin/documentation') }}">Documentation</a>.</p>
                            <ul class="mt-3 text-sm list-disc pl-5 text-gray-600 dark:text-gray-300">
                                <li>[YYYY-MM-DD] Initial public documentation page.</li>
                            </ul>
                        </section>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-sm text-gray-500 dark:text-gray-400">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>{{ __('messages.footer.copyright', ['year' => now()->year]) }}</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('welcome') }}" class="hover:text-red-600 dark:hover:text-red-400">{{ __('messages.nav.home') }}</a>
                    <a href="{{ url('/admin/login') }}" class="hover:text-red-600 dark:hover:text-red-400">{{ __('messages.nav.agent_login') }}</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
