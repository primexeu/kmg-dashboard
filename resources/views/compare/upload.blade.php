<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Comparison - KMG Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Order Comparison
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Upload PDF Order and Excel data for comparison
                </p>
            </div>
            
            <form class="mt-8 space-y-6" action="{{ route('compare.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="pdf_file" class="block text-sm font-medium text-gray-700">
                            PDF Order File
                        </label>
                        <input id="pdf_file" name="pdf_file" type="file" accept=".pdf" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('pdf_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="excel_file" class="block text-sm font-medium text-gray-700">
                            Excel Data File
                        </label>
                        <input id="excel_file" name="excel_file" type="file" accept=".xlsx,.xls" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('excel_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    There were errors with your submission:
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Compare Order
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="{{ route('compare.form') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                        Compare Order Instead
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
