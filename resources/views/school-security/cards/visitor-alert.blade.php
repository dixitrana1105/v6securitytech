<x-layout.default>
    <style>
        .text-red {
            color: red !important;
            font-weight: 600;
        }

        .text-green {
            color: green !important;
            font-weight: 600;
        }
    </style>
    <div class="flex justify-center mb-4">
        <h1 class="text-2xl font-bold">Visitor Alerts</h1>
    </div>

    <div class="table-responsive" x-data="form">
        <table class="min-w-full shadow-lg rounded-lg overflow-hidden">
            <thead>
                <tr>
                    <th class="font-bold py-2 px-6">S.No</th>
                    <th class="font-bold py-2 px-6">Card ID</th>
                    <th class="font-bold py-2 px-6">Comments</th>
                    <th class="font-bold py-2 px-6">School</th>
                    <th class="font-bold py-2 px-6">Visitor Name</th>
                    <th class="font-bold py-2 px-6">Visitor Contact Number</th>
                    <th class="font-bold py-2 px-6">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($visitors as $key => $visitor)
                    <tr class="border-b duration-300">
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $visitor->card?->serial_id ?? "N/A" }}</td>
                        <td>{{ $visitor->comments }}</td>
                        <td>{{ $visitor->building?->name ?? 'N/A' }}</td>
                        <td>{{ $visitor->visitor?->visitor_name ?? 'N/A' }}</td>
                        <td>{{ $visitor->visitor?->mobile ?? 'N/A' }}</td>
                        <td>
                            @if(!$visitor->is_read && str_contains($visitor->comments ?? '', 'wrong location'))
                                <form action="{{ route('school.security.card.alert-status-change', $visitor->id) }}"
                                    method="post">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_read" value="1">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Mark as Read
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</x-layout.default>