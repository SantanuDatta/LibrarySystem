<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Library System') }}</title>

        <link href="https://fonts.googleapis.com" rel="preconnect">
        <link
            href="https://fonts.gstatic.com"
            rel="preconnect"
            crossorigin
        >
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        >
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style type="text/tailwindcss">
            @theme {
                --font-sans: "Manrope", ui-sans-serif, system-ui, sans-serif;
                --color-ink: #e5f3f2;
                --color-surface: #061311;
                --color-brand: #14b8a6;
                --color-brand-soft: #134e4a;
                --color-accent: #fb923c;
            }

            [x-cloak] {
                display: none !important;
            }
        </style>
    </head>

    <body class="bg-surface font-sans text-slate-200 antialiased md:subpixel-antialiased">
        <div
            class="relative overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(20,184,166,0.22),_transparent_26%),radial-gradient(circle_at_top_right,_rgba(251,146,60,0.18),_transparent_24%),linear-gradient(180deg,_#061311_0%,_#081c1a_38%,_#0b1120_100%)]">
            <div
                class="absolute inset-x-0 top-0 -z-10 h-72 bg-[linear-gradient(135deg,_rgba(20,184,166,0.12),_transparent_60%)]">
            </div>

            <header class="fixed inset-x-0 top-0 z-50 px-6 pt-4 sm:px-10 lg:px-12">
                <div class="mx-auto max-w-7xl" x-data="{ open: false }">
                    <nav class="rounded-lg border border-white/10 bg-slate-950/65 px-4 py-4 shadow-lg shadow-black/30 ring-1 ring-white/5 backdrop-blur-xl md:px-5">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                @if ($settings?->site_logo)
                                    <img
                                        src="{{ Storage::disk('public')->url($settings->site_logo) }}"
                                        alt="{{ $settings->site_name }}"
                                        class="h-8 w-auto"
                                    >
                                @else
                                    <p class="text-lg font-semibold uppercase tracking-[0.35em] text-teal-300">
                                        Library System
                                    </p>
                                @endif
                            </div>

                            <button
                                class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/5 p-3 text-slate-100 transition hover:bg-white/10 md:hidden"
                                type="button"
                                aria-controls="mobile-navigation"
                                :aria-expanded="open.toString()"
                                x-on:click="open = !open"
                            >
                                <span class="sr-only">Open navigation menu</span>
                                <svg
                                    class="size-6 transition duration-300"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    x-show="!open"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                                    />
                                </svg>
                                <svg
                                    class="size-6 transition duration-300"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    x-cloak
                                    x-show="open"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 18 18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>

                            <div class="hidden md:flex md:flex-none md:items-center md:gap-3">
                                <a
                                    class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:border-teal-400/40 hover:bg-teal-400/10 hover:text-teal-200"
                                    href="{{ url('/staff/login') }}"
                                >
                                    Staff Login
                                </a>
                                <a
                                    class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-teal-400"
                                    href="{{ url('/admin/login') }}"
                                >
                                    Admin Login
                                </a>
                            </div>
                        </div>

                        <div
                            class="md:hidden"
                            id="mobile-navigation"
                            x-cloak
                            x-show="open"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                        >
                            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                                <a
                                    class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:border-teal-400/40 hover:bg-teal-400/10 hover:text-teal-200"
                                    href="{{ url('/staff/login') }}"
                                >
                                    Staff Login
                                </a>
                                <a
                                    class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-teal-400"
                                    href="{{ url('/admin/login') }}"
                                >
                                    Admin Login
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>
            </header>

            <main class="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 pt-28 sm:px-10 sm:pt-32 lg:px-12 lg:pt-32">

                <section class="grid flex-1 items-center gap-12 py-14 lg:grid-cols-[1.1fr_0.9fr] lg:py-20">
                    <div class="max-w-3xl">
                        <div
                            class="inline-flex items-center gap-2 rounded-lg border border-teal-400/20 bg-teal-400/10 px-4 py-2 text-sm font-medium text-teal-200 shadow-sm shadow-teal-950/20">
                            <span class="size-2 rounded-full bg-orange-500"></span>
                            Built for daily library operations
                        </div>

                        <h1 class="text-ink mt-8 text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                            Library operations, built for modern campuses.
                        </h1>

                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300 sm:text-xl">
                            Manage books, authors, genres, publishers, and transactions from focused dashboards made for
                            administrators and staff. Keep circulation moving with a cleaner front door to your system.
                        </p>

                        <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                            <a class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-6 py-4 text-base font-semibold text-slate-950 shadow-lg shadow-teal-700/20 transition hover:-translate-y-0.5 hover:bg-teal-400"
                                href="{{ url('/admin/login') }}"
                            >
                                Open Admin Panel
                            </a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-6 py-4 text-base font-semibold text-slate-100 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:border-orange-300/30 hover:bg-orange-400/10 hover:text-orange-200"
                                href="{{ url('/staff/login') }}"
                            >
                                Continue as Staff
                            </a>
                        </div>

                        <dl class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div
                                class="rounded-lg border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/10 backdrop-blur">
                                <dt class="text-sm font-medium text-slate-400">Collections</dt>
                                <dd class="text-ink mt-2 text-xl font-bold">Books</dd>
                            </div>
                            <div
                                class="rounded-lg border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/10 backdrop-blur">
                                <dt class="text-sm font-medium text-slate-400">Workflows</dt>
                                <dd class="text-ink mt-2 text-xl font-bold">Borrow & Return</dd>
                            </div>
                            <div
                                class="rounded-lg border border-white/10 bg-white/5 p-4 shadow-xl shadow-black/10 backdrop-blur">
                                <dt class="text-sm font-medium text-slate-400">Access</dt>
                                <dd class="text-ink mt-2 text-xl font-bold">Admin + Staff</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="relative">
                        <div
                            class="absolute -left-6 top-12 hidden h-24 w-24 rounded-full bg-orange-300/50 blur-3xl sm:block">
                        </div>
                        <div
                            class="absolute -right-6 bottom-8 hidden h-28 w-28 rounded-full bg-teal-300/50 blur-3xl sm:block">
                        </div>

                        <div
                            class="relative rounded-lg border border-white/10 bg-slate-950/80 p-5 shadow-2xl shadow-black/30 sm:p-6">
                            <div
                                class="rounded-lg bg-[linear-gradient(180deg,_#0f172a_0%,_#111827_100%)] p-5 text-white sm:p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm uppercase tracking-[0.35em] text-teal-300">Today</p>
                                        <h2 class="mt-2 text-2xl font-bold">Reading room dashboard</h2>
                                    </div>
                                    <div class="rounded-lg bg-white/10 px-4 py-3 text-right">
                                        <p class="text-xs text-slate-300">Status</p>
                                        <p class="text-sm font-semibold text-emerald-300">System Active</p>
                                    </div>
                                </div>

                                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                    <div class="bg-white/8 rounded-lg p-5 ring-1 ring-white/10">
                                        <p class="text-sm text-slate-300">Active loans</p>
                                        <p class="mt-3 text-4xl font-extrabold">248</p>
                                        <p class="mt-2 text-sm text-emerald-300">Steady circulation across departments
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-lg bg-gradient-to-br from-teal-400 to-cyan-300 p-5 text-slate-950">
                                        <p class="text-sm font-semibold uppercase tracking-[0.28em]">Focus</p>
                                        <p class="mt-3 text-2xl font-extrabold">Low-friction catalog updates</p>
                                        <p class="mt-2 text-sm font-medium text-slate-800/80">Quick access to authors,
                                            publishers, genres, and availability.</p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <div
                                        class="bg-white/6 flex items-center justify-between rounded-lg px-4 py-3 ring-1 ring-white/10">
                                        <div>
                                            <p class="font-semibold">Circulation desk</p>
                                            <p class="text-sm text-slate-300">Checkouts, returns, and due dates</p>
                                        </div>
                                        <span
                                            class="rounded-lg bg-emerald-400/15 px-3 py-1 text-sm font-semibold text-emerald-300"
                                        >Live</span>
                                    </div>

                                    <div
                                        class="bg-white/6 flex items-center justify-between rounded-lg px-4 py-3 ring-1 ring-white/10">
                                        <div>
                                            <p class="font-semibold">Catalog control</p>
                                            <p class="text-sm text-slate-300">Book, genre, author, and publisher records
                                            </p>
                                        </div>
                                        <span
                                            class="rounded-lg bg-orange-400/15 px-3 py-1 text-sm font-semibold text-orange-200"
                                        >Updated</span>
                                    </div>

                                    <div
                                        class="bg-white/6 flex items-center justify-between rounded-lg px-4 py-3 ring-1 ring-white/10">
                                        <div>
                                            <p class="font-semibold">Team access</p>
                                            <p class="text-sm text-slate-300">Separate areas for admin and staff
                                                workflows</p>
                                        </div>
                                        <span
                                            class="rounded-lg bg-sky-400/15 px-3 py-1 text-sm font-semibold text-sky-200"
                                        >Ready</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pb-10">
                    <div class="grid gap-5 md:grid-cols-3">
                        <article class="rounded-lg border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/20">
                            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-teal-300">Catalog</p>
                            <h3 class="text-ink mt-4 text-2xl font-bold">Organize your collection</h3>
                            <p class="mt-3 leading-7 text-slate-300">
                                Keep book records structured with linked authors, genres, and publishers so search and
                                maintenance stay simple.
                            </p>
                        </article>

                        <article class="rounded-lg border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/20">
                            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-orange-300">Transactions
                            </p>
                            <h3 class="text-ink mt-4 text-2xl font-bold">Track lending activity</h3>
                            <p class="mt-3 leading-7 text-slate-300">
                                Support day-to-day borrowing with clear status handling for issued items, returns, and
                                borrower visibility.
                            </p>
                        </article>

                        <article class="rounded-lg border border-white/10 bg-white/5 p-6 shadow-lg shadow-black/20">
                            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-200">Teamwork</p>
                            <h3 class="text-ink mt-4 text-2xl font-bold">Give each role a focused panel</h3>
                            <p class="mt-3 leading-7 text-slate-300">
                                Let administrators and staff sign in through dedicated flows tailored to the work they
                                handle every day.
                            </p>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </body>

</html>
