<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Directory - CPSU Navigator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold">📋 Office Directory</h1>
                <a href="{{ url('/') }}" class="text-blue-600 hover:text-blue-800">← Back to Map</a>
            </div>
        </div>
    </nav>
    
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="grid gap-3">
            @php
                $offices = App\Models\Office::all();
            @endphp
            @foreach($offices as $office)
            <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-800">{{ $office->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $office->building }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="px-2 py-0.5 rounded-full" style="background: #dbeafe; color: #3b82f6">{{ $office->category }}</span>
                        </p>
                    </div>
                    <a href="{{ url('/?office=' . $office->office_id) }}" 
                       class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                        <i class="fas fa-directions mr-1"></i> Navigate
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>