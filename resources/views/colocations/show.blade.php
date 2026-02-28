<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $colocation->name }}
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
            @if (session('link'))
                <div class="rounded border border-blue-200 bg-blue-50 p-3 text-blue-700 break-all">
                    Invitation link: {{ session('link') }}
                </div>
            @endif

            <div class="rounded bg-white p-4 shadow">
                <form method="POST" action="{{ route('colocations.update', $colocation) }}" class="flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $colocation->name }}" class="rounded border-gray-300">
                    @if ($isOwner)
                        <button class="rounded bg-gray-800 px-3 py-2 text-white">Rename</button>
                    @endif
                </form>
            </div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded bg-white p-4 shadow">
        <h3 class="font-semibold mb-3">Members</h3>
        <ul class="space-y-2">
    @foreach ($memberships as $membership)
        <li class="flex items-center justify-between border-b pb-2">
            <div>
                <p class="font-medium">{{ $membership->user->name }}</p>
                <p class="text-sm text-gray-600">
                    {{ $membership->role }} | Reputation: {{ $membership->user->reputation }}
                </p>
            </div>
            @if ($isOwner && $membership->role !== 'owner')
                <form method="POST" action="{{ route('colocations.members.remove', [$colocation, $membership]) }}">
                    @csrf
                    <button class="rounded bg-red-600 px-3 py-1 text-white text-sm">Remove</button>
                </form>
            @endif
    </li>
            @endforeach
    </ul>

<div class="mt-4 flex gap-2">
    @if (! $isOwner)
        <form method="POST" action="{{ route('colocations.leave', $colocation) }}">
            @csrf
            <button class="rounded bg-yellow-600 px-3 py-2 text-white">Leave</button>
        </form>
    @endif
    @if ($isOwner)
        <form method="POST" action="{{ route('colocations.cancel', $colocation) }}">
            @csrf
            <button class="rounded bg-red-700 px-3 py-2 text-white">Cancel colocation</button>
        </form>
    @endif
</div>
</div>
<div class="rounded bg-white p-4 shadow space-y-4">
@if ($isOwner)
    <div>
        <h3 class="font-semibold mb-2">Invite member</h3>
        <form method="POST" action="{{ route('invitations.store', $colocation) }}" class="flex gap-2">
            @csrf
            <input type="email" name="email" required class="rounded border-gray-300 flex-1" placeholder="email@example.com">
            <button class="rounded bg-blue-600 px-3 py-2 text-white">Invite</button>
        </form>
    </div>
@endif

@if ($isOwner)
    <div>
        <h3 class="font-semibold mb-2">Add category</h3>
        <form method="POST" action="{{ route('categories.store', $colocation) }}" class="flex gap-2">
            @csrf
            <input type="text" name="name" required class="rounded border-gray-300 flex-1">
            <button class="rounded bg-blue-600 px-3 py-2 text-white">Add</button>
        </form>
    </div>
@endif

<div>
    <h3 class="font-semibold mb-2">Categories</h3>
    <ul class="space-y-1">
    @foreach ($categories as $category)
    <li class="flex items-center justify-between">
    <span>{{ $category->name }}</span>
     @if ($isOwner)
        <form method="POST" action="{{ route('categories.destroy', [$colocation, $category]) }}">
            @csrf
            @method('DELETE')
            <button class="text-sm text-red-600">Delete</button>
        </form>
    @endif
    </li>
    @endforeach
        </ul>
    </div>
