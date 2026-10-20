<?php

namespace Tests\Unit;

use App\Services\Ai\VietnameseEditor;
use PHPUnit\Framework\TestCase;

/**
 * Deterministic parts of the Vietnamese editorial pass: the translationese lint and the safe-edit rules.
 * The model's judgement itself is covered by the rubric suite (php artisan articles:ai-vi-eval).
 */
class VietnameseEditorTest extends TestCase
{
    /** @dataProvider flaggedSentences */
    public function testLintFlagsKnownTranslationese(string $text, string $label)
    {
        $found = implode(' | ', VietnameseEditor::lint($text));
        $this->assertStringContainsString($label, $found, "Not flagged: {$text}");
    }

    public function flaggedSentences(): array
    {
        return [
            ['Lan Hạ mạnh ở góc nhìn sát mặt nước.', 'mạnh ở/về'],
            ['Hạ Long mạnh về những điểm đã thành biểu tượng.', 'mạnh ở/về'],
            ['Green Ruby 1 và Green Ruby 2 là hai tàu chị em.', 'tàu chị em'],
            ['Buổi sáng bạn chuyển sang tàu ngày để vào vịnh.', 'tàu ngày'],
            ['Lan Hạ thắng về chèo kayak.', 'thắng về'],
            ['Nói gọn: chọn Lan Hạ nếu bạn thích yên tĩnh.', 'Nói gọn'],
            ['Câu trả lời thật là cả hai đều đẹp.', 'câu trả lời thật là'],
            ['Hãy coi đây là minh hoạ.', 'Hãy coi đây là'],
            ['Mùa đi mới là thứ làm thay đổi trải nghiệm.', 'mới là thứ'],
            ['Một vịnh với hai đêm đáng hơn.', 'đáng hơn'],
            ['Đây là chuyến đi chậm.', 'đi chậm'],
            ['Đi chậm, ngủ sớm.', 'đi chậm'],
            ['Cảnh đẹp — nước xanh.', 'gạch dài'],
            ['Không chỉ đẹp mà còn yên. Không chỉ rộng mà còn sạch.', 'không chỉ'],
            ['Trải nghiệm lý tưởng, nơi lý tưởng, mang đến sự yên bình, mang đến kỳ vĩ.', 'Từ quảng cáo'],
        ];
    }

    public function testLintFindsTableHeadingOnlyInTableHeaders()
    {
        $doc = ['title' => 'T', 'lead' => '', 'html' => '<table><tr><th>Thời gian trong ngày</th></tr></table>'];
        $this->assertStringContainsString('Thời gian trong ngày', implode(' ', VietnameseEditor::lint(VietnameseEditor::plain($doc))));

        $prose = ['title' => 'T', 'lead' => '', 'html' => '<p>Thời gian trong ngày ở Lan Hạ trôi chậm.</p>'];
        $this->assertStringNotContainsString('Thời gian trong ngày', implode(' ', VietnameseEditor::lint(VietnameseEditor::plain($prose))));
    }

    /** @dataProvider naturalSentences */
    public function testLintLeavesNaturalVietnameseAlone(string $text)
    {
        $this->assertSame([], VietnameseEditor::lint($text), $text);
    }

    public function naturalSentences(): array
    {
        return [
            ['Chọn vịnh Hạ Long hay Lan Hạ chủ yếu phụ thuộc vào việc bạn muốn xem gì nhiều hơn.'],
            ['Cảnh quan hai vịnh khá tương đồng, khác nhau ở mức độ đông đúc.'],
            ['Nếu thích chèo kayak, Lan Hạ có nhiều điểm hơn.'],
            ['Buổi sáng, tàu tham quan trong ngày đưa khách vào khu Ba Trái Đào.'],
            ['Ngày càng nhiều du khách chọn ở lại hai đêm.'],
            ['Gió mạnh nhất vào tháng 7.'],
            ['Tàu chạy chậm lại khi vào gần hang.'],
            ['Lan Hạ mang lại cảm giác gần gũi với thiên nhiên hơn.'],
        ];
    }

    public function testAppliesSafeEditExactlyOnce()
    {
        $doc = ['title' => 'T', 'html' => '<p>Lan Hạ mạnh ở góc nhìn sát mặt nước. Hạ Long đông hơn.</p>'];
        [$out, $applied, $flagged, $unapplied] = VietnameseEditor::applyEdits($doc, [
            $this->issue('Lan Hạ mạnh ở góc nhìn sát mặt nước.', 'Lan Hạ hợp với người thích ngắm cảnh từ mặt nước.'),
        ]);
        $this->assertSame('<p>Lan Hạ hợp với người thích ngắm cảnh từ mặt nước. Hạ Long đông hơn.</p>', $out['html']);
        $this->assertCount(1, $applied);
        $this->assertSame([], $flagged);
        $this->assertSame([], $unapplied);
    }

