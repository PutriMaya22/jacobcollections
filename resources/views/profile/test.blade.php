<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile Test') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-3xl font-bold text-blue-600 mb-4">Profile Page Test</h1>
                    
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <strong>Success!</strong> This page is working correctly.
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-800 mb-2">User Information</h3>
                            <p><strong>Name:</strong> {{ Auth::user()->name ?? 'Not available' }}</p>
                            <p><strong>Email:</strong> {{ Auth::user()->email ?? 'Not available' }}</p>
                            <p><strong>ID:</strong> {{ Auth::user()->id ?? 'Not available' }}</p>
                        </div>
                        
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-yellow-800 mb-2">Page Information</h3>
                            <p><strong>Route:</strong> {{ request()->route()->getName() ?? 'Not available' }}</p>
                            <p><strong>URL:</strong> {{ request()->url() }}</p>
                            <p><strong>Method:</strong> {{ request()->method() }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button id="testButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Test JavaScript
                        </button>
                        <div id="testResult" class="mt-2 text-sm text-gray-600"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Test page loaded successfully');
            
            const testButton = document.getElementById('testButton');
            const testResult = document.getElementById('testResult');
            
            if (testButton && testResult) {
                testButton.addEventListener('click', function() {
                    testResult.textContent = 'JavaScript is working! Button clicked at: ' + new Date().toLocaleTimeString();
                    testResult.className = 'mt-2 text-sm text-green-600';
                });
            }
        });
    </script>
</x-app-layout>

