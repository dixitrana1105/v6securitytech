<x-layout.default>
    <div class="flex justify-center mb-4">
        <h1 class="text-2xl font-bold">Card List</h1>
    </div>

    <style>
        .alert-danger {
            color: red;
        }

        .alert-success {
            color: #5CB85C;
        }
    </style>

    <form method="GET" class="container mt-3">
        <div class="flex gap-2">
            <div class="flex justify-start items-center mb-4 space-x-2">
                <label for="assign_status" class="whitespace-nowrap"><strong>Assign Status:</strong></label>
                <select id="assign_status" name="assign_status" class="form-input w-32">
                    <option value="">All</option>
                    <option value="assigned" {{ request('assign_status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="unassigned" {{ request('assign_status') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                </select>
            </div>
            <div class="form-group-item">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <div class="table-responsive" x-data="form">
        <table class="min-w-full shadow-lg rounded-lg overflow-hidden">
            <thead>
                <tr>
                    <th class="font-bold py-2 px-6">S.No</th>
                    <th class="font-bold py-2 px-6">Serial ID</th>
                    <th class="font-bold py-2 px-6">Building</th>
                    <th class="font-bold py-2 px-6">Status</th>
                    <th class="font-bold py-2 px-6">Assign Visitor ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cards as $key => $card)
                    <tr class="border-b duration-300">
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $card->serial_id }}</td>
                        <td>{{ $card->building->name ?? 'N/A' }}</td>
                        <td>
                            <span
                                class="px-2 py-1 rounded {{ $card->assign_status ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $card->assign_status }}
                            </span>
                        </td>
                        {{-- {{ dd($card->visitor_master); }} --}}
                        <td>
                            @if($card->assign_status === 'assigned')
                                {{ $card->visitor_master->visitor_id ?? 'N/A' }}
                            @else
                                {{-- Show nothing or alternative text --}}
                                {{-- Optional: echo 'Unassigned' or just leave it blank --}}
                            @endif
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>



    </div>
</x-layout.default>
