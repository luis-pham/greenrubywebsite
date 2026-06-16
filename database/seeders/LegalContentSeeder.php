<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\BackEnd\Entities\AppPage;
use Modules\BackEnd\Entities\AppPageConfig;

class LegalContentSeeder extends Seeder
{
    private const LANG_VI = 1;
    private const LANG_EN = 2;

    public function run(): void
    {
        $this->seedEnglish();
        $this->seedVietnamese();

        $this->command?->info('LegalContentSeeder: legal hub content filled for EN and VI.');
    }

    private function seedEnglish(): void
    {
        $this->setLegalConfig(self::LANG_EN, 'legal-safety-policies-content', <<<'HTML'
<p>The safety of our guests and crew is our top priority on every voyage.</p>
<p><strong>Onboard safety</strong></p>
<ul>
<li>Life jackets and safety briefings are provided before departure.</li>
<li>Crew are trained in emergency procedures and first response.</li>
<li>Itineraries may change when weather or maritime authorities require it.</li>
</ul>
<p><strong>Guest responsibilities</strong></p>
<ul>
<li>Follow crew instructions during boarding, activities, and emergencies.</li>
<li>Supervise children near pools, balconies, and open decks.</li>
<li>Report health or safety concerns to the purser or duty officer immediately.</li>
</ul>
HTML);

        $this->setLegalConfig(self::LANG_EN, 'legal-privacy-policies-content', <<<'HTML'
<p>Green Ruby Cruises respects your privacy. This policy explains what we collect, why we collect it, and how you can contact us.</p>
<p><strong>Information we collect</strong></p>
<ul>
<li>Booking details: name, email, phone, passport or ID where required by port authorities.</li>
<li>Payment references processed through our approved payment partners.</li>
<li>Website usage data such as cookies and analytics, where permitted.</li>
</ul>
<p><strong>How we use information</strong></p>
<ul>
<li>To confirm bookings, provide cruise services, and respond to support requests.</li>
<li>To meet legal, immigration, and maritime reporting obligations.</li>
<li>To improve our website and guest experience with aggregated analytics.</li>
</ul>
<p><strong>Your rights</strong></p>
<p>You may request access, correction, or deletion of personal data where applicable law allows. Contact <a href="mailto:privacy@greenrubycruises.com">privacy@greenrubycruises.com</a>.</p>
HTML);

        $this->setLegalConfig(self::LANG_EN, 'legal-payment-methods-content', <<<'HTML'
<p>We support the following payment options for confirmed bookings:</p>
<p><strong>1. Bank transfer</strong></p>
<p>Account details are shared in your booking confirmation email after availability is confirmed.</p>
<p><strong>2. Card payment</strong></p>
<p>Visa and Mastercard are accepted through our secure payment partner where available.</p>
<p><strong>3. Cash on board</strong></p>
<p>Onboard purchases and incidental charges may be settled in VND or USD according to the published on-board tariff.</p>
HTML);
    }