</div></div>

            <div class="rounded bg-white p-4 shadow">
                <h3 class="font-semibold mb-3">Add expense</h3>
                <form method="POST" action="{{ route('expenses.store', $colocation) }}" class="grid gap-3 md:grid-cols-5">
                    @csrf
                    <input type="text" name="title" required placeholder="Title" class="rounded border-gray-300">
                    <input type="number" name="amount" required min="0.01" step="0.01" placeholder="Amount" class="rounded border-gray-300">
                    <input type="date" name="expenses_date" required class="rounded border-gray-300">
                    <select name="payer_id" required class="rounded border-gray-300">
                        <option value="">Payer</option>
                        @foreach ($memberships as $membership)
                            <option value="{{ $membership->user->id }}">{{ $membership->user->name }}</option>
                        @endforeach
                    </select>
                    <select name="category_id" required class="rounded border-gray-300">
                        <option value="">Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button class="rounded bg-blue-600 px-4 py-2 text-white md:col-span-5 md:w-fit">Add expense</button>
                </form>
            </div>

            <div class="rounded bg-white p-4 shadow">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold">Expenses</h3>
                    <form method="GET" action="{{ route('colocations.show', $colocation) }}" class="flex items-center gap-2">
                        <label class="text-sm">Month</label>
                        <input type="month" name="month" value="{{ $month }}" class="rounded border-gray-300">
                        <button class="rounded bg-gray-700 px-3 py-1 text-white text-sm">Filter</button>
                        <a href="{{ route('colocations.show', $colocation) }}" class="text-sm text-blue-600">All</a>
                    </form>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="py-2">Date</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Payer</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr class="border-b">
                                <td class="py-2">{{ optional($expense->expenses_date)->format('Y-m-d') }}</td>
                                <td>{{ $expense->title }}</td>
                                <td>{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->category->name ?? '-' }}</td>
                                <td>{{ $expense->payer->name ?? '-' }}</td>
                                <td>
                                    @if ($isOwner || auth()->id() === $expense->payer_id)
                                        <form method="POST" action="{{ route('expenses.destroy', [$colocation, $expense]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-3 text-gray-600">No expenses.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded bg-white p-4 shadow">
                    <h3 class="font-semibold mb-3">Balances</h3>
                    <p class="mb-2 text-sm text-gray-700">Total expenses: {{ number_format($balanceData['total_expenses'], 2) }}</p>
                    <ul class="space-y-2">
                        @foreach ($balanceData['members'] as $member)
                            <li class="border-b pb-2">
                                <p class="font-medium">{{ $member['user']->name }}</p>
                                <p class="text-sm text-gray-600">
                                    Paid: {{ number_format($member['paid'], 2) }} |
                                    Share: {{ number_format($member['share'], 2) }} |
                                    Balance: {{ number_format($member['balance'], 2) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded bg-white p-4 shadow">
                    <h3 class="font-semibold mb-3">Who pays who</h3>
                    <ul class="space-y-3">
                        @forelse ($balanceData['settlements'] as $settlement)
                            <li class="border-b pb-2">
                                <p class="text-sm">
                                    <strong>{{ $settlement['from_user']->name }}</strong>
                                    pays
                                    <strong>{{ $settlement['to_user']->name }}</strong>
                                    : {{ number_format($settlement['amount'], 2) }}
                                </p>
                                @if (auth()->id() === $settlement['from_user']->id || $isOwner)
                                    <form method="POST" action="{{ route('payments.store', $colocation) }}" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="from_user_id" value="{{ $settlement['from_user']->id }}">
                                        <input type="hidden" name="to_user_id" value="{{ $settlement['to_user']->id }}">
                                        <input type="hidden" name="amount" value="{{ $settlement['amount'] }}">
                                        <button class="rounded bg-green-600 px-3 py-1 text-white text-sm">Mark paid</button>
                                    </form>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-gray-600">No debt.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded bg-white p-4 shadow">
                    <h3 class="font-semibold mb-3">Stats by category</h3>
                    <ul class="space-y-1 text-sm">
                        @forelse ($categoryStats as $cat => $amount)
                            <li>{{ $cat ?: 'No category' }}: {{ number_format($amount, 2) }}</li>
                        @empty
                            <li class="text-gray-600">No data.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="rounded bg-white p-4 shadow">
                    <h3 class="font-semibold mb-3">Monthly stats</h3>
                    <ul class="space-y-1 text-sm">
                        @forelse ($monthlyStats as $m => $amount)
                            <li>{{ $m }}: {{ number_format($amount, 2) }}</li>
                        @empty
                            <li class="text-gray-600">No data.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
