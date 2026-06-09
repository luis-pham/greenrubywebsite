<?php
return [
    'subject' => 'Contact Request',
    'hotline' => 'Hotline',
    'address' => 'Address',
    'explore-itineraries' => 'Explore itineraries',
    'our-services' => 'Our Services',
    'section-cover.title' => "Contact Us",
    'section-cover.description' => "Where luxury meets sustainability — sailing in harmony with nature",
    'section-contact.title' => "Connecting with the passion for the sea",
    'section-contact.description' => 'Always ready to listen',
    'section-contact.contact-form.comment' => "For any inquiries or technical support requests, please send them to our online mailbox. We are committed to providing optimal solutions for all your problems.",
    'section-contact.contact-form.name' => 'Name',
    'section-contact.contact-form.phone' => 'Phone',
    'section-contact.contact-form.email' => 'Email',
    'section-contact.contact-form.request-content' => 'Request content',
    'section-contact.contact-form.submit' => 'Submit a request',
    'section-contact.contact-form.placeholder' => 'Enter',
    'section-contact.contact-form.placeholder-name' => 'Enter name',
    'section-contact.contact-form.sending' => 'Sending',

    'validation' => [
        'name' => [
            'required' => 'Please enter your full name.',
            'max'      => 'Name must not exceed 255 characters.',
        ],
        'phone' => [
            'required' => 'Please enter your phone number.',
            'max'      => 'Phone number must not exceed 20 characters.',
            'regex'    => 'Please enter a valid phone number (digits, +, -, spaces, parentheses only).'
        ],
        'email' => [
            'required' => 'Please enter your email address.',
            'email'    => 'Please enter a valid email address.',
            'max'      => 'Email must not exceed 255 characters.',
        ],
        'request_content' => [
            'required' => 'Please enter your request content.',
            'max'      => 'Request content must not exceed 2000 characters.',
        ],
    ],
    'request-success' => 'Your request has been sent successfully!',
    'request-error'   => 'Something went wrong, please try again.',
];
