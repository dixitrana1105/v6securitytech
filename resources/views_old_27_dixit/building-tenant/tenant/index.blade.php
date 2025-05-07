<x-layout.default>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="flex justify-center mb-4">
        <h1 class="text-2xl font-bold">Tenant List</h1>
    </div>
    <form method="GET" class="container mt-3">
        <div class="flex gap-2">
            <div class="flex justify-start items-center mb-4 space-x-2">
                <label for="statusFilter" class="whitespace-nowrap"><strong>Filter by Status:</strong></label>
                <select id="statusFilter" name="status" class="form-input w-32" @change="filterStatus($event)">
                    <option value="">All</option>
                    <option value="1" {{ request()->input('status') == '1' ? 'selected' : '' }}>Active
                    </option>
                    <option value="0" {{ request()->input('status') == '0' ? 'selected' : '' }}>Inactive
                    </option>
                </select>
            </div>
            <div class="form-group-item">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
    <br>
    <div class="table-responsive">
        <table class="min-w-full shadow-lg rounded-lg overflow-hidden">
            <thead>
                <tr>
                    <th class="font-bold py-2 px-6">S.No</th>
                    <th class="font-bold py-2 px-8">Register Date</th>
                    <th class="font-bold py-2 px-8">User ID</th>
                    <th class="font-bold py-2 px-8">Flat/Office Number</th>
                    <th class="font-bold py-2 px-8">Contact Person</th>
                    <th class="font-bold py-2 px-8">Contact Number</th>
                    <th class="font-bold py-2 px-8">WhatsApp Number</th>
                    <th class="font-bold py-2 px-8">Emergency Contact Number</th>
                    <th class="font-bold py-2 px-8">Email ID</th>
                    <th class="font-bold py-2 px-8">Status</th>
                    <th class="font-bold py-2 px-8">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tenant as $key => $item)
                    <tr class="border-b duration-300">
                        <th>{{ $key + 1 }}</th>
                        <th>{{ $item->date }}</th>
                        <th>{{ $item->tenant_id }}</th>
                        <th>{{ $item->flat_office_no }}</th>
                        <th>{{ $item->contact_person }}</th>
                        <th>{{ $item->contact_number }}</th>
                        <th>{{ $item->whatsapp_no }}</th>
                        <th>{{ $item->emergency_contact_no }}</th>
                        <th>{{ $item->email }}</th>
                        <td class="py-2 px-8">
                            @if ($item->status == 1)
                                <span class="text-green-500 font-bold">Active</span>
                            @else
                                <span class="text-red-500 font-bold">Inactive</span>
                            @endif
                        </td>
                        <td class="py-2 px-8">
                            <button
                                class="btn-primary text-white font-bold py-1 px-4 rounded shadow-md transition duration-300"
                                onclick="newMessage('{{ $item->id }}')">
                                Message
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    @php
        $sender_id = Auth::guard('buildingtenant')->check()
            ? Auth::guard('buildingtenant')->user()->id
            : (Auth::guard('security_master')->check()
                ? Auth::guard('security_master')->user()->id
                : null);
    @endphp

    <script>
        let senderId = "{{ $sender_id }}";

        function newMessage(id) {
            $.ajax({
                url: "{{ route('tenant-send-message') }}",
                type: 'POST',
                data: {
                    reciever_id: id,
                    sender_id: senderId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 200 && response.data.redirect) {
                        window.location.href = response.data.redirect + '?reciever_id=' + response.data
                            .reciever_id + '&sender_id=' + response.data.sender_id +
                            '&receiver_type=' + response.data.reciever_type + '&sender_type=' + response.data
                            .sender_type;
                    } else {
                        alert('Failed to send message. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        }
    </script>

</x-layout.default>
