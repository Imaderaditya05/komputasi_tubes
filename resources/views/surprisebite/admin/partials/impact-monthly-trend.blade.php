@php
    $mealCols = array_column($monthlyTrend, 'meals');
    $maxMeals = $mealCols === [] ? 1 : max(1, max($mealCols));
@endphp
<div class="text-sm font-black text-slate-900">Monthly Trend {{ $trendYear }}</div>
<div class="mt-6 space-y-4" id="rt-impact-monthly-trend">
    @foreach ($monthlyTrend as $b)
        @php
            $wasteLabel = $b['waste_kg'] < 100
                ? rtrim(rtrim(number_format($b['waste_kg'], 1, ',', ''), '0'), ',') . ' kg'
                : rtrim(rtrim(number_format($b['waste_tons'], 2, ',', ''), '0'), ',') . ' ton';
        @endphp
        <div>
            <div class="flex items-center justify-between text-sm">
                <div class="font-bold text-slate-800">{{ $b['m'] }}</div>
                <div class="text-slate-600">{{ number_format($b['meals']) }} meals <span class="text-slate-400">•</span> {{ $wasteLabel }} dicegah</div>
            </div>
            <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-orange-500" style="width: {{ (int) round(($b['meals'] / $maxMeals) * 100) }}%"></div>
            </div>
        </div>
    @endforeach
</div>
