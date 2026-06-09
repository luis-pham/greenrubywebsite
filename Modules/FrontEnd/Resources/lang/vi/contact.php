<?php
return [
    'subject' => 'Yêu cầu liên hệ',
    'hotline' => 'Đường dây nóng',
    'address' => 'Địa chỉ',
    'explore-itineraries' => 'Khám phá hành trình',
    'our-services' => 'Dịch vụ của chúng tôi',
    'section-cover.title' => "Liên hệ chúng tôi",
    'section-cover.description' => "Nơi sự sang trọng gặp gỡ bền vững — hành trình thuận hòa với thiên nhiên",
    'section-contact.title' => "Kết nối với niềm đam mê biển cả",
    'section-contact.description' => 'Luôn sẵn sàng lắng nghe',
    'section-contact.contact-form.comment' => "Đối với mọi thắc mắc hoặc yêu cầu hỗ trợ kỹ thuật, vui lòng gửi đến hộp thư trực tuyến của chúng tôi. Chúng tôi cam kết cung cấp giải pháp tối ưu cho mọi vấn đề của bạn.",
    'section-contact.contact-form.name' => 'Họ và tên',
    'section-contact.contact-form.phone' => 'Số điện thoại',
    'section-contact.contact-form.email' => 'Email',
    'section-contact.contact-form.request-content' => 'Nội dung yêu cầu',
    'section-contact.contact-form.submit' => 'Gửi yêu cầu',
    'section-contact.contact-form.placeholder' => 'Nhập',
    'section-contact.contact-form.placeholder-name' => 'Nhập tên',
    'section-contact.contact-form.sending' => 'Đang gửi',

    'validation' => [
        'name' => [
            'required' => 'Vui lòng nhập họ và tên.',
            'max'      => 'Họ và tên không được vượt quá 255 ký tự.',
        ],
        'phone' => [
            'required' => 'Vui lòng nhập số điện thoại.',
            'max'      => 'Số điện thoại không được vượt quá 20 ký tự.',
            'regex'    => 'Số điện thoại không hợp lệ (chỉ chấp nhận số, +, -, dấu cách và dấu ngoặc).'
        ],
        'email' => [
            'required' => 'Vui lòng nhập email.',
            'email'    => 'Email không hợp lệ.',
            'max'      => 'Email không được vượt quá 255 ký tự.',
        ],
        'request_content' => [
            'required' => 'Vui lòng nhập nội dung yêu cầu.',
            'max'      => 'Nội dung không được vượt quá 2000 ký tự.',
        ],
    ],
    'request-success' => 'Yêu cầu của bạn đã được gửi thành công!',
    'request-error'   => 'Đã có lỗi xảy ra, vui lòng thử lại.',
];
