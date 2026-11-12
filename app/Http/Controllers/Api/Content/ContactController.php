<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\BackEnd\Helpers\Logging;
use Modules\FrontEnd\Http\Requests\Contact\ContactRequest;

/**
 * JSON twin of Modules\FrontEnd\Http\Controllers\ContactController@request:
 * same validation rules/messages (ContactRequest) and the same SendMail, sent to config('rsv.contact_notify_to').
 * Every message is also stored in contact_messages for the reservations desk.
 * API field "message" maps to the form field "request_content".
 */
class ContactController extends Controller
{
    use ResolvesContentLanguage;

    public function store(Request $request)
    {
        // Lenient: an unexpected language must never lose the guest's message (answered in English, logged).
        $language = $this->resolveLanguage($request, (string) ($request->input('lang') ?? $request->query('lang', 'en')), lenient: true);
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

        // Stored first (reservations desk, rsv.greenruby.cruises), then e-mailed: a mail failure no longer loses the message.
        $valid = $validator->validated();
        $id = null;
        try {
            $id = DB::table('contact_messages')->insertGetId([
                'name' => mb_substr((string) $valid['name'], 0, 191),
                'email' => $valid['email'] ?? null,
                'phone' => isset($valid['phone']) ? mb_substr((string) $valid['phone'], 0, 50) : null,
                'message' => (string) $valid['request_content'],
                'locale' => $language->code,
                'source' => 'contact_form',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Logging::logError('Contact: không lưu được tin nhắn: ' . $e->getMessage());
        }

        try {
            Mail::to(config('rsv.contact_notify_to') ?: config('mail.admin.address'))
                ->send(new SendMail(__('frontend::contact.subject'), $valid));
            if ($id) {
                DB::table('contact_messages')->where('id', $id)->update(['mail_status' => 'sent', 'updated_at' => now()]);
            }
        } catch (\Throwable $e) {
            Logging::logError($e->getMessage());
            if (!$id) {
                return response()->json(['success' => false, 'error' => 'send_failed'], 500);
            }
            DB::table('contact_messages')->where('id', $id)
                ->update(['mail_status' => 'failed', 'mail_error' => mb_substr($e->getMessage(), 0, 2000), 'updated_at' => now()]);
        }

        return response()->json(['success' => true], 201);
    }
}
