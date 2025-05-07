<x-layout.default>
    <style>
        .text-red{
            color: red !important;
            font-weight: 600;
        }
        .text-green{
            color: green !important;
            font-weight: 600;
        }
    </style>
    <div class="flex justify-center mb-4">
        <h1 class="text-2xl font-bold">Today's Card History</h1>
    </div>

    <div class="table-responsive" x-data="form">
        <table class="min-w-full shadow-lg rounded-lg overflow-hidden">
            <thead>
                <tr>
                    <th class="font-bold py-2 px-6">S.No</th>
                    <th class="font-bold py-2 px-6">Card ID</th>
                    <th class="font-bold py-2 px-6">Card Status</th>
                    <th class="font-bold py-2 px-6">School</th>
                    <th class="font-bold py-2 px-6">Visitor Name</th>
                    <th class="font-bold py-2 px-6">Visitor Contact Number</th>
                    <th class="font-bold py-2 px-6">Visitor Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($visitors as $key => $visitor)
                    <tr class="border-b duration-300">
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $visitor->card?->serial_id ?? "N/A" }}</td>
                        <td>{{ $visitor->out_time ? 'Card Returned': 'Pending' }}</td>
                        <td>{{ $visitor->building->name ?? 'N/A' }}</td>
                        <td>{{ $visitor->visitor_name ?? 'N/A' }}</td>
                        <td>{{ $visitor->mobile ?? 'N/A' }}</td>
                        <td>
                            <span
                                class="px-2 py-1 rounded {{ $visitor->out_time ? 'text-green' : 'text-red' }}">
                                {{ $visitor->out_time ? 'Visit Completed': 'Visit Running' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</x-layout.default>