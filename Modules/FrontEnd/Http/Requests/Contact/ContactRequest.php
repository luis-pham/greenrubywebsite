<?php
namespace Modules\FrontEnd\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'phone' => 'required|string|max:20|regex:/^[0-9\+\-\s\(\)]+$/',
            'email'           => 'required|email|max:255',
            'request_content' => 'required|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => __('frontend::contact.validation.name.required'),
            'name.max'                 => __('frontend::contact.validation.name.max'),
            'phone.required'           => __('frontend::contact.validation.phone.required'),
            'phone.max'                => __('frontend::contact.validation.phone.max'),
            'phone.regex'    => __('frontend::contact.validation.phone.regex'),
            'email.required'           => __('frontend::contact.validation.email.required'),
            'email.email'              => __('frontend::contact.validation.email.email'),
            'email.max'                => __('frontend::contact.validation.email.max'),
            'request_content.required' => __('frontend::contact.validation.request_content.required'),
            'request_content.max'      => __('frontend::contact.validation.request_content.max'),
        ];
    }
}
