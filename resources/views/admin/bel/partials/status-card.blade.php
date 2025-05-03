<div id="{{ $id }}" class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition duration-200">
    <div class="flex items-start">
        <div id="{{ $id }}Icon" class="p-3 rounded-full bg-green-100 flex-shrink-0">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
            </svg>
        </div>
        <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">{{ $title }}</h3>
            <p id="{{ $statusId }}" class="text-lg font-semibold text-green-600">{{ $status }}</p>
            <p id="{{ $detailsId }}" class="text-xs text-gray-500 mt-1">{{ $details }}</p>
        </div>
    </div>
</div>