@extends('frontend::layouts.master')
@php
    $languageCode = Route::current()->parameter('languageCode');

    $itineraryUrl = route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]);
    $serviceUrl = route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.service.index'), ['languageCode' => $languageCode]);
@endphp
@section('content')
    <div id="contact">
        <section class="section-cover contact-hero position-relative">
            <svg class="contact-hero-topo" viewBox="0 0 1440 300" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="60" rx="320" ry="180" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="60" rx="240" ry="130" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="260" rx="280" ry="160" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="260" rx="200" ry="110" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="container hero-content">
                <div class="main-info mx-auto text-white text-center">
                    <p class="section-eyebrow section-eyebrow--gold">Contact Us</p>
                    <h1 class="title font-heading">Let's Plan Your <em>Perfect Voyage.</em></h1>
                    <p class="description">Our team is ready to help you find the right itinerary, cabin, and date for your Green Ruby experience.</p>
                    <div class="list-button d-flex align-items-center">
                        <div class="item">
                            <a href="{{ $itineraryUrl }}" class="btn-rounded btn-warning">{{ __('frontend::contact.explore-itineraries') }}</a>
                        </div>
                        <div class="item">
                            <a href="{{ $serviceUrl }}" class="btn-rounded btn-success">{{ __('frontend::contact.our-services') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-contact bg">
            <div class="container-fluid px-0">
                <div class="container contact-main-grid">
                    <div class="main-info contact-left">
                        <p class="section-eyebrow section-eyebrow--gold text-left">Get in Touch</p>
                        <h2 class="contact-left-title">We're Here <em>to Help.</em></h2>
                        <p class="contact-left-tagline">Questions about itineraries, cabin availability, or special requests — reach us directly.</p>
                        <div class="contact">
                            <div class="item">
                                <div class="contact-item-icon"><i class="fas fa-phone"></i></div>
                                <div class="contact-item-body">
                                    <span class="contact-item-label">{{ __('frontend::contact.hotline') }}</span>
                                    <span class="contact-item-value">{{ $config['hotline'] ?? '' }}</span>
                                </div>
                            </div>
                            <div class="item">
                                <div class="contact-item-icon"><i class="fa-brands fa-whatsapp"></i></div>
                                <div class="contact-item-body">
                                    <span class="contact-item-label">Whatsapp</span>
                                    <span class="contact-item-value">{{ $config['whatsapp'] ?? '' }}</span>
                                </div>
                            </div>
                            <div class="item">
                                <div class="contact-item-icon"><i class="fas fa-envelope"></i></div>
                                <div class="contact-item-body">
                                    <span class="contact-item-label">Email</span>
                                    <span class="contact-item-value">{{ $config['email'] ?? '' }}</span>
                                </div>
                            </div>
                            <div class="item">
                                <div class="contact-item-icon"><i class="fas fa-location-dot"></i></div>
                                <div class="contact-item-body">
                                    <span class="contact-item-label">{{ __('frontend::contact.address') }}</span>
                                    <span class="contact-item-value">{{ $config['address'] ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-contact">
                        <div class="contact-form-header">
                            <h3 class="contact-form-title">Send Us a Message</h3>
                            <p class="contact-form-sub">We typically respond within 2 hours during business hours.</p>
                        </div>
                        {!!
                            Form::open([
                                'id' => 'frm',
                                'url' => route(Utilities::getRouteName('frontend.contact.request'), ['languageCode' => $languageCode]),
                                'method' => 'POST',
                                'class' => 'row contact-form',
                            ])
                        !!}
                            <div class="col-12 col-md-6 form-group mb-0">
                                <label class="col-form-label contact-form-label">{{ __('frontend::contact.section-contact.contact-form.name') }} <span class="text-danger">*</span></label>
                                {!!
                                    Form::text(
                                        'name',
                                        old('name') ?? null,
                                        [
                                            'class' => 'form-control contact-form-input' . ($errors->has('name') ? ' is-invalid' : ''),
                                            'placeholder' => __('frontend::contact.section-contact.contact-form.placeholder-name'),
                                        ]
                                    )
                                !!}
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6 form-group mb-0">
                                <label class="col-form-label contact-form-label">{{ __('frontend::contact.section-contact.contact-form.phone') }} <span class="text-danger">*</span></label>
                                {!!
                                    Form::text(
                                        'phone',
                                        old('phone'),
                                        [
                                            'class' => 'form-control contact-form-input' . ($errors->has('phone') ? ' is-invalid' : ''),
                                            'placeholder' => __('frontend::contact.section-contact.contact-form.placeholder'),
                                        ]
                                    )
                                !!}
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 form-group mb-0">
                                <label class="col-form-label contact-form-label">{{ __('frontend::contact.section-contact.contact-form.email') }} <span class="text-danger">*</span></label>
                                {!!
                                    Form::text(
                                        'email',
                                        old('email') ?? null,
                                        [
                                            'class' => 'form-control contact-form-input' . ($errors->has('email') ? ' is-invalid' : ''),
                                            'placeholder' => __('frontend::contact.section-contact.contact-form.placeholder'),
                                        ]
                                    )
                                !!}
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 form-group mb-0">
                                <label class="col-form-label contact-form-label">I'm interested in</label>
                                <input
                                    type="text"
                                    name="interest"
                                    class="form-control contact-form-input"
                                    placeholder="e.g. 2D1N Ha Long Bay, Opera Suite..."
                                />
                            </div>
                            <div class="col-12 form-group mb-0">
                                <label class="col-form-label contact-form-label">{{ __('frontend::contact.section-contact.contact-form.request-content') }} <span class="text-danger">*</span></label>
                                {!!
                                    Form::textarea(
                                        'request_content',
                                        old('request_content') ?? null,
                                        [
                                            'class' => 'form-control contact-form-input contact-form-textarea' . ($errors->has('request_content') ? ' is-invalid' : ''),
                                            'placeholder' => __('frontend::contact.section-contact.contact-form.placeholder'),
                                            'rows' => 2,
                                        ]
                                    )
                                !!}
                                @error('request_content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <button class="btn btn-warning btn-submit" type="submit">Send Message →</button>
                                <p class="contact-form-note">We respect your privacy. No spam, ever.</p>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.structured-data-organization', [
        'url' => route(Utilities::getRouteName('frontend.contact.index'), ['languageCode' => $languageCode]),
    ])
    <script type="text/javascript" defer>
        $(document).ready(function(){
            function preApiCall(){
                const $btn = $('.btn-submit');
                $btn.addClass('disabled');
                $btn.data('original-html', $btn.html());
                $btn.html(`<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>{{__('frontend::contact.section-contact.contact-form.sending')}}`);
            }

            function postApiCall(){
                const $btn = $('.btn-submit');
                $btn.removeClass('disabled');
                $btn.html($btn.data('original-html'));
            }

            $('#frm').on('submit',function(e){
                e.preventDefault();

                let data  = Object.fromEntries(new FormData(this));

                preApiCall();
                $.ajax({
                    url: "{{route(Utilities::getRouteName('frontend.contact.request'),['languageCode' => $languageCode])}}",
                    method: 'POST',
                    data: data,
                    headers: { 'Accept': 'application/json' },
                    success: function(res){
                        if(res.status === 'success'){
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                confirmButtonText: 'OK'
                            });
                            $('#frm')[0].reset();
                            $('.form-control').removeClass('is-invalid');
                            $('.invalid-feedback').remove();
                        }
                    },
                    error: function(xhr){
                        const res = xhr.responseJSON || {};
                        const errors = res.errors || {};

                        $('.form-control').removeClass('is-invalid');
                        $('.invalid-feedback').remove();

                        if (Object.keys(errors).length > 0) {
                            $.each(errors, function(field, messages) {
                                const $input = $('[name="' + field + '"]');
                                if ($input.length) {
                                    $input.addClass('is-invalid');
                                    const $feedback = $('<div class="invalid-feedback d-block"></div>')
                                        .text(messages[0]);
                                    $input.after($feedback);
                                }
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message || 'Something went wrong',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    complete: function(){
                        postApiCall()
                    }
                })
            })
        })
    </script>
@endpush
