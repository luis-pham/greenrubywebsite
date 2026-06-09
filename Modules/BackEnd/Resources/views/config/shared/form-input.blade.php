@php
    $formHorizontal = $formHorizontal ?? true;
@endphp

<div class="form-group {{ $formHorizontal ? 'row' : '' }}">
    <label class="{{ $formHorizontal ? 'col-md-3 col-form-label' : '' }}">{{ $obj->label }}</label>
    @if ($formHorizontal)
        <div class="col-md-9">
    @endif
        @if ($obj->type == config('backend.configInput.textbox'))
            <input type="text" name="{{ $obj->key }}" value="{{ old($obj->key, $obj->value) }}" class="form-control" placeholder="Nhập {{ $obj->label }}..." maxlength="255" autocomplete="off" />
        @elseif ($obj->type == config('backend.configInput.textarea'))
            <textarea name="{{ $obj->key }}" class="form-control" placeholder="Nhập {{ $obj->label }}..." rows="5" autocomplete="off">{{ old($obj->key, $obj->value) }}</textarea>
        @elseif ($obj->type == config('backend.configInput.texteditor'))
            <textarea name="{{ $obj->key }}" class="form-control text-editor" placeholder="Nhập {{ $obj->label }}..." rows="5" autocomplete="off">{{ old($obj->key, $obj->value) }}</textarea>
        @elseif ($obj->type == config('backend.configInput.selectbox'))
            @php
                $listValue = json_decode($obj->list_value);
            @endphp
            <select name="{{ $obj->key }}" class="form-control">
                <option value="">Chọn</option>
                @foreach ($listValue as $key => $value)
                    <option value="{{ $key }}" {{ $key == old($obj->key, $obj->value) ? 'selected="selected"' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        @elseif ($obj->type == config('backend.configInput.image'))
            <input type="hidden" name="{{ $obj->key }}" value="{{ old($obj->key, $obj->value) }}" class="image-select" data-link-full="{{ Utilities::getFileLink(old($obj->key, $obj->value)) }}" data-file-manager-url="{{ route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')]) }}" />
        @elseif ($obj->type == config('backend.configInput.gallery'))
            <div class="gallery" key="{{ $obj->key }}">
                @php
                    $listImage = old($obj->key, $obj->value) ? json_decode(old($obj->key, $obj->value)) : [];
                @endphp
                <div id="list-image-{{ $obj->key }}" class="list-image row">
                    @for ($i = 0; $i < count($listImage); $i++)
                        @php
                            $thumbnail = property_exists($listImage[$i], 'thumbnail') ? $listImage[$i]->thumbnail : null;
                            $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $listImage[$i]->link : $thumbnail)
                        @endphp
                        <div class="item col-4 col-lg-3" data-obj="{{ json_encode($listImage[$i], JSON_UNESCAPED_UNICODE) }}">
                            <div class="box-dragdrop position-relative">
                                <div class="image-wrapper position-relative">
                                    <a href="{{ Utilities::getFileLink($listImage[$i]->link) }}" data-fancybox="gallery-{{ $obj->key }}">
                                        <img src="{{ $thumbnailFull }}" alt="{{ $listImage[$i]->title }}" class="position-absolute w-100 h-100" />
                                    </a>
                                    <div class="action position-absolute">
                                        <a href="#" class="btn-edit btn btn-info btn-sm" title="Sửa">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <a href="#" class="btn-delete btn btn-danger btn-sm" title="Xóa">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="name position-absolute w-100 text-center">
                                    <span class="give-ellipsis after-2-lines">{{ $listImage[$i]->title }}</span>
                                </div>
                            </div>
                        </div>
                    @endfor
                    <div class="item col-4 col-lg-3">
                        <div class="image-wrapper position-relative">
                            <a href="#" class="btn-open-modal-select icon d-block position-absolute w-100 h-100">
                                <i class="far fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
                {{ Form::hidden($obj->key, json_encode($listImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}
            </div>
        @elseif ($obj->type == config('backend.configInput.sourceData'))
            <div class="source-data" key="{{ $obj->key }}">
                @php
                    $listId = old($obj->key, $obj->value) ? json_decode(old($obj->key, $obj->value)) : [];
                @endphp
                <div id="list-item-{{ $obj->key }}" class="list-item"></div>
                <a href="#" class="btn-open-modal-select btn btn-primary btn-sm"><i class="fas fa-plus"></i> Thêm</a>
                {{ Form::hidden($obj->key, json_encode($listId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}
            </div>
        @endif
    @if ($formHorizontal)
        </div>
    @endif
</div>