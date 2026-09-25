<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" x-data="{
    detectLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                @this.call('setGeolocation', pos.coords.latitude, pos.coords.longitude, tz);
            },
            (err) => {
                alert('Unable to retrieve your location: ' + err.message);
            }
        );
    }
}">
    <!-- Header & Controls -->
    <header class="flex flex-col md:flex-row items-center justify-between gap-4 pb-6 border-b border-slate-800/80">
        <!-- Logo / Title -->
        <div class="flex items-center space-x-3 text-center md:text-left">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-950/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-100 to-amber-200">
                        Last 3rd
                    </h1>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-300 font-medium border border-amber-500/20">
                        Tahajjud Calculator
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-arabic text-right tracking-wide">الثلث الأخير من الليل</p>
            </div>
        </div>

        <!-- Controls: City Selector, Geolocation & Toggles -->
        <div class="flex flex-wrap items-center justify-center md:justify-end gap-2.5 w-full md:w-auto">
            <!-- City Dropdown -->
            <div class="relative">
                <select 
                    wire:change="selectCity($event.target.value)" 
                    class="bg-slate-900 border border-slate-700/80 hover:border-slate-600 text-slate-200 text-xs rounded-xl px-3 py-2 pr-8 focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500 transition cursor-pointer appearance-none shadow-sm">
                    @foreach ($presetCities as $index => $city)
                        <option value="{{ $index }}" {{ $location['name'] === $city['name'] ? 'selected' : '' }}>
                            📍 {{ $city['name'] }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- Geolocation Button -->
            <button 
                type="button" 
                @click="detectLocation()" 
                title="Detect My Location"
                class="flex items-center space-x-1.5 bg-slate-900 hover:bg-slate-800 border border-slate-700/80 hover:border-slate-600 text-slate-200 text-xs rounded-xl px-3 py-2 transition shadow-sm">
                <svg class="h-3.5 w-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="hidden sm:inline">Auto-Locate</span>
            </button>

            <!-- 12h / 24h Toggle -->
            <button 
                type="button" 
                wire:click="toggleTimeFormat" 
                class="bg-slate-900 hover:bg-slate-800 border border-slate-700/80 text-slate-300 text-xs font-semibold rounded-xl px-3 py-2 transition shadow-sm">
                {{ $timeFormat === '12h' ? '12-Hour' : '24-Hour' }}
            </button>

            <!-- Method Switcher -->
            <div class="inline-flex rounded-xl p-1 bg-slate-900/90 border border-slate-800 text-xs">
                <button 
                    type="button" 
                    wire:click="setMethod('sunset_to_fajr')" 
                    class="px-2.5 py-1 rounded-lg transition {{ $method === 'sunset_to_fajr' ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-400 hover:text-slate-200' }}"
                    title="Majority Fiqh opinion: Sunset to Fajr (Dawn)">
                    Maghrib &rarr; Fajr
                </button>
                <button 
                    type="button" 
                    wire:click="setMethod('sunset_to_sunrise')" 
                    class="px-2.5 py-1 rounded-lg transition {{ $method === 'sunset_to_sunrise' ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-400 hover:text-slate-200' }}"
                    title="Sunset to Sunrise">
                    Maghrib &rarr; Sunrise
                </button>
            </div>
        </div>
    </header>

    <!-- Date Navigation Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-900/40 backdrop-blur-md border border-slate-800/80 p-3 sm:p-4 rounded-2xl shadow-sm">
        <div class="flex items-center space-x-2">
            <button 
                wire:click="changeDate(-1)" 
                class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 transition" 
                title="Previous Day">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="text-center sm:text-left">
                <span class="text-sm font-bold text-slate-100 block">
                    {{ Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                </span>
                <span class="text-xs text-slate-400">
                    {{ $location['name'] }} &bull; Timezone: {{ $todayForecast['timezone'] }}
                </span>
            </div>
            <button 
                wire:click="changeDate(1)" 
                class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 transition" 
                title="Next Day">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <div class="flex items-center space-x-2">
            @if ($selectedDate !== Carbon\Carbon::today()->format('Y-m-d'))
                <button 
                    wire:click="goToToday" 
                    class="text-xs px-3 py-1.5 rounded-xl bg-indigo-600/30 hover:bg-indigo-600/50 text-indigo-300 border border-indigo-500/30 transition">
                    Jump to Tonight
                </button>
            @endif

            <!-- Tabs: Today vs Week -->
            <div class="inline-flex rounded-xl p-1 bg-slate-950/80 border border-slate-800 text-xs">
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'today')" 
                    class="px-3 py-1 rounded-lg transition {{ $activeTab === 'today' ? 'bg-slate-800 text-amber-300 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">
                    Tonight View
                </button>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'week')" 
                    class="px-3 py-1 rounded-lg transition {{ $activeTab === 'week' ? 'bg-slate-800 text-amber-300 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">
                    7-Day Forecast
                </button>
            </div>
        </div>
    </div>

    @if ($activeTab === 'today')
        <!-- HERO SPOTLIGHT: Tonight's Last Third & Realtime Status -->
        <section class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-b from-slate-900/90 via-slate-900/50 to-slate-950 p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
            <!-- Accent ring glow -->
            <div class="absolute -top-24 -right-24 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Current Live Status Banner -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
                <div>
                    <span class="text-xs uppercase tracking-wider font-semibold text-amber-400">Night Status</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2 mt-0.5">
                        @if ($todayForecast['status']['is_last_third'])
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <span class="text-emerald-300">Blessed Last Third is Active!</span>
                        @elseif ($todayForecast['status']['is_night'])
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-400"></span>
                            <span>{{ $todayForecast['status']['phase_label'] }}</span>
                        @else
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span>{{ $todayForecast['status']['phase_label'] }}</span>
                        @endif
                    </h2>
                </div>

                <!-- Highlight Badge -->
                <div class="px-4 py-2 rounded-2xl {{ $todayForecast['status']['is_last_third'] ? 'bg-emerald-950/60 border border-emerald-500/40 text-emerald-300' : 'bg-slate-800/80 border border-slate-700/80 text-amber-300' }} text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @if ($todayForecast['status']['is_last_third'])
                        <span>Ends in {{ $this->formatDuration($todayForecast['status']['seconds_remaining_in_last_third'] ?? 0) }} at Fajr</span>
                    @elseif (isset($todayForecast['status']['seconds_until_last_third']) && $todayForecast['status']['seconds_until_last_third'] > 0)
                        <span>Last 3rd starts in {{ $this->formatDuration($todayForecast['status']['seconds_until_last_third']) }}</span>
                    @else
                        <span>Total Night: {{ $todayForecast['durations']['total_human'] }}</span>
                    @endif
                </div>
            </div>

            <!-- Visual Night Progress Bar -->
            <div class="mb-10 space-y-2">
                <div class="flex justify-between text-xs text-slate-400 font-medium px-1">
                    <span>Maghrib ({{ $this->formatTime($todayForecast['timestamps']['sunset']) }})</span>
                    <span>Midnight ({{ $this->formatTime($todayForecast['midnight']) }})</span>
                    <span class="text-amber-300 font-semibold">Last 3rd ({{ $this->formatTime($todayForecast['divisions']['3/3']['start']) }})</span>
                    <span>{{ $method === 'sunset_to_sunrise' ? 'Sunrise' : 'Fajr' }} ({{ $this->formatTime($todayForecast['timestamps']['night_end']) }})</span>
                </div>

                <div class="h-3 w-full bg-slate-950 rounded-full overflow-hidden flex border border-slate-800 p-0.5">
                    <!-- 1/3 Segment -->
                    <div class="h-full rounded-l-full bg-slate-800 hover:bg-slate-700 transition" style="width: 33.33%" title="First Third: {{ $this->formatTime($todayForecast['divisions']['1/3']['start']) }} - {{ $this->formatTime($todayForecast['divisions']['1/3']['end']) }}"></div>
                    <!-- 2/3 Segment -->
                    <div class="h-full bg-slate-700 hover:bg-slate-600 transition" style="width: 33.33%" title="Second Third: {{ $this->formatTime($todayForecast['divisions']['2/3']['start']) }} - {{ $this->formatTime($todayForecast['divisions']['2/3']['end']) }}"></div>
                    <!-- 3/3 Segment (Last Third) -->
                    <div class="h-full rounded-r-full bg-gradient-to-r from-amber-500 to-amber-400 shadow-sm shadow-amber-500/50" style="width: 33.34%" title="Last Third: {{ $this->formatTime($todayForecast['divisions']['3/3']['start']) }} - {{ $this->formatTime($todayForecast['divisions']['3/3']['end']) }}"></div>
                </div>

                <div class="flex justify-between text-[11px] text-slate-500 px-1">
                    <span>1st Third ({{ $todayForecast['durations']['third_human'] }})</span>
                    <span>2nd Third ({{ $todayForecast['durations']['third_human'] }})</span>
                    <span class="text-amber-400 font-medium">Blessed Last Third ({{ $todayForecast['durations']['third_human'] }})</span>
                </div>
            </div>

            <!-- Key Milestones 4-Column Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Sunset / Maghrib -->
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700 transition">
                    <div class="flex items-center justify-between text-slate-400 mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider">Sunset (Maghrib)</span>
                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div class="text-2xl font-black text-slate-100">
                        {{ $this->formatTime($todayForecast['timestamps']['sunset']) }}
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Start of Islamic night</p>
                </div>

                <!-- 2. Islamic Midnight -->
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700 transition">
                    <div class="flex items-center justify-between text-slate-400 mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider">Islamic Midnight</span>
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </div>
                    <div class="text-2xl font-black text-slate-100">
                        {{ $this->formatTime($todayForecast['midnight']) }}
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Exact middle of the night (Isha cutoff)</p>
                </div>

                <!-- 3. The Last Third (Tahajjud) - Golden Accent -->
                <div class="relative bg-gradient-to-b from-amber-950/40 to-slate-950/90 border-2 border-amber-500/60 rounded-2xl p-5 shadow-lg shadow-amber-950/40">
                    <div class="absolute -top-2.5 right-3 px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                        Tahajjud Peak
                    </div>
                    <div class="flex items-center justify-between text-amber-300 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider">Last Third Starts</span>
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div class="text-2xl font-black text-amber-200">
                        {{ $this->formatTime($todayForecast['divisions']['3/3']['start']) }}
                    </div>
                    <p class="text-xs text-amber-300/80 mt-1">Divine descent & accepted dua</p>
                </div>

                <!-- 4. Fajr / Dawn -->
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-5 hover:border-slate-700 transition">
                    <div class="flex items-center justify-between text-slate-400 mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider">{{ $method === 'sunset_to_sunrise' ? 'Sunrise' : 'Fajr (Dawn)' }}</span>
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-2xl font-black text-slate-100">
                        {{ $this->formatTime($todayForecast['timestamps']['night_end']) }}
                    </div>
                    <p class="text-xs text-slate-500 mt-1">End of night & Tahajjud</p>
                </div>
            </div>

            <!-- Detailed Thirds Breakdown -->
            <div class="mt-8 pt-6 border-t border-slate-800/80">
                <h3 class="text-sm font-bold text-slate-300 mb-4 flex items-center gap-2">
                    <span>The Three Divisions of the Night</span>
                    <span class="text-xs font-normal text-slate-500">(Each third is ~{{ $todayForecast['durations']['third_human'] }})</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($todayForecast['divisions'] as $fraction => $division)
                        <div class="p-4 rounded-2xl {{ !empty($division['is_last_third']) ? 'bg-amber-500/10 border border-amber-500/30' : 'bg-slate-900/60 border border-slate-800' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold {{ !empty($division['is_last_third']) ? 'text-amber-300' : 'text-slate-400' }}">
                                    {{ $fraction }} &bull; {{ $division['label'] }}
                                </span>
                                @if (!empty($division['is_last_third']))
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300 font-bold">Recommended</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <div>
                                    <span class="text-[11px] text-slate-500 block">Start</span>
                                    <span class="font-bold text-slate-200">{{ $this->formatTime($division['start']) }}</span>
                                </div>
                                <span class="text-slate-600 font-light">&rarr;</span>
                                <div class="text-right">
                                    <span class="text-[11px] text-slate-500 block">End</span>
                                    <span class="font-bold text-slate-200">{{ $this->formatTime($division['end']) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Spiritual Wisdom & Hadith Card -->
        <section class="rounded-3xl border border-slate-800/80 bg-slate-900/40 backdrop-blur-md p-6 sm:p-7 text-slate-300">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex-shrink-0">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                </div>
                <div class="space-y-2">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-amber-300">The Virtue of the Last Third of the Night</h4>
                    <p class="text-sm leading-relaxed text-slate-300 italic">
                        The Messenger of Allah ﷺ said: 
                        <span class="text-slate-100 font-medium">"Our Lord, the Blessed and Exalted, descends every night to the lowest heaven when one-third of the night remains and says: 'Who calls upon Me, that I may answer him? Who asks of Me, that I may give him? Who seeks My forgiveness, that I may forgive him?'"</span>
                    </p>
                    <p class="text-xs text-slate-500">
                        Reference: Sahih al-Bukhari 1145, Sahih Muslim 758.
                    </p>
                </div>
            </div>
        </section>
    @else
        <!-- 7-DAY FORECAST VIEW -->
        <section class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-lg font-bold text-slate-100">7-Day Night Divisions Schedule</h2>
                <span class="text-xs text-slate-400">{{ $location['name'] }} &bull; {{ $method === 'sunset_to_sunrise' ? 'Maghrib to Sunrise' : 'Maghrib to Fajr' }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($weekForecasts as $forecast)
                    <div class="rounded-2xl border {{ $forecast['date'] === Carbon\Carbon::today()->format('Y-m-d') ? 'border-amber-500/50 bg-gradient-to-b from-slate-900 via-slate-900 to-amber-950/20' : 'border-slate-800 bg-slate-900/60' }} p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <div>
                                <span class="font-bold text-slate-100 text-sm block">
                                    {{ Carbon\Carbon::parse($forecast['date'])->format('l, M j, Y') }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    Duration: {{ $forecast['durations']['total_human'] }}
                                </span>
                            </div>
                            @if ($forecast['date'] === Carbon\Carbon::today()->format('Y-m-d'))
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    Tonight
                                </span>
                            @endif
                        </div>

                        <!-- 3 Thirds Grid -->
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            <!-- 1/3 -->
                            <div class="bg-slate-950/60 p-2.5 rounded-xl border border-slate-800/80">
                                <span class="text-[10px] text-slate-400 font-semibold block mb-1">1st Third</span>
                                <span class="font-bold text-slate-200 block">{{ $this->formatTime($forecast['divisions']['1/3']['start']) }}</span>
                                <span class="text-[10px] text-slate-500 block">to {{ $this->formatTime($forecast['divisions']['1/3']['end']) }}</span>
                            </div>

                            <!-- 2/3 -->
                            <div class="bg-slate-950/60 p-2.5 rounded-xl border border-slate-800/80">
                                <span class="text-[10px] text-slate-400 font-semibold block mb-1">2nd Third</span>
                                <span class="font-bold text-slate-200 block">{{ $this->formatTime($forecast['divisions']['2/3']['start']) }}</span>
                                <span class="text-[10px] text-slate-500 block">to {{ $this->formatTime($forecast['divisions']['2/3']['end']) }}</span>
                            </div>

                            <!-- 3/3 (Last 3rd) -->
                            <div class="bg-amber-500/10 p-2.5 rounded-xl border border-amber-500/30">
                                <span class="text-[10px] text-amber-300 font-bold block mb-1">Last 3rd</span>
                                <span class="font-black text-amber-200 block">{{ $this->formatTime($forecast['divisions']['3/3']['start']) }}</span>
                                <span class="text-[10px] text-amber-300/70 block">to {{ $this->formatTime($forecast['divisions']['3/3']['end']) }}</span>
                            </div>
                        </div>

                        <!-- Bottom row: Sunset, Midnight, End of night -->
                        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 px-1">
                            <span>Sunset: <strong class="text-slate-300">{{ $this->formatTime($forecast['timestamps']['sunset']) }}</strong></span>
                            <span>Midnight: <strong class="text-slate-300">{{ $this->formatTime($forecast['midnight']) }}</strong></span>
                            <span>{{ $method === 'sunset_to_sunrise' ? 'Sunrise' : 'Fajr' }}: <strong class="text-slate-300">{{ $this->formatTime($forecast['timestamps']['night_end']) }}</strong></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
