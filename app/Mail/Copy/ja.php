<?php

/*
 * Japanese copy for guest booking e-mails (i18n phase 2B). STATUS: DRAFT, PENDING_REVIEW.
 * Not sent unless 'ja' is in config i18n.mail.locales (env I18N_MAIL_LOCALES); see App\Services\Mail\BookingMailLocale.
 * Terminology: greenruby-web/docs/i18n-audit/phase-2a/japanese-terminology-glossary.md (です・ます, no superlatives).
 *
 * Every paragraph that states a payment obligation, a cancellation or a refund is an array with
 * 'review' => 'LEGAL_REVIEW_REQUIRED' (and a stable 'id'); the views wrap it in a marked block. No cancellation or
 * refund rule is written here: the e-mails link to the Booking Terms (English) instead.
 * Placeholders are the ones of App\Mail\BookingPaymentMail (:code, :name, :due, …) plus :days.
 */
return [
    'subjects' => [
        'confirmation' => 'ご予約内容のご確認（予約番号：:code）',
        'cancellation' => 'ご予約キャンセルのご確認（予約番号：:code）',
    ],

    'payment_methods' => [
        'stripe' => 'クレジットカード（Stripe）',
        'paypal' => 'PayPal',
        'sepay' => '銀行振込',
        'cash' => '予約のみ（オンラインでのお支払いなし）',
    ],

    'payment' => [
        'greeting' => ':name 様',
        'signature' => '予約担当チーム',
        'terms_link' => 'ご予約条件（英語）',
        'terms_note' => 'ご予約条件は現在、英語版のみご用意しております。',

        'payment_link' => [
            'subject' => 'ご予約手続きのご案内（予約番号：:code）',
            'body' => [
                'このたびはGreen Ruby Cruisesをお選びいただき、誠にありがとうございます。ご予約を確定していただくためのお手続きの準備が整いました。',
                ['id' => 'payment_link.terms_and_payment', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => '下記の安全なリンクから、ご予約内容をご確認のうえ、ご予約条件に同意し、お支払いをお済ませください。リンクの有効期限は:expiresです。'],
            ],
            'facts' => [['予約番号', ':code'], ['行程', ':voyage'], ['出発日', ':departure'], ['合計金額', ':total']],
            'button' => '確認してお支払い',
            'terms' => 'payment',
        ],
        'balance_request' => [
            'subject' => '残金のお支払いのご案内（予約番号：:code）',
            'body' => [
                '予約金のお支払いをいただき、誠にありがとうございます。クルーズ代金の残金のお支払い時期となりましたので、ご案内いたします。',
                ['id' => 'balance_request.due', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => ':due（ご出発の:days日前）までに、下記の安全なリンクから残金をお支払いください。'],
            ],
            'facts' => [['予約番号', ':code'], ['行程', ':voyage'], ['出発日', ':departure'], ['合計金額', ':total'], ['お支払い済み', ':paid'], ['残金', ':balance'], ['お支払い期日', ':due']],
            'button' => '残金を支払う',
            'terms' => 'payment',
        ],
        'balance_reminder_1' => [
            'subject' => '残金のお支払い期日のお知らせ（予約番号：:code）',
            'body' => [
                ['id' => 'balance_reminder_1.due', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => 'クルーズ代金の残金:balanceのお支払い期日は:dueです。お手続きをお願いいたします。'],
            ],
            'facts' => [['予約番号', ':code'], ['行程', ':voyage'], ['出発日', ':departure'], ['残金', ':balance'], ['お支払い期日', ':due']],
            'button' => '残金を支払う',
        ],
        'balance_reminder_2' => [
            'subject' => '【再度のお知らせ】残金のお支払い期日（予約番号：:code）',
            'body' => [
                ['id' => 'balance_reminder_2.due', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => 'クルーズ代金の残金:balanceのお支払いを、まだ確認できておりません。お支払い期日は:dueです。'],
                '本メールと行き違いでお支払いいただいている場合は、ご容赦ください。銀行振込の場合、着金の確認までにお時間がかかることがあります。',
            ],
            'facts' => [['予約番号', ':code'], ['行程', ':voyage'], ['出発日', ':departure'], ['残金', ':balance'], ['お支払い期日', ':due']],
            'button' => '残金を支払う',
        ],
        'overdue_notice' => [
            'subject' => '【重要】残金のお支払い期日経過のお知らせ（予約番号：:code）',
            'body' => [
                ['id' => 'overdue_notice.formal_notice', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => 'ご予約の残金:balanceは:dueがお支払い期日でしたが、現時点でお支払いを確認できておりません。本メールにて正式にお知らせいたします。'],
                ['id' => 'overdue_notice.cure_deadline', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => ':deadlineまでに、下記のリンクからお支払いをお済ませください。'],
                ['id' => 'overdue_notice.consequence', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => 'この期限までにお支払いを確認できない場合、ご予約条件（英語）第4.4項に基づき、ご予約をキャンセルさせていただくことがあります。その場合のお取り扱いは、ご予約条件に定めるとおりです。'],
                'すでにお支払い済みの場合や、お支払いについてご不明な点がある場合は、期限までにご連絡ください。',
            ],
            'facts' => [['予約番号', ':code'], ['行程', ':voyage'], ['出発日', ':departure'], ['残金', ':balance'], ['お支払い期限', ':deadline']],
            'button' => '残金を支払う',
            'terms' => 'payment',
        ],
        'refund_processed' => [
            'subject' => '返金手続き完了のお知らせ（予約番号：:code）',
            'body' => [
                ['id' => 'refund_processed.refund', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => 'ご予約について、:amountの返金手続きを、お支払い時と同じ方法で行いました。'],
                ['id' => 'refund_processed.timing', 'review' => 'LEGAL_REVIEW_REQUIRED',
                    'text' => 'ご返金が反映されるまでの期間は、ご利用の金融機関または決済サービスによって異なります。'],
            ],
            'facts' => [['予約番号', ':code'], ['行程', ':voyage'], ['出発日', ':departure'], ['返金額', ':amount']],
            'terms' => 'refunds',
        ],
        // cure_period_ended is a staff-only message (always Vietnamese): no Japanese version.
    ],
];
