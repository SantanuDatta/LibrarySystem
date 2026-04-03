<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'BookHive') }}</title>

        <link href="https://fonts.googleapis.com" rel="preconnect">
        <link
            href="https://fonts.gstatic.com"
            rel="preconnect"
            crossorigin
        >
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        >
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-screen bg-background font-sans text-foreground antialiased md:subpixel-antialiased">
        <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,rgba(249,115,22,0.12),transparent_24%),linear-gradient(180deg,#09090b_0%,#0f0f11_100%)]">
            <div class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-size[64px_64px] opacity-40"></div>
            <div class="absolute left-1/2 top-0 -z-10 h-80 w-80 -translate-x-1/2 rounded-full bg-orange-600/10 blur-3xl"></div>

            <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-4 sm:px-6 lg:px-8">
                <header class="rounded-2xl border border-white/10 bg-zinc-950/80 p-3 shadow-2xl shadow-black/30 backdrop-blur-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="min-w-0">
                                @if ($settings?->site_logo)
                                    <img
                                        src="{{ Storage::disk('public')->url($settings->site_logo) }}"
                                        alt="{{ $settings->site_name }}"
                                        class="h-8 w-auto"
                                    >
                                @else
                                    <p class="truncate text-lg font-semibold uppercase tracking-[0.28em] text-zinc-100 sm:text-sm sm:tracking-[0.22em]">
                                        {{ config('app.name', 'BookHive') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="hidden items-center gap-2 md:flex">
                            <a
                                class="inline-flex items-center justify-center rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-zinc-100 transition hover:border-zinc-500 hover:bg-zinc-800"
                                href="{{ url('/staff/login') }}"
                            >
                                Staff Login
                            </a>
                            <a
                                class="inline-flex items-center justify-center rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-orange-400"
                                href="{{ url('/admin/login') }}"
                            >
                                Admin Login
                            </a>
                        </div>

                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-zinc-900 p-3 text-zinc-100 transition hover:bg-zinc-800 md:hidden"
                            type="button"
                            aria-controls="mobile-navigation"
                            aria-expanded="false"
                            data-menu-toggle
                        >
                            <span class="sr-only">Open navigation menu</span>
                            <svg
                                class="size-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                data-menu-icon="open"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                                />
                            </svg>
                            <svg
                                class="size-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                data-menu-icon="close"
                                hidden
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                    </div>
                </header>

                <div
                    class="mt-3 md:hidden"
                    id="mobile-navigation"
                    hidden
                >
                    <div class="space-y-2 rounded-xl border border-white/10 bg-zinc-950/95 p-3 shadow-2xl shadow-black/40 backdrop-blur-xl">
                        <a
                            class="inline-flex w-full items-center justify-center rounded-lg border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm font-semibold text-zinc-100 transition hover:border-zinc-500 hover:bg-zinc-800"
                            href="{{ url('/staff/login') }}"
                        >
                            Staff Login
                        </a>
                        <a
                            class="inline-flex w-full items-center justify-center rounded-lg bg-orange-500 px-4 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-orange-400"
                            href="{{ url('/admin/login') }}"
                        >
                            Admin Login
                        </a>
                    </div>
                </div>

                <main class="flex-1 py-6 lg:py-8">
                    <section class="grid items-stretch gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                        <div class="rounded-2xl border border-white/10 bg-zinc-950/80 p-6 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-8">
                            <div class="mt-6 max-w-2xl">
                                <h1 class="text-4xl font-bold tracking-tight text-zinc-50 sm:text-5xl">
                                    Manage your library from catalog to circulation.
                                </h1>
                                <p class="mt-4 text-base leading-7 text-zinc-400 sm:text-lg">
                                    BookHive helps administrators and staff manage books, authors, genres, publishers,
                                    and borrowing activity from one organized workspace.
                                </p>
                            </div>

                            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                <a
                                    class="inline-flex items-center justify-center rounded-lg bg-orange-500 px-5 py-3.5 text-sm font-semibold text-primary-foreground transition hover:bg-orange-400"
                                    href="{{ url('/admin/login') }}"
                                >
                                    Go to Admin Panel
                                </a>
                                <a
                                    class="inline-flex items-center justify-center rounded-lg border border-zinc-700 bg-zinc-900 px-5 py-3.5 text-sm font-semibold text-zinc-100 transition hover:border-zinc-500 hover:bg-zinc-800"
                                    href="{{ url('/staff/login') }}"
                                >
                                    Go to Staff Panel
                                </a>
                            </div>

                            <div class="mt-8 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-white/10 bg-zinc-900 p-4">
                                    <p class="text-xs font-medium uppercase tracking-[0.24em] text-zinc-500">Catalog</p>
                                    <p class="mt-3 text-2xl font-semibold text-zinc-50">Book Records</p>
                                    <p class="mt-2 text-sm text-zinc-400">Keep books, authors, genres, and publishers organized in one place.</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-zinc-900 p-4">
                                    <p class="text-xs font-medium uppercase tracking-[0.24em] text-zinc-500">Lending</p>
                                    <p class="mt-3 text-2xl font-semibold text-zinc-50">Borrowing</p>
                                    <p class="mt-2 text-sm text-zinc-400">Track issued books, returns, and front-desk activity with clarity.</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid h-full gap-6">
                            <div class="h-full rounded-2xl border border-white/10 bg-zinc-950/80 p-4 shadow-2xl shadow-black/30 backdrop-blur-xl sm:p-5">
                                <div class="flex h-full flex-col rounded-xl border border-white/10 bg-zinc-900 p-4 sm:p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.24em] text-zinc-500">Library overview</p>
                                            <h2 class="mt-2 text-xl font-semibold text-zinc-50">Daily operations</h2>
                                        </div>
                                        <div class="rounded-lg border border-orange-500/20 bg-orange-500/10 px-3 py-2 text-xs font-semibold text-orange-200">
                                            Online
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-xl border border-white/10 bg-zinc-950 p-4">
                                            <p class="text-sm text-zinc-500">Books on loan</p>
                                            <p class="mt-2 text-3xl font-bold text-zinc-50">248</p>
                                            <p class="mt-2 text-sm text-zinc-400">Current borrowed items being tracked by the system.</p>
                                        </div>
                                        <div class="rounded-xl border border-white/10 bg-zinc-950 p-4">
                                            <p class="text-sm text-zinc-500">Catalog updates</p>
                                            <p class="mt-2 text-3xl font-bold text-zinc-50">19</p>
                                            <p class="mt-2 text-sm text-zinc-400">Recent changes to books, categories, and publisher records.</p>
                                        </div>
                                    </div>

                                    <div class="mt-5 flex-1 space-y-3">
                                        <div class="flex items-center justify-between rounded-xl border border-white/10 bg-zinc-950 px-4 py-3">
                                            <div>
                                                <p class="text-sm font-semibold text-zinc-100">Circulation desk</p>
                                                <p class="text-sm text-zinc-500">Handle borrowing, returns, and due dates</p>
                                            </div>
                                            <span class="rounded-md bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-300">Ready</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl border border-white/10 bg-zinc-950 px-4 py-3">
                                            <div>
                                                <p class="text-sm font-semibold text-zinc-100">Catalog management</p>
                                                <p class="text-sm text-zinc-500">Maintain books, genres, authors, and publishers</p>
                                            </div>
                                            <span class="rounded-md bg-orange-500/10 px-3 py-1 text-xs font-medium text-orange-200">Focused</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </section>
                </main>
            </div>
        </div>
    </body>

</html>
