<div>
    <h1 class="text-2xl font-semibold text-gray-900 mb-4">Manage Affiliates</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
    
    <!-- Status Filter -->
    <div class="mb-4">
        <label for="statusFilter" class="mr-2">Filter by status:</label>
        <select wire:model.live="filterStatus" id="statusFilter" class="rounded-md border-gray-300">
            <option value="pending">Pending</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    <!-- Affiliates Table -->
     <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($affiliates as $affiliate)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $affiliate->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $affiliate->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($affiliate->status) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($affiliate->status == 'pending')
                                <button wire:click="setStatus({{ $affiliate->id }}, 'active')" class="text-indigo-600 hover:text-indigo-900">Approve</button>
                                <button wire:click="setStatus({{ $affiliate->id }}, 'rejected')" class="text-red-600 hover:text-red-900 ml-4">Reject</button>
                            @else
                                <button wire:click="setStatus({{ $affiliate->id }}, 'active')" class="text-indigo-600 hover:text-indigo-900">Activate</button>
                                <button wire:click="setStatus({{ $affiliate->id }}, 'inactive')" class="text-yellow-600 hover:text-yellow-900 ml-4">Deactivate</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $affiliates->links() }}</div>
</div>