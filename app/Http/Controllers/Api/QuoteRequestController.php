<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'event_type' => 'nullable|string|max:32|in:meeting,business_seminar,wedding,birthday_party',
            'number' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:2000',
        ], [
            'contact_name.required' => 'Vui lòng nhập tên liên hệ.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['code'] = QuoteRequest::generateCode();
        $data['status'] = 'new';

        $quote = QuoteRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Gửi yêu cầu báo giá thành công. Chúng tôi sẽ liên hệ sớm.',
            'code' => $quote->code,
        ], 201);
    }
}
