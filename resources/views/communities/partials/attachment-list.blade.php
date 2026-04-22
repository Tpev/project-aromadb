@if($attachments->isNotEmpty())
    <div class="mt-4 flex flex-wrap gap-2">
        @foreach($attachments as $attachment)
            <a href="{{ route($downloadRouteName, $attachment) }}"
               class="inline-flex items-center gap-3 rounded-2xl border border-[#dfe5cf] bg-white px-3 py-2 text-sm text-gray-700 shadow-sm transition hover:border-[#647a0b] hover:text-[#647a0b]">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#f7faef] text-[#647a0b]">↧</span>
                <span class="min-w-0">
                    <span class="block truncate font-semibold">{{ $attachment->original_name }}</span>
                    <span class="block text-xs text-gray-500">{{ \App\Support\UploadLimit::formatBytes($attachment->size) }}</span>
                </span>
            </a>
        @endforeach
    </div>
@endif
