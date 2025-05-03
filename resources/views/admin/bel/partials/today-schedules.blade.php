@if($todaySchedules->count() > 0)
<div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded-r-lg">
    <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-medium text-blue-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            Today's Schedule ({{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }})
        </h3>
        <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-medium">
            {{ $todaySchedules->count() }} {{ $todaySchedules->count() > 1 ? 'Schedules' : 'Schedule' }}
        </span>
    </div>
    
    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach($todaySchedules as $schedule)
        <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-100 hover:shadow-md transition duration-200 flex justify-between items-center">
            <div>
                <span class="font-medium text-gray-800">{{ $schedule->formatted_time }}</span>
                <span class="text-xs text-gray-500 block mt-1">File {{ $schedule->file_number }}</span>
            </div>
            <div class="flex items-center">
                <span class="px-2 py-1 text-xs rounded-full font-medium {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                </span>
                @if($schedule->is_now)
                <span class="ml-2 px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-800 animate-pulse">
                    SEKARANG
                </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif