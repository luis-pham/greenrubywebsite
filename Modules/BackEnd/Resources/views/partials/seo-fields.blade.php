{{-- SEO title and description (phase 1E-c): cruise and itinerary forms had the columns but no form fields. --}}
<div class="form-group col-12 col-lg-6">
    <label class="col-form-label">Tiêu đề SEO <small class="text-muted">(≤ 65 ký tự; để trống = theo tên)</small></label>
    {{ Form::text('seo_title', $row->seo_title ?? null, ['class' => 'form-control', 'maxlength' => 65, 'autocomplete' => 'off', 'disabled' => $readOnly ?? false]) }}
</div>
<div class="form-group col-12 col-lg-6">
    <label class="col-form-label">Mô tả SEO <small class="text-muted">(≤ 255 ký tự)</small></label>
    {{ Form::textarea('seo_description', $row->seo_description ?? null, ['class' => 'form-control', 'maxlength' => 255, 'rows' => 2, 'disabled' => $readOnly ?? false]) }}
</div>
