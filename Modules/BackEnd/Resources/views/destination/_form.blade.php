{{-- Shared by create/edit. $obj is null on create. --}}
@php
    $value = fn ($field, $default = null) => old($field, $obj->{$field} ?? $default);
    $repeaters = [
        'facts' => ['title' => 'THÔNG TIN NHANH', 'icon' => 'fa-list', 'help' => 'Hiển thị thành bảng ngắn trên trang. Chỉ nhập thông tin đã kiểm chứng (vd. "Cách tiếp cận" – "Xuồng/kayak, phụ thuộc thủy triều"). Không nhập giá vé, giờ mở cửa chưa xác minh.', 'fields' => ['label' => 'Nhãn', 'value' => 'Nội dung']],
        'faq' => ['title' => 'CÂU HỎI THƯỜNG GẶP', 'icon' => 'fa-question-circle', 'help' => 'Câu hỏi thực tế của du khách. Hiển thị dạng thu gọn ở cuối trang.', 'fields' => ['q' => 'Câu hỏi', 'a' => 'Trả lời']],
        'sources' => ['title' => 'NGUỒN THAM KHẢO (CÔNG KHAI)', 'icon' => 'fa-book', 'help' => 'Nguồn chính thức dùng để kiểm chứng (UNESCO, Ban quản lý vịnh, Wikimedia...). Hiển thị công khai ở cuối trang.', 'fields' => ['title' => 'Tên nguồn', 'url' => 'https://...']],
    ];
