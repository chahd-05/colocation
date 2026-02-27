<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

<div class="py-8">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    @if (session('success'))
<div class="rounded border border-green-200 bg-green-50 p-3 text-green-700">
    {{ session('success') }}
</div>
    @endif
    @if ($errors->any())
<div class="rounded border border-red-200 bg-red-50 p-3 text-red-700">
    {{ $errors->first() }}
</div>
    @endif

<div class="grid gap-4 md:grid-cols-4">
    <div class="rounded bg-white p-4 shadow">Users: {{ $usersCount }}</div>
    <div class="rounded bg-white p-4 shadow">Colocations: {{ $colocationsCount }}</div>
    <div class="rounded bg-white p-4 shadow">Expenses: {{ $expensesCount }}</div>
    <div class="rounded bg-white p-4 shadow">Banned: {{ $bannedCount }}</div>
</div>

<div class="rounded bg-white p-4 shadow">
    <h3 class="font-semibold mb-3">Users</h3>
    <table class="w-full text-sm">
    <thead>
    <tr class="border-b text-left">
        <th class="py-2">Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Reputation</th>
        <th>Status</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @foreach ($users as $user)
    <tr class="border-b">
    <td class="py-2">{{ $user->name }}</td>
    <td>{{ $user->email }}</td>
    <td>{{ $user->role }}</td>
    <td>{{ $user->reputation }}</td>
    <td>{{ $user->is_banned ? 'Banned' : 'Active' }}</td>
    <td>
       @if ($user->is_banned)
       <form method="POST" action="{{ route('admin.users.unban', $user) }}">
       @csrf
       <button class="rounded bg-green-600 px-3 py-1 text-white">Unban</button>
       </form>
       @else
       <form method="POST" action="{{ route('admin.users.ban', $user) }}">
       @csrf
       <button class="rounded bg-red-600 px-3 py-1 text-white">Ban</button>
       </form>
       @endif
    </td>
    </tr>
    @endforeach
    </tbody>
    </table>
    </div>
    </div>
    </div>
</x-app-layout>