    private function seedVietnamese(): void
    {
        $this->setLegalConfig(self::LANG_VI, 'legal-safety-policies-content', <<<'HTML'
<p>An toàn của khách và thủy thủ đoàn là ưu tiên hàng đầu trên mọi hành trình.</p>
<p><strong>An toàn trên tàu</strong></p>
<ul>
<li>Áo phao và hướng dẫn an toàn được cung cấp trước khi khởi hành.</li>
<li>Thủy thủ đoàn được đào tạo quy trình khẩn cấp và sơ cứu.</li>
<li>Lịch trình có thể thay đổi khi thời tiết hoặc cơ quan hàng hải yêu cầu.</li>
</ul>
<p><strong>Trách nhiệm của khách</strong></p>
<ul>
<li>Tuân thủ hướng dẫn của thủy thủ đoàn khi lên tàu, tham gia hoạt động và trong tình huống khẩn cấp.</li>
<li>Giám sát trẻ em gần hồ bơi, ban công và khu vực boong mở.</li>
<li>Báo ngay cho quản gia hoặc sĩ quan trực nếu có vấn đề sức khỏe hoặc an toàn.</li>
</ul>
HTML);

        $this->setLegalConfig(self::LANG_VI, 'legal-terms-and-conditions-content', <<<'HTML'
<p>Khi đặt dịch vụ qua website hoặc hệ thống của chúng tôi, khách hàng đồng ý với các điều khoản sau.</p>
<p><strong>1. Đặt chỗ</strong></p>
<p>Khách cung cấp thông tin chính xác và đầy đủ khi đặt chỗ. Đặt chỗ chỉ được xác nhận sau khi nhận xác nhận từ hệ thống của chúng tôi.</p>
<p><strong>2. Sử dụng dịch vụ</strong></p>
<p>Khách tuân thủ hướng dẫn an toàn và chỉ dẫn của nhân viên trong suốt hành trình. Không mang theo vật phẩm nguy hiểm hoặc bị cấm theo quy định hiện hành.</p>
<p><strong>3. Hủy và hoàn tiền</strong></p>
<p>Chính sách hủy áp dụng theo mức phí đã công bố tại thời điểm đặt chỗ. Yêu cầu hủy phải được gửi bằng văn bản qua email hoặc kênh hỗ trợ chính thức.</p>
HTML);

        $this->setLegalConfig(self::LANG_VI, 'legal-privacy-policies-content', <<<'HTML'
<p>Green Ruby Cruises tôn trọng quyền riêng tư của bạn. Chính sách này giải thích dữ liệu chúng tôi thu thập và cách bạn liên hệ với chúng tôi.</p>
<p><strong>Thông tin thu thập</strong></p>
<ul>
<li>Thông tin đặt chỗ: họ tên, email, điện thoại, hộ chiếu hoặc CMND khi cảng yêu cầu.</li>
<li>Tham chiếu thanh toán qua đối tác thanh toán được phê duyệt.</li>
<li>Dữ liệu sử dụng website như cookie và analytics, khi được phép.</li>
</ul>
<p><strong>Mục đích sử dụng</strong></p>
<ul>
<li>Xác nhận đặt chỗ, cung cấp dịch vụ du thuyền và hỗ trợ khách.</li>
<li>Đáp ứng nghĩa vụ pháp lý, hải quan và hàng hải.</li>
<li>Cải thiện website và trải nghiệm khách bằng dữ liệu tổng hợp.</li>
</ul>
<p><strong>Quyền của bạn</strong></p>
<p>Bạn có thể yêu cầu truy cập, chỉnh sửa hoặc xóa dữ liệu cá nhân theo quy định pháp luật. Liên hệ <a href="mailto:privacy@greenrubycruises.com">privacy@greenrubycruises.com</a>.</p>
HTML);

        $this->setLegalConfig(self::LANG_VI, 'legal-payment-methods-content', <<<'HTML'
<p>Chúng tôi hỗ trợ các phương thức thanh toán sau cho đặt chỗ đã xác nhận:</p>
<p><strong>1. Chuyển khoản ngân hàng</strong></p>
<p>Thông tin tài khoản được gửi trong email xác nhận sau khi xác nhận chỗ trống.</p>
<p><strong>2. Thẻ tín dụng</strong></p>
<p>Chấp nhận Visa và Mastercard qua đối tác thanh toán bảo mật khi khả dụng.</p>
<p><strong>3. Tiền mặt trên tàu</strong></p>
<p>Chi phí phát sinh trên tàu có thể thanh toán bằng VND hoặc USD theo bảng giá công bố.</p>
HTML);
    }

    private function setLegalConfig(int $languageId, string $key, string $value): void
    {
        $page = AppPage::where('code', 'legal')->where('language_id', $languageId)->first();
        if (!$page) {
            return;
        }

        $config = AppPageConfig::where('page_id', $page->id)->where('key', $key)->first();
        if (!$config) {
            return;
        }

        if (trim((string) $config->value) !== '') {
            return;
        }

        $config->value = $value;
        $config->save();
    }
}
