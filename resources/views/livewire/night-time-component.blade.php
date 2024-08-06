<div class="max-w-7xl mx-auto p-4">
    <h1 class="text-2xl sm:text-3xl font-bold mb-2 text-center">Night Time Forecast</h1>
    <h2 class="text-lg sm:text-xl mb-4 text-center text-gray-600">{{ $location['name'] }}</h2>

    @if ($loading)
        <div class="text-center p-4">Loading...</div>
    @elseif ($error)
        <div class="text-center p-4 text-red-500">{{ $error }}</div>
    @else
        <div class="space-y-6">
            @foreach ($forecasts as $forecast)
                <div class="bg-white shadow-md rounded-lg p-4">
                    <h2 class="text-xl font-semibold mb-4 text-center">
                        {{ Carbon\Carbon::parse($forecast['date'])->format('l, F j, Y') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @php
                            $nightTimes = $this->calculateNightTimes($forecast['sunset'], $forecast['sunrise']);
                        @endphp
                        @foreach (['1/3', '2/3', '3/3'] as $fraction)
                            <div class="bg-slate-800 text-white rounded-lg p-4">
                                <div class="flex items-center justify-between pb-2">
                                    <h3 class="text-sm font-medium">
                                        {{ $fraction }} of Night
                                    </h3>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <div>
                                        <p class="text-sm text-gray-300">Start</p>
                                        <p class="text-lg font-bold">{{ $nightTimes[$fraction]['start'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-300">End</p>
                                        <p class="text-lg font-bold">{{ $nightTimes[$fraction]['end'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
