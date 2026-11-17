{{-- Shared by create/edit. $obj is null on create. --}}
@php
    $value = fn ($field, $default = null) => old($field, $obj->{$field} ?? $default);
    $dateValue = fn ($field) => ($v = $value($field)) ? substr((string) $v, 0, 10) : null;
    $publicUrl = $obj ? rtrim(config('app.url'), '/') . '/tuyen-dung/' . $obj->slug : null;
    $sections = [
        'intro' => ['Giới thiệu công việc', 'Vai trò của vị trí trong đội ngũ, làm việc với ai, ở đâu.'],
        'description' => ['Mô tả công việc', 'Các đầu việc chính (danh sách gạch đầu dòng).'],
        'requirements' => ['Yêu cầu công việc', 'Kinh nghiệm, kỹ năng, ngoại ngữ... chỉ ghi yêu cầu thật.'],
        'benefits' => ['Quyền lợi', 'Chỉ ghi quyền lợi đã được duyệt (lương, thưởng, bảo hiểm...). Để trống nếu chưa chốt.'],
    ];
@endphp
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-briefcase"></i> THÔNG TIN VỊ TRÍ</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tên vị trí <span class="text-danger">*</span></label>
                    {{ Form::text('title', $value('title'), ['class' => 'form-control', 'maxlength' => 150, 'placeholder' => 'Ví dụ: Chuyên viên Marketing', 'autocomplete' => 'off']) }}
                    <small class="text-muted">Là tiêu đề (H1) của trang và tên trên thẻ ở trang /tuyen-dung.</small>
                </div>
                <div class="form-group">
                    <label>Đường dẫn (slug)</label>
                    {{ Form::text('slug', $value('slug'), ['class' => 'form-control', 'maxlength' => 120, 'placeholder' => 'Để trống = tạo từ tên vị trí', 'autocomplete' => 'off']) }}
                    <small class="text-muted">Trang: /tuyen-dung/<b>slug</b>. <span class="text-danger">Đổi slug của vị trí đã đăng sẽ làm hỏng liên kết cũ.</span></small>
                </div>
                <div class="form-group">
                    <label>Tổng quan ngắn</label>
                    {{ Form::textarea('summary', $value('summary'), ['class' => 'form-control', 'rows' => 3, 'maxlength' => 600, 'placeholder' => '1–2 câu giới thiệu vị trí, hiển thị dưới tiêu đề và dùng làm mô tả SEO khi để trống ô Mô tả SEO.']) }}
                </div>
                @foreach ($sections as $name => [$label, $help])
                    <div class="form-group">
                        <label>{{ $label }}</label>
                        {{ Form::textarea($name, $value($name), ['class' => 'form-control job-editor', 'rows' => 8]) }}
                        <small class="text-muted">{{ $help }} Mục để trống sẽ không hiển thị.</small>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-search"></i> SEO</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tiêu đề SEO</label>
                    {{ Form::text('seo_title', $value('seo_title'), ['class' => 'form-control', 'maxlength' => 65, 'placeholder' => 'Để trống = "Tuyển dụng [Tên vị trí]"']) }}
                </div>
                <div class="form-group">
                    <label>Mô tả SEO</label>
                    {{ Form::text('seo_description', $value('seo_description'), ['class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'Để trống = dùng Tổng quan ngắn']) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-check-circle"></i> XUẤT BẢN</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Trạng thái <span class="text-danger">*</span></label>
                    {{ Form::select('status', config('backend.jobStatus'), $value('status', 'draft'), ['class' => 'form-control']) }}
                    <small class="text-muted">
                        "Đang tuyển": hiện trên /tuyen-dung và sitemap. "Đã đóng" (hoặc quá hạn nộp): trang vẫn mở nhưng báo đã đóng, không có nút ứng tuyển. "Bản nháp": không hiển thị.
                    </small>
                </div>
                <div class="form-group">
                    <label>Hạn nộp hồ sơ</label>
                    {{ Form::date('deadline', $dateValue('deadline'), ['class' => 'form-control']) }}
                    <small class="text-muted">Ngày cuối cùng nhận hồ sơ. Sau ngày này vị trí tự động đóng. Để trống = không có hạn.</small>
                </div>
                <div class="form-group">
                    <label>Ngày đăng</label>
                    {{ Form::date('posted_at', $dateValue('posted_at'), ['class' => 'form-control']) }}
                    <small class="text-muted">Để trống = ngày xuất bản lần đầu.</small>
                </div>
                @if ($obj && $obj->status !== 'draft')
                    <p class="small mb-0"><a href="{{ $publicUrl }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Xem trên website</a></p>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-tag"></i> PHÂN LOẠI</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Bộ phận <span class="text-danger">*</span></label>
                    {{ Form::text('department', $value('department'), ['class' => 'form-control', 'maxlength' => 100, 'placeholder' => 'Ví dụ: Marketing, Kinh doanh', 'autocomplete' => 'off']) }}
                </div>
                <div class="form-group">
                    <label>Hình thức làm việc</label>
                    {{ Form::select('employment_type', config('backend.jobEmploymentType'), $value('employment_type'), ['class' => 'form-control', 'placeholder' => 'Không hiển thị']) }}
                </div>
                <div class="form-group">
                    <label>Thứ tự</label>
                    {{ Form::number('ord', $value('ord'), ['class' => 'form-control', 'min' => 0]) }}
                    <small class="text-muted">Số nhỏ hiển thị trước. Có thể kéo thả trong danh sách.</small>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-map-marker-alt"></i> ĐỊA ĐIỂM LÀM VIỆC</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Địa điểm (hiển thị)</label>
                    {{ Form::text('location', $value('location'), ['class' => 'form-control', 'maxlength' => 150, 'placeholder' => 'Ví dụ: Tuần Châu, Hạ Long, Quảng Ninh', 'autocomplete' => 'off']) }}
                </div>
                <div class="form-row">
                    <div class="form-group col-6">
                        <label>Thành phố</label>
                        {{ Form::text('address_locality', $value('address_locality'), ['class' => 'form-control', 'maxlength' => 100, 'placeholder' => 'Hạ Long']) }}
                    </div>
                    <div class="form-group col-6">
                        <label>Tỉnh/thành</label>
                        {{ Form::text('address_region', $value('address_region'), ['class' => 'form-control', 'maxlength' => 100, 'placeholder' => 'Quảng Ninh']) }}
                    </div>
                </div>
                <small class="text-muted">Thành phố / tỉnh dùng cho Google Việc làm (dữ liệu có cấu trúc). Thiếu cả hai thì trang vẫn hiển thị nhưng không gửi dữ liệu JobPosting cho Google.</small>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fas fa-envelope"></i> NHẬN HỒ SƠ</h6>
            </div>
            <div class="card-body">
                <div class="form-group mb-0">
                    <label>Email nhận hồ sơ</label>
                    {{ Form::email('apply_email', $value('apply_email'), ['class' => 'form-control', 'maxlength' => 150, 'placeholder' => \App\Services\Content\CareerContentService::defaultEmail()]) }}
                    <small class="text-muted">Để trống = email tuyển dụng chung ("Email tuyển dụng" trong Cấu hình hệ thống).</small>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Lưu thông tin</button>
                <a href="{{ Utilities::getGoBackUrl(route('backend.job.index')) }}" class="btn btn-light btn-block">
                    <i class="fas fa-arrow-left"></i> Hủy bỏ
                </a>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    @include('backend::shared.text-editor-script')
    <script type="text/javascript">
        $('.job-editor').each(function () {
            $(this).textEditor({ height: 320 });
        });
    </script>
@endsection
