<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

<div class="py-12">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    @if (session('success'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-700">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-red-700">
        {{ $errors->first() }}
    </div>
     @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    @if ($activeMembership)
    <p class="text-gray-800 mb-4">Active colocation: <strong>{{ $activeMembership->colocation->name }}</strong></p>
    <a href="{{ route('colocations.show', $activeMembership->colocation) }}" class="inline-block rounded bg-blue-600 px-4 py-2 text-white">Open colocation</a>
    @else
    <p class="text-gray-800 mb-4">You do not have an active colocation.</p>
    <a href="{{ route('colocations.create') }}" class="inline-block rounded bg-blue-600 px-4 py-2 text-white">Create colocation</a>
    @endif

    @if (auth()->user()->role === 'admin')
    <div class="mt-6">
    <a href="{{ route('admin.index') }}" class="inline-block rounded bg-gray-900 px-4 py-2 text-white">Admin dashboard</a>
    </div>
    @endif
    </div>
</div>
</div>
</x-app-layout>
