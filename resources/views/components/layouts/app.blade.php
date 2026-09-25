<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Accurate Islamic night divisions, Islamic midnight, and Last Third of the Night (Tahajjud) calculator with live countdown and city auto-detection.">
    <meta name="theme-color" content="#090d16">

    <title>{{ $title ?? 'Last 3rd | Islamic Night & Tahajjud Calculator' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .font-arabic {
            font-family: 'Amiri', serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #090d16;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
</head>
<body class="bg-[#090d16] text-slate-100 min-h-screen antialiased selection:bg-amber-500/30 selection:text-amber-200">
    <div class="relative min-h-screen overflow-hidden flex flex-col justify-between">
        <!-- Ambient background glows -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-b from-indigo-900/20 via-sky-950/10 to-transparent blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-40 right-10 w-72 h-72 bg-amber-500/5 blur-3xl rounded-full pointer-events-none -z-10"></div>
        <div class="absolute bottom-20 left-10 w-96 h-96 bg-indigo-600/5 blur-3xl rounded-full pointer-events-none -z-10"></div>

        <!-- Main Content -->
        <main class="flex-grow py-6 sm:py-10">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-800/80 bg-slate-950/60 py-6 text-center text-xs text-slate-500">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center space-x-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
                    <span class="font-medium text-slate-400">Last 3rd</span>
                    <span>&bull;</span>
                    <span>Calculated with astronomical precision</span>
                </div>
                <div>
                    <span class="italic">"Our Lord descends every night to the lowest heaven when one-third of the night remains..."</span>
                    <span class="text-slate-400 ml-1">(Sahih al-Bukhari)</span>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
