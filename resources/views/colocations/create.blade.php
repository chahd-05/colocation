<x-app-layout>
    <x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
     Create Colocation
</h2></x-slot>
<div class="py-8">
<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
    @if ($errors->any())
    <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-red-700">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="rounded bg-white p-6 shadow">
    form method="POST" action="{{ route('colocations.store') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm text-gray-700">Name</label>
        <input type="text" name="name" required class="mt-1 w-full rounded border-gray-300">
    </div>

    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-white">Create</button>

    </form>
    </div>
    </div>
    </div>
</x-app-layout>
