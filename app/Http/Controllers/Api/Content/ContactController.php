<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\BackEnd\Helpers\Logging;
use Modules\FrontEnd\Http\Requests\Contact\ContactRequest;

/**
 * JSON twin of Modules\FrontEnd\Http\Controllers\ContactController@request:
 * same validation rules/messages (ContactRequest) and the same SendMail to config('mail.admin.address').
 * API field "message" maps to the form field "request_content".
 */
class ContactController extends Controller
{
    use ResolvesContentLanguage;

    public function store(Request $request)
    {
        $language = $this->resolveLanguage($request, (string) ($request->input('lang') ?? $request->query('lang', 'en')));
        App::setLocale($language->code);

        $data = [
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'request_content' => $request->input('message'),
        ];

        $formRequest = new ContactRequest();
        $validator = Validator::make($data, $formRequest->rules(), $formRequest->messages(), [
            'request_content' => 'message',
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field === 'request_content' ? 'message' : $field] = array_values($messages);
            }

            return response()->json(['success' => false, 'errors' => $errors], 422);
        }

        try {
            Mail::to(config('mail.admin.address'))
                ->send(new SendMail(__('frontend::contact.subject'), $validator->validated()));
        } catch (\Throwable $e) {
            Logging::logError($e->getMessage());

            return response()->json(['success' => false, 'error' => 'send_failed'], 500);
        }

        return response()->json(['success' => true], 201);
    }
}