    public function testMeaningChangeIsFlaggedNotApplied()
    {
        $doc = ['html' => '<p>Tàu dự kiến hoạt động từ tháng 2/2027.</p>'];
        [$out, $applied, $flagged] = VietnameseEditor::applyEdits($doc, [
            $this->issue('Tàu dự kiến hoạt động', 'Tàu hoạt động', true),
        ]);
        $this->assertSame($doc, $out);
        $this->assertSame([], $applied);
        $this->assertCount(1, $flagged);
    }

    public function testChangedNumbersAreFlagged()
    {
        $doc = ['html' => '<p>Xe từ Hà Nội mất khoảng 2,5 giờ.</p>'];
        [$out, , $flagged] = VietnameseEditor::applyEdits($doc, [
            $this->issue('mất khoảng 2,5 giờ', 'mất khoảng 3 giờ'),
        ]);
        $this->assertSame($doc, $out);
        $this->assertStringContainsString('Số liệu', $flagged[0]['reason']);
    }

    public function testChangedLinksAndPlaceholdersAreFlagged()
    {
        $doc = ['html' => '<p>Xem <a href="/vi/du-thuyen">du thuyền</a>.</p><p>[IMAGE: hang Sáng Tối | alt: hang]</p>'];
        [$out, $applied, $flagged] = VietnameseEditor::applyEdits($doc, [
            $this->issue('<a href="/vi/du-thuyen">du thuyền</a>', '<a href="/vi/lich-trinh">du thuyền</a>'),
            $this->issue('<p>[IMAGE: hang Sáng Tối | alt: hang]</p>', ''),
            $this->issue('Xem <a href="/vi/du-thuyen">du thuyền</a>', 'Tham khảo <a href="/vi/du-thuyen">các du thuyền</a>'),
        ]);
        $this->assertCount(2, $flagged);
        $this->assertCount(1, $applied, 'anchor text may change when href stays');
        $this->assertStringContainsString('<a href="/vi/du-thuyen">các du thuyền</a>', $out['html']);
        $this->assertStringContainsString('[IMAGE: hang Sáng Tối', $out['html']);
    }

    public function testDeletionsAndLargeCutsAreFlagged()
    {
        $para = '<p>Nếu yêu thích không gian yên tĩnh, Lan Hạ là lựa chọn đáng cân nhắc. Với gia đình có người lớn tuổi, nên lưu ý những điểm phải leo nhiều bậc đá.</p>';
        $doc = ['html' => $para . '<p>Hạ Long đông hơn.</p>'];
        [$out, $applied, $flagged] = VietnameseEditor::applyEdits($doc, [
            $this->issue($para, ''),
            $this->issue('<p>Hạ Long đông hơn.</p>', '<p>Hạ Long đông khách hơn.</p>'),
        ]);
        $this->assertStringContainsString('người lớn tuổi', $out['html']);
        $this->assertCount(1, $flagged);
        $this->assertStringContainsString('Xoá', $flagged[0]['reason']);
        $this->assertCount(1, $applied, 'short rewording is still applied');

        [, , $flagged] = VietnameseEditor::applyEdits($doc, [$this->issue($para, '<p>Lan Hạ hợp với người thích yên tĩnh.</p>')]);
        $this->assertCount(1, $flagged, 'cutting a long paragraph by more than half is a content decision');
    }

    public function testMissingOrAmbiguousOriginalIsNotApplied()
    {
        $doc = ['html' => '<p>đi chậm. Rồi lại đi chậm.</p>'];
        [$out, $applied, , $unapplied] = VietnameseEditor::applyEdits($doc, [
            $this->issue('đi chậm', 'đi thong thả'),
            $this->issue('Không có câu này', 'Câu khác'),
        ]);
        $this->assertSame($doc, $out);
        $this->assertSame([], $applied);
        $this->assertCount(2, $unapplied);
    }

    public function testEditsTargetTheirOwnField()
    {
        $doc = ['title' => 'Nói gọn: Hạ Long hay Lan Hạ', 'lead' => 'Nói gọn: Hạ Long hay Lan Hạ', 'html' => ''];
        [$out] = VietnameseEditor::applyEdits($doc, [
            ['field' => 'title'] + $this->issue('Nói gọn: Hạ Long hay Lan Hạ', 'Hạ Long hay Lan Hạ'),
        ]);
        $this->assertSame('Hạ Long hay Lan Hạ', $out['title']);
        $this->assertSame('Nói gọn: Hạ Long hay Lan Hạ', $out['lead']);
    }

    public function testSchemaRequiresAllReviewFields()
    {
        $s = VietnameseEditor::schema();
        $this->assertSame(['field', 'original', 'category', 'why', 'revision', 'meaning_changed'], $s['properties']['issues']['items']['required']);
        $this->assertSame(VietnameseEditor::ACCEPTANCE, $s['properties']['acceptance']['required']);
    }

    private function issue(string $orig, string $rev, bool $meaning = false): array
    {
        return ['field' => 'html', 'original' => $orig, 'revision' => $rev, 'category' => 'translationese', 'why' => 'test', 'meaning_changed' => $meaning];
    }
}
