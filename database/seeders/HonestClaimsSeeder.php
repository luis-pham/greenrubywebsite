<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\BackEnd\Entities\AdConfig;
use Modules\BackEnd\Entities\AppPage;
use Modules\BackEnd\Entities\AppPageConfig;

class HonestClaimsSeeder extends Seeder
{
    private const LANG_VI = 1;
    private const LANG_EN = 2;

    public function run(): void
    {
        $this->clearFakeHomepageStats();
        $this->replaceFakeHomepageAwards();
        $this->clearUnverifiedAboutStats();
        $this->softenAboutPartnerCopy();
        $this->fixFooterDescriptions();
        $this->fixWebsiteDescriptions();

        $this->command?->info('HonestClaimsSeeder: homepage awards/stats, footer, and about copy updated.');
    }

    private function clearFakeHomepageStats(): void
    {
        foreach ([self::LANG_VI, self::LANG_EN] as $languageId) {
            foreach ([
                'homepage-guest-rating',
                'homepage-happy-guests',
                'homepage-would-recommend',
            ] as $key) {
                $this->updatePageConfig('homepage', $languageId, $key, '');
            }
        }
    }

    private function replaceFakeHomepageAwards(): void
    {
        $iconCup = '/2026/02/06/icon-award-cup.png';
        $iconStar = '/2026/02/06/icon-award-start.png';
        $iconLeaf = '/2026/02/06/icon-award-leaf.png';

        $enAwards = [
            ['title' => 'Green Globe · In Progress', 'link' => $iconCup, 'extension' => 'png', 'description' => ''],
            ['title' => 'Solar-Assisted Operations', 'link' => $iconStar, 'extension' => 'png', 'description' => ''],
            ['title' => 'Seawater Cooling System', 'link' => $iconLeaf, 'extension' => 'png', 'description' => ''],
        ];

        $viAwards = [
            ['id' => 1078, 'title' => 'Green Globe · Đang tiến hành', 'link' => $iconCup, 'thumbnail' => null, 'extension' => 'png', 'description' => '', 'is_360' => 0],
            ['id' => 1079, 'title' => 'Năng lượng mặt trời hỗ trợ', 'link' => $iconStar, 'thumbnail' => null, 'extension' => 'png', 'description' => '', 'is_360' => 0],
            ['id' => 1080, 'title' => 'Hệ thống làm mát nước biển', 'link' => $iconLeaf, 'thumbnail' => null, 'extension' => 'png', 'description' => '', 'is_360' => 0],
        ];

        $this->updatePageConfig('homepage', self::LANG_EN, 'homepage-award', json_encode($enAwards, JSON_UNESCAPED_UNICODE));
        $this->updatePageConfig('homepage', self::LANG_VI, 'homepage-award', json_encode($viAwards, JSON_UNESCAPED_UNICODE));
    }

    private function clearUnverifiedAboutStats(): void
    {
        foreach ([self::LANG_VI, self::LANG_EN] as $languageId) {
            foreach ([
                'about-us-statistic-wastewater-treated',
                'about-us-statistic-reduced-annually',
                'about-us-statistic-renewable-solar-power',
            ] as $key) {
                $this->updatePageConfig('about-us', $languageId, $key, '');
            }
        }
    }

    private function softenAboutPartnerCopy(): void
    {
        $this->updatePageConfig(
            'about-us',
            self::LANG_EN,
            'about-us-partner-description',
            'Working toward recognised sustainability standards for hospitality on Ha Long and Lan Ha Bay.'
        );
        $this->updatePageConfig(
            'about-us',
            self::LANG_VI,
            'about-us-partner-description',
            'Hướng tới các tiêu chuẩn bền vững được công nhận cho dịch vụ du thuyền tại Vịnh Hạ Long và Lan Hạ.'
        );
    }

    private function fixFooterDescriptions(): void
    {
        $this->updateAdConfig(
            'footer-description-en',
            'Eco-luxury cruises on Ha Long Bay and Lan Ha Bay — solar-assisted power and seawater cooling on board.'
        );
        $this->updateAdConfig(
            'footer-description-vi',
            'Du thuyền eco-luxury trên Vịnh Hạ Long và Lan Hạ — năng lượng mặt trời hỗ trợ và làm mát bằng nước biển trên tàu.'
        );
    }

    private function fixWebsiteDescriptions(): void
    {
        $this->updateAdConfig(
            'website-description-en',
            'Eco-luxury cruises on Ha Long Bay and the quieter Lan Ha Bay, Vietnam. Solar-assisted vessels with seawater cooling. Green Globe certification in progress.'
        );
        $this->updateAdConfig(
            'website-description-vi',
            'Du thuyền eco-luxury trên Vịnh Hạ Long và Lan Hạ yên tĩnh. Tàu dùng năng lượng mặt trời hỗ trợ và làm mát bằng nước biển. Đang trong quá trình chứng nhận Green Globe.'
        );
    }

    private function updatePageConfig(string $pageCode, int $languageId, string $key, string $value): void
    {
        $page = AppPage::where('code', $pageCode)->where('language_id', $languageId)->first();
        if (!$page) {
            return;
        }

        AppPageConfig::where('page_id', $page->id)->where('key', $key)->update(['value' => $value]);
    }

    private function updateAdConfig(string $key, string $value): void
    {
        AdConfig::where('key', $key)->update(['value' => $value]);
    }
}
