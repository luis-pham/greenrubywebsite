<div class="card">
    <div class="card-body">
        <p class="mb-0">
            Khởi tạo
            lúc <strong><span class="text-success">{{ Utilities::formatDisplayDateTime($obj->created_at )}}</span></strong>
            @php
                $created = Utilities::getUserById($obj->created_by);
            @endphp
            @if ($created)
                bởi <strong><a href="{{ route('backend.user.info', ['id' => $created->id]) }}" class="text-success" target="_blank">{{ $created->fullname }}</a></strong></p>
            @endif
        @if ($obj->updated_at || $obj->updated_by)
            <p class="mt-2 mb-0">
                Cập nhật lần cuối
                @if ($obj->updated_at)
                    lúc <strong><span class="text-success">{{ Utilities::formatDisplayDateTime($obj->updated_at )}}</span></strong>
                @endif
                @if ($obj->updated_by)
                    @php
                        $updated = Utilities::getUserById($obj->updated_by);
                    @endphp
                    bởi <strong><a href="{{ $updated ? route('backend.user.info', ['id' => $updated->id]) : 'javascript:void(0)' }}" class="text-success" target="_blank">{{ $updated ? $updated->fullname : '' }}</a></strong>
                @endif
            </p>
        @endif
    </div>
</div>