@endphp
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-info-circle"></i> THÔNG TIN ĐIỂM ĐẾN</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tên điểm đến <span class="text-danger">*</span></label>
                    {{ Form::text('name', $value('name'), ['class' => 'form-control', 'maxlength' => 150, 'placeholder' => 'Ví dụ: Sung Sot Cave / Hang Sửng Sốt', 'autocomplete' => 'off']) }}
                    <small class="text-muted">Hiển thị trên thẻ, bản đồ, breadcrumb; là H1 nếu không nhập tiêu đề trang bên dưới.</small>
                </div>
                <div class="form-group">
                    <label>Tiêu đề trang (H1)</label>
                    {{ Form::text('heading', $value('heading'), ['class' => 'form-control', 'maxlength' => 160, 'placeholder' => 'Để trống = dùng tên điểm đến. Ví dụ: Sung Sot Cave (Surprise Cave): A Visitor\'s Guide', 'autocomplete' => 'off']) }}
                </div>
                <div class="form-group">
                    <label>Đường dẫn (slug)</label>
                    {{ Form::text('slug', $value('slug'), ['class' => 'form-control', 'maxlength' => 120, 'placeholder' => 'Để trống = tạo từ tên', 'autocomplete' => 'off']) }}
                    <small class="text-muted">Trang: /destinations/<b>slug</b> (EN) hoặc /vi/dia-diem-tham-quan/<b>slug</b> (VI). <span class="text-danger">Đổi slug của trang đã xuất bản sẽ làm hỏng liên kết cũ.</span></small>
                </div>
                <div class="form-group">
                    <label>Mô tả ngắn <span class="text-danger">*</span></label>
                    {{ Form::textarea('summary', $value('summary'), ['class' => 'form-control', 'rows' => 3, 'maxlength' => 600, 'placeholder' => '1–2 câu: điểm đến là gì, ở đâu, vì sao đáng ghé. Dùng cho thẻ trên trang danh sách.']) }}
                </div>
                <div class="form-group">
                    <label>Nội dung chi tiết <span class="text-danger">*</span></label>
                    {{ Form::textarea('content', $value('content'), ['class' => 'form-control', 'rows' => 14]) }}
                    <small class="text-muted">Dùng Heading 2 cho các mục (Tổng quan, Hoạt động, Cách đến, Khi đến nơi, Lưu ý tiếp cận, Thời tiết & mùa). Không ghi chú biên tập trong nội dung.</small>
                </div>
            </div>
        </div>

        @foreach ($repeaters as $name => $repeater)
            @php $rows = old($name, $obj->{$name} ?? []) ?: []; @endphp
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas {{ $repeater['icon'] }}"></i> {{ $repeater['title'] }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">{{ $repeater['help'] }}</p>
                    <div class="destination-repeater" data-name="{{ $name }}">
                        @foreach (array_values($rows) as $i => $row)
                            <div class="form-row repeater-row mb-2">
                                @foreach ($repeater['fields'] as $key => $placeholder)
                                    <div class="col">
                                        @if ($key === 'a')
                                            <textarea name="{{ $name }}[{{ $i }}][{{ $key }}]" class="form-control" rows="3" placeholder="{{ $placeholder }}">{{ $row[$key] ?? '' }}</textarea>
                                        @else
                                            <input type="text" name="{{ $name }}[{{ $i }}][{{ $key }}]" class="form-control" value="{{ $row[$key] ?? '' }}" placeholder="{{ $placeholder }}">
                                        @endif
                                    </div>
                                @endforeach
                                <div class="col-auto"><button type="button" class="btn btn-light repeater-remove" title="Xóa dòng"><i class="fas fa-times"></i></button></div>
                            </div>
                        @endforeach
                        <template>
                            <div class="form-row repeater-row mb-2">
                                @foreach ($repeater['fields'] as $key => $placeholder)
                                    <div class="col">
                                        @if ($key === 'a')
                                            <textarea data-key="{{ $key }}" class="form-control" rows="3" placeholder="{{ $placeholder }}"></textarea>
                                        @else
                                            <input type="text" data-key="{{ $key }}" class="form-control" placeholder="{{ $placeholder }}">
                                        @endif
                                    </div>
                                @endforeach
                                <div class="col-auto"><button type="button" class="btn btn-light repeater-remove" title="Xóa dòng"><i class="fas fa-times"></i></button></div>
                            </div>
                        </template>
                        <button type="button" class="btn btn-outline-secondary btn-sm repeater-add"><i class="fas fa-plus"></i> Thêm dòng</button>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-search"></i> SEO</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tiêu đề SEO</label>
                    {{ Form::text('seo_title', $value('seo_title'), ['class' => 'form-control', 'maxlength' => 65, 'placeholder' => 'Để trống = dùng tên điểm đến']) }}
                </div>
                <div class="form-group">
                    <label>Mô tả SEO</label>
                    {{ Form::text('seo_description', $value('seo_description'), ['class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'Để trống = dùng mô tả ngắn']) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-check-circle"></i> XUẤT BẢN & KIỂM DUYỆT</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Trạng thái <span class="text-danger">*</span></label>
                    {{ Form::select('status', config('backend.destinationStatus'), $value('status', 'draft'), ['class' => 'form-control']) }}
                    <small class="text-muted">Chỉ "Đã xuất bản" mới hiện trên website và sitemap.</small>
                </div>
                <div class="form-group">
                    <label>Ngày kiểm chứng thông tin</label>
                    {{ Form::date('fact_checked_at', $value('fact_checked_at'), ['class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    <label>Ghi chú biên tập (nội bộ)</label>
                    {{ Form::textarea('editor_notes', $value('editor_notes'), ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Những gì còn cần xác minh. Không bao giờ hiển thị trên website.']) }}
                </div>
                @isset($isLinked)
                    <p class="small mb-0 {{ $isLinked ? 'text-success' : 'text-warning' }}">
                        <i class="fas fa-language"></i>
                        {{ $isLinked ? 'Đã liên kết bản dịch.' : 'Chưa liên kết bản dịch EN/VI: hreflang và nút đổi ngôn ngữ sẽ không trỏ sang bản còn lại.' }}
                        <a href="{{ route('backend.translation.index', ['type' => 'destination']) }}">Liên kết bản dịch</a>
                    </p>
                @endisset
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-tag"></i> PHÂN LOẠI</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Vịnh <span class="text-danger">*</span></label>
                    {{ Form::select('bay', config('backend.destinationBay'), $value('bay'), ['class' => 'form-control', 'placeholder' => 'Chọn vịnh...']) }}
                </div>
                <div class="form-group">
                    <label>Loại điểm đến <span class="text-danger">*</span></label>
                    {{ Form::select('category', config('backend.destinationCategory'), $value('category'), ['class' => 'form-control', 'placeholder' => 'Chọn loại...']) }}
                </div>
                <div class="form-group">
                    <label>Hành trình Green Ruby ghé (dự kiến)</label>
                    <select name="itinerary_ids[]" class="form-control select2-destination" multiple>
                        @foreach ($itineraryOptions as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, array_map('intval', old('itinerary_ids', $itineraryIds))) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Để trống nếu điểm đến không nằm trong hành trình Green Ruby: trang sẽ ghi rõ là điểm tham quan khác trong vùng.</small>
                </div>
                <div class="form-group">
                    <label>Thứ tự</label>
                    {{ Form::number('ord', $value('ord'), ['class' => 'form-control', 'min' => 0]) }}
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-map-marker-alt"></i> VỊ TRÍ TRÊN BẢN ĐỒ</h6>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-6">
                        <label>Vĩ độ</label>
                        {{ Form::text('latitude', $value('latitude'), ['class' => 'form-control', 'placeholder' => '20.844753']) }}
                    </div>
                    <div class="form-group col-6">
                        <label>Kinh độ</label>
                        {{ Form::text('longitude', $value('longitude'), ['class' => 'form-control', 'placeholder' => '107.090432']) }}
                    </div>
                </div>
                <div class="form-group">
                    <label>Nguồn tọa độ</label>
                    {{ Form::text('coords_source', $value('coords_source'), ['class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'Ví dụ: Wikidata Q10765376']) }}
                </div>
                <div class="form-check">
                    {{ Form::checkbox('coords_verified', 1, (bool) $value('coords_verified', false), ['class' => 'form-check-input', 'id' => 'coords_verified']) }}
                    <label class="form-check-label" for="coords_verified">Tọa độ đã kiểm chứng (chỉ khi được chọn mới hiện trên bản đồ)</label>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-image"></i> ẢNH ĐẠI DIỆN</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    {{ Form::hidden('image_link', $value('image_link'), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink($value('image_link')), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                </div>
                <div class="form-group">
                    <label>Mô tả ảnh (alt)</label>
                    {{ Form::text('image_alt', $value('image_alt'), ['class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'Mô tả đúng nội dung ảnh']) }}
                </div>
                <div class="form-group">
                    <label>Tác giả / giấy phép ảnh</label>
                    {{ Form::text('image_credit', $value('image_credit'), ['class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'Ví dụ: Ảnh: Tên tác giả, CC BY 4.0, Wikimedia Commons']) }}
                    <small class="text-muted">Chỉ dùng ảnh thật của Green Ruby hoặc ảnh có giấy phép. Ảnh CC BY bắt buộc ghi tác giả.</small>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Lưu thông tin</button>
                <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.destination.index'), request()->route('languageCode') ? ['languageCode' => request()->route('languageCode')] : [])) }}" class="btn btn-light btn-block">
                    <i class="fas fa-arrow-left"></i> Hủy bỏ
                </a>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    @include('backend::shared.text-editor-script')
    <script type="text/javascript">
        $('[name="content"]').textEditor({ height: 520 });
        $('.select2-destination').select2({ width: '100%', placeholder: 'Chọn hành trình...' });

        // Repeater rows: new rows get the next free index so names stay unique.
        $(document).on('click', '.repeater-add', function () {
            const box = $(this).closest('.destination-repeater');
            const name = box.data('name');
            const index = Date.now();
            const row = $(box.find('template').html());
            row.find('[data-key]').each(function () {
                $(this).attr('name', name + '[' + index + '][' + $(this).data('key') + ']').removeAttr('data-key');
            });
            row.insertBefore($(this));
        });
        $(document).on('click', '.repeater-remove', function () {
            $(this).closest('.repeater-row').remove();
        });
    </script>
@endsection
