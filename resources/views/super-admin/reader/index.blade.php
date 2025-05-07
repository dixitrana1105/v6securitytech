<x-layout.default>
    <div class="flex justify-center mb-4">
        <h1 class="text-2xl font-bold">Reader List</h1>
    </div>

    <style>
        .alert-danger {
            color: red;
        }

        .alert-success {
            color: #5CB85C;
        }
    </style>

    <div class="table-responsive" x-data="form">
        <table class="min-w-full shadow-lg rounded-lg overflow-hidden">
            <thead>
                <tr>
                    <th class="font-bold py-2 px-6">S.No</th>
                    <th class="font-bold py-2 px-6">Serial ID</th>
                    <th class="font-bold py-2 px-6">Building</th>
                    <th class="font-bold py-2 px-6 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($readers as $key => $reader)
                    <tr class="border-b duration-300">
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $reader->serial_id }}</td>
                        <td>{{ $reader->building->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <a href="{{ route('super-admin.reader-edit', $reader->id) }}" x-tooltip="Edit">
                                ✏️
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($readers->hasPages())
            <div class="mt-4">
                {{ $readers->links() }}
            </div>
        @endif

    </div>
</x-layout.default>