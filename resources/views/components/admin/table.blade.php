<table class="table table-bordered table-responsive dataTable">
    <thead>
        <tr>
            @foreach ($headers as $header)
                <th class="px-4 py-2">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
            @forelse ($rows as $row)
                <tr>
                    @foreach ($headers as $key => $header)
                        <td class="px-2 py-2">
                            {{ $row[$key] ?? '-' }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="text-center px-4 py-3 text-gray-500">
                        No data available
                    </td>
                </tr>
            @endforelse
        </tbody>
</table>