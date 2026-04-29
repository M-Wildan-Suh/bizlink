@if ($list === 'guardian')
    @foreach ($guardians as $item)
        <tr class="text-neutral-600 border-b">
            <td class=" font-semibold line-clamp-2 py-2">{{ $item->url }}</td>
            <td class=" text-right">{{ $item->access ? number_format($item->access, 0, ',', '.') : 0 }}</td>
            <td class=" text-right">{{ $item->wa_access ? number_format($item->wa_access, 0, ',', '.') : 0 }}</td>
        </tr>
    @endforeach
@endif
@if ($list === 'category')
    @foreach ($categories as $item)
        <tr class="text-neutral-600 border-b">
            <td class=" font-semibold line-clamp-2 py-2">{{ $item->category }}</td>
            <td class=" text-right">{{ $item->access ? number_format($item->access, 0, ',', '.') : 0 }}</td>
            <td class=" text-right">{{ $item->wa_access ? number_format($item->wa_access, 0, ',', '.') : 0 }}</td>
        </tr>
    @endforeach
@endif
@if ($list === 'article')
    @foreach ($articles as $item)
        <tr class="text-neutral-600 border-b">
            <td class=" font-semibold line-clamp-2 py-2">{{ $item->judul }}</td>
            <td class=" text-right">{{ $item->access ? number_format($item->access, 0, ',', '.') : 0 }}</td>
            <td class=" text-right">{{ $item->wa_access ? number_format($item->wa_access, 0, ',', '.') : 0 }}</td>
        </tr>
    @endforeach
@endif
