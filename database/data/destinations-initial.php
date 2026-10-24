<?php

/*
| First eight destinations for `php artisan destinations:import`. Each entry is one EN/VI pair that is
| linked in content_translations. Itinerary ids are the production ids (EN 1-4, VI 5/7/8/9):
|   Lan Ha Immersion 3D2N = EN 1 / VI 7, Lan Ha Escape 2D1N = EN 2 / VI 5,
|   Halong Immersion 3D2N = EN 3 / VI 8, Halong Escape 2D1N = EN 4 / VI 9.
| Facts were checked on 2026-10-24 against the sources listed per entry; anything that could not be
| confirmed was left out of the copy and is listed in editor_notes (never published).
*/

$unesco = ['title' => 'UNESCO World Heritage Centre: Ha Long Bay – Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'];
$catBaMab = ['title' => 'UNESCO Man and the Biosphere: Cat Ba Biosphere Reserve', 'url' => 'https://www.unesco.org/en/mab/cat-ba'];

return [
    [
        'bay' => 'ha-long',
        'category' => 'cave',
        'ord' => 1,
        'latitude' => 20.844753,
        'longitude' => 107.090432,
        'coords_verified' => true,
        'coords_source' => 'Wikidata Q10765376 (20.84441, 107.09144) and Vietnamese Wikipedia (20.844753, 107.090432) agree',
        'image' => ['file' => 'hang-sung-sot-cave-ha-long-bay.jpg', 'link' => '/2026/10/24/hang-sung-sot-cave-ha-long-bay.jpg'],
        'image_credit' => 'Photo: Jakub Hałun, CC BY 4.0, via Wikimedia Commons',
        'itineraries' => ['en' => [3, 4], 'vi' => [8, 9]],
        'sources' => [
            $unesco,
            ['title' => 'Wikidata: Sung Sot Cave (coordinates)', 'url' => 'https://www.wikidata.org/wiki/Q10765376'],
            ['title' => 'Wikipedia (Vietnamese): Hang Sửng Sốt', 'url' => 'https://vi.wikipedia.org/wiki/Hang_S%E1%BB%ADng_S%E1%BB%91t'],
            ['title' => 'Photo: Sung Sot Cave, Jakub Hałun (Wikimedia Commons)', 'url' => 'https://commons.wikimedia.org/wiki/File:Sung_Sot_Cave,_Ha_Long_Bay,_Vietnam,_20240128_1556_3854.jpg'],
        ],
        'editor_notes' => "EN and VI updated 2026-10-24 from the editorial briefs; opening hours from the Ha Long Bay Management Board 'Visiting time' page (updated 2024-11-06): 7h30-17h30 (16/4-31/10), 7h30-17h00 (1/11-15/4). Not published: exact number of steps, cave size, ticket prices. The 1901 French naming story is on vi.wikipedia only; left out until confirmed by the Management Board.",
        'en' => [
            'slug' => 'sung-sot-cave',
            'name' => 'Sung Sot Cave',
            'heading' => 'Sung Sot Cave (Surprise Cave): A Visitor\'s Guide',
            'image_alt' => 'Lit stalactites and the high ceiling of the main chamber in Sung Sot Cave, Ha Long Bay',
            'seo_title' => 'Sung Sot Cave (Surprise Cave), Ha Long Bay: Visitor Guide',
            'seo_description' => 'Sung Sot Cave (Surprise Cave) on Bo Hon Island, Ha Long Bay: opening hours, how to get there, how long a visit takes, the climb, tickets and nearby sights.',
            'summary' => 'Sung Sot Cave, also known as Surprise Cave, is one of the best-known attractions in Ha Long Bay. Located on Bo Hon Island, it features two large limestone chambers filled with stalactites and stalagmites. Reaching the entrance involves a climb, but visitors are rewarded with impressive cave formations and views over the bay.',
            'sources' => [
                ['title' => 'Ha Long Bay Management Board — Visitor Opening Hours', 'url' => 'https://halongbay.com.vn/en/p/71-thoi-gian-don-tra-khach-ve-cang-ben'],
                ['title' => 'UNESCO World Heritage Centre — Ha Long Bay–Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'Wikidata — Sung Sot Cave', 'url' => 'https://www.wikidata.org/wiki/Q10765376'],
            ],
            'content' => <<<'HTML'
<p><em>Opening hours and visitor arrangements may change. Confirm the latest information before travelling.</em></p>
<h2>Where is Sung Sot Cave?</h2>
<p>Sung Sot Cave is located on Bo Hon Island, in the central area of Ha Long Bay. The island is also home to <a href="/destinations/luon-cave">Luon Cave</a>, while <a href="/destinations/ti-top-island">Ti Top Island</a> is nearby.</p>
<p>These three attractions are commonly included on the same sightseeing route, making Sung Sot Cave a popular stop on both day trips and overnight cruises.</p>
<h2>What makes Sung Sot Cave worth visiting?</h2>
<p>The name <em>Sung Sot</em> means “surprise” in Vietnamese, a reference to the unexpectedly large spaces hidden behind the cave’s relatively small entrance.</p>
<p>The cave has two main chambers. The first introduces visitors to its limestone formations, while the second opens into a much larger space with a high ceiling and striking stalactites and stalagmites.</p>
<p>Walkways and lighting make it possible to explore the chambers without specialist equipment. Guides often point out rock formations associated with local legends.</p>
<p>The climb also brings you to an elevated viewpoint overlooking part of the bay.</p>
<h2>How do you get to Sung Sot Cave?</h2>
<p>Sung Sot Cave is accessible by boat. Most visitors arrive as part of a Ha Long Bay sightseeing tour or an overnight cruise.</p>
<p>Boats stop at the island’s landing area, where a stone stairway leads up towards the entrance. Inside, visitors follow a designated walking route through the chambers before returning towards the pier.</p>
<p>You cannot reach the cave directly by road.</p>
<h2>How long does a visit take?</h2>
<p>Allow approximately <strong>45–60 minutes</strong> for a typical visit, although the actual time depends on your walking pace, the number of visitors and the schedule of your tour.</p>
<p>If you enjoy photography or prefer to take the stairs slowly, allow some additional time where your itinerary permits.</p>
<h2>Is Sung Sot Cave difficult to visit?</h2>
<p>The main challenge is the stairway leading to the cave, followed by steps and uneven surfaces along the walking route.</p>
<ul>
<li>Wear comfortable shoes with good grip.</li>
<li>Bring drinking water, especially during warmer months.</li>
<li>Take care on damp steps and walkways.</li>
<li>Supervise young children near stairways and railings.</li>
<li>Ask your cruise operator about accessibility if anyone in your group has difficulty walking.</li>
</ul>
<p>The route is not wheelchair accessible. Travellers with limited mobility should discuss their needs with the tour operator before booking.</p>
<h2>When is the best time to visit?</h2>
<p>Sung Sot Cave is a popular attraction throughout the year. The busiest periods often coincide with groups arriving on scheduled sightseeing tours.</p>
<p>Early or later visits may be less crowded, depending on the day’s boat schedules.</p>
<p>During summer, conditions around the island can be hot and humid. In winter, the climb may feel more comfortable, although mist and rain can affect the views outside.</p>
<p>Rain does not necessarily prevent a cave visit, but wet steps require extra care. During severe weather, boat operations may be suspended by local authorities.</p>
<h2>Tickets and entrance fees</h2>
<p>Sung Sot Cave is part of an established Ha Long Bay sightseeing route. Admission arrangements generally depend on the sightseeing ticket and tour package you choose.</p>
<p>For visitors booking an organised cruise, entrance fees for scheduled attractions are often included in the fare.</p>
<p>Check your booking confirmation to confirm which fees and activities are covered.</p>
<h2>Visiting Sung Sot Cave with Green Ruby Cruises</h2>
<p>Sung Sot Cave is planned as a sightseeing stop on selected <a href="/cruise/green-ruby-1-3.html"><strong>Green Ruby 1</strong></a> itineraries in Ha Long Bay.</p>
<p>Green Ruby 1 is being prepared for overnight journeys that combine well-known attractions with time to relax on board. Sung Sot Cave is one of the destinations featured in its proposed sightseeing programme.</p>
<p>The final route and visit times will depend on the confirmed itinerary, weather and local operating conditions.</p>
<p><a href="/itinerary">Explore the planned Green Ruby 1 itineraries</a> to see how Sung Sot Cave fits into your cruise.</p>
<h2>Nearby attractions</h2>
<h3><a href="/destinations/luon-cave">Luon Cave</a></h3>
<p>Located on the same island, Luon Cave offers a different experience, with visitors exploring a sheltered lagoon by kayak or bamboo boat.</p>
<h3><a href="/destinations/ti-top-island">Ti Top Island</a></h3>
<p>Known for its beach and hillside viewpoint, Ti Top Island is another popular stop on Ha Long Bay sightseeing routes.</p>
<p>These attractions can complement a visit to Sung Sot Cave, although the stops included depend on your chosen cruise.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Bo Hon Island, Ha Long Bay, Vietnam'],
                ['label' => 'Also known as', 'value' => 'Surprise Cave'],
                ['label' => 'Getting there', 'value' => 'By sightseeing boat or cruise'],
                ['label' => 'Typical visit', 'value' => 'Around 45–60 minutes'],
                ['label' => 'Walking difficulty', 'value' => 'Moderate, with steps and uneven surfaces'],
                ['label' => 'Opening hours', 'value' => '7:30 AM–5:30 PM (16 April–31 October); 7:30 AM–5:00 PM (1 November–15 April). May change: confirm before travelling.'],
                ['label' => 'Entrance fees', 'value' => 'Usually covered by the sightseeing route ticket or included in an organised cruise package'],
            ],
            'faq' => [
                ['q' => 'How long does a visit to Sung Sot Cave take?', 'a' => 'Most visits take around 45–60 minutes, including the walk through the chambers. Allow additional time if your group moves slowly or the site is busy.'],
                ['q' => 'Is Sung Sot Cave suitable for older travellers or young children?', 'a' => 'Many visitors can enjoy the cave, but reaching the entrance involves a climb and the walking route includes steps. Families with young children and travellers with limited mobility should consider these conditions before visiting.'],
                ['q' => 'Is Sung Sot Cave crowded?', 'a' => 'It can be, especially when several sightseeing groups arrive at similar times. The number of visitors varies by season, day and tour schedule.'],
                ['q' => 'Can you visit Sung Sot Cave without a cruise?', 'a' => 'You need a boat to reach Bo Hon Island. Visitors can join a licensed sightseeing boat or book a cruise itinerary that includes the cave.'],
                ['q' => 'Do all Green Ruby cruises visit Sung Sot Cave?', 'a' => 'No. Sung Sot Cave is a planned stop on selected Green Ruby 1 itineraries in Ha Long Bay. Check the individual itinerary for the proposed attractions and confirmed schedule.'],
            ],
        ],
        'vi' => [
            'slug' => 'hang-sung-sot',
            'name' => 'Hang Sửng Sốt',
            'heading' => 'Hang Sửng Sốt (Hạ Long): Kinh nghiệm tham quan',
            'image_alt' => 'Thạch nhũ được chiếu sáng dưới trần cao của khoang chính hang Sửng Sốt, vịnh Hạ Long',
            'image_credit' => 'Ảnh: Jakub Hałun, CC BY 4.0, qua Wikimedia Commons',
            'seo_title' => 'Hang Sửng Sốt, vịnh Hạ Long: kinh nghiệm tham quan',
            'seo_description' => 'Hang Sửng Sốt trên đảo Bồ Hòn, vịnh Hạ Long: giờ tham quan, cách đến, mất bao lâu, đường lên hang, vé tham quan và các điểm gần đó.',
            'summary' => 'Hang Sửng Sốt là một trong những hang động nổi tiếng nhất vịnh Hạ Long, nằm trên đảo Bồ Hòn. Phía sau cửa hang không quá lớn là hai khoang đá vôi rộng, có nhiều thạch nhũ với hình dáng độc đáo. Để đến cửa hang, du khách cần đi bộ lên một đoạn cầu thang đá từ bến thuyền.',
            'sources' => [
                ['title' => 'Ban Quản lý vịnh Hạ Long – Thời gian đón khách tham quan', 'url' => 'https://halongbay.com.vn/p/71-thoi-gian-don-tra-khach-ve-cang-ben'],
                ['title' => 'UNESCO World Heritage Centre – Ha Long Bay – Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'Wikidata – Sung Sot Cave', 'url' => 'https://www.wikidata.org/wiki/Q10765376'],
            ],
            'content' => <<<'HTML'
<p><em>Giờ mở cửa và quy định tham quan có thể thay đổi. Bạn nên kiểm tra thông tin trước chuyến đi.</em></p>
<h2>Hang Sửng Sốt nằm ở đâu?</h2>
<p>Hang Sửng Sốt nằm trên đảo Bồ Hòn, thuộc khu vực trung tâm vịnh Hạ Long. Hang nằm ở vị trí cao hơn mặt biển nên du khách phải leo một đoạn cầu thang đá để lên cửa hang.</p>
<p>Cùng trên đảo Bồ Hòn còn có <a href="/vi/dia-diem-tham-quan/hang-luon">hang Luồn</a>, trong khi <a href="/vi/dia-diem-tham-quan/dao-ti-top">đảo Ti Tốp</a> nằm không xa. Ba địa điểm này thường xuất hiện trong các chương trình tham quan vịnh Hạ Long, kết hợp khám phá hang động, chèo kayak và ngắm cảnh từ trên cao.</p>
<h2>Hang Sửng Sốt có gì đặc biệt?</h2>
<p>Điểm thú vị của hang Sửng Sốt nằm ở sự tương phản giữa cửa hang và không gian bên trong. Sau khi đi qua lối vào tương đối nhỏ, du khách sẽ đến hai khoang hang rộng với nhiều khối thạch nhũ được hình thành qua thời gian dài.</p>
<p>Khoang đầu tiên có không gian thấp hơn, với nhiều nhũ đá trên trần và vách. Đi sâu vào bên trong, khoang thứ hai mở ra rộng và cao hơn, tạo nên khung cảnh đặc trưng của hang Sửng Sốt.</p>
<p>Lối đi được bố trí qua các khoang để du khách quan sát những khối thạch nhũ, măng đá với hình dáng khác nhau. Một số hình thù gắn với các câu chuyện dân gian và thường được hướng dẫn viên giới thiệu trong chuyến tham quan.</p>
<p>Ở khu vực gần lối ra, bạn còn có thể ngắm nhìn cảnh núi đá vôi và tàu thuyền neo đậu trên vịnh.</p>
<h2>Đến hang Sửng Sốt bằng cách nào?</h2>
<p>Hang Sửng Sốt nằm trên đảo nên không thể đến trực tiếp bằng đường bộ. Du khách cần đi tàu tham quan vịnh Hạ Long hoặc chọn hành trình du thuyền có điểm dừng tại hang.</p>
<p>Sau khi tàu cập bến dưới chân đảo, bạn sẽ đi bộ theo cầu thang đá lên cửa hang. Bên trong đã có lối đi dành cho khách tham quan, với những đoạn cầu thang và lan can hỗ trợ.</p>
<p>Du khách tham quan theo tuyến đường quy định trước khi trở xuống khu vực bến thuyền.</p>
<h2>Tham quan hang Sửng Sốt mất bao lâu?</h2>
<p>Một chuyến tham quan hang Sửng Sốt thường mất khoảng <strong>45–60 phút</strong>, bao gồm thời gian leo bậc đá, đi qua các khoang hang và ngắm cảnh.</p>
<p>Thời gian thực tế phụ thuộc vào tốc độ di chuyển, lượng khách tại thời điểm tham quan và lịch trình của tàu.</p>
<p>Nếu đi cùng người lớn tuổi hoặc trẻ nhỏ, bạn nên dành thêm thời gian cho việc lên xuống cầu thang.</p>
<h2>Đường lên hang Sửng Sốt có khó không?</h2>
<p>Đường lên hang có nhiều bậc đá. Bên trong hang cũng có những đoạn lên xuống và bề mặt có thể ẩm, trơn khi thời tiết mưa hoặc độ ẩm cao.</p>
<p>Một số lưu ý khi tham quan:</p>
<ul>
<li>Nên mang giày thể thao hoặc giày có đế bám tốt.</li>
<li>Chuẩn bị nước uống, nhất là vào những ngày nóng.</li>
<li>Đi chậm và chú ý ở các đoạn cầu thang hoặc nền đá ẩm.</li>
<li>Trẻ nhỏ cần có người lớn đi cùng và hỗ trợ khi lên xuống bậc đá.</li>
<li>Người gặp khó khăn khi di chuyển nên cân nhắc trước khi tham quan.</li>
</ul>
<p>Hang không có lối đi phù hợp cho xe lăn. Nếu đi cùng người lớn tuổi hoặc người có vấn đề về vận động, bạn nên trao đổi trước với đơn vị tổ chức tour.</p>
<h2>Nên tham quan hang Sửng Sốt vào thời điểm nào?</h2>
<p>Hang Sửng Sốt đón khách quanh năm theo lịch và quy định của cơ quan quản lý.</p>
<p>Vào mùa hè, thời tiết bên ngoài thường nóng ẩm, vì vậy đoạn leo cầu thang lên cửa hang có thể khiến bạn mất sức hơn. Mùa thu và những ngày thời tiết mát mẻ thường thuận tiện hơn cho việc đi bộ.</p>
<p>Hang cũng là điểm tham quan nổi tiếng nên có thể khá đông, đặc biệt vào cuối tuần, ngày lễ hoặc khi nhiều đoàn tàu đến cùng lúc.</p>
<p>Nếu muốn tham quan thong thả, bạn có thể hỏi đơn vị tổ chức về thời gian dự kiến ghé hang.</p>
<p>Khi có thời tiết nguy hiểm, hoạt động tàu du lịch trên vịnh có thể bị tạm dừng theo quy định của cơ quan chức năng. Vì vậy, lịch tham quan hang cũng có thể thay đổi.</p>
<h2>Vé tham quan hang Sửng Sốt</h2>
<p>Hang Sửng Sốt thuộc tuyến tham quan có thu phí trên vịnh Hạ Long. Vé tham quan thường được mua cùng chương trình đi tàu hoặc bao gồm trong gói du thuyền đã đặt.</p>
<p>Nếu đi theo tour, bạn nên kiểm tra phần dịch vụ bao gồm để biết phí tham quan hang đã được tính trong giá hay chưa.</p>
<p>Đối với khách đặt du thuyền nghỉ đêm, phí tham quan các điểm có trong lịch trình thường được bao gồm theo điều kiện của từng gói dịch vụ.</p>
<h2>Tham quan hang Sửng Sốt cùng Green Ruby Cruises</h2>
<p>Theo chương trình dự kiến, hang Sửng Sốt là một trong những điểm tham quan thuộc một số hành trình của <a href="/vi/du-thuyen/green-ruby-1-1.html"><strong>Green Ruby 1</strong></a> trên vịnh Hạ Long.</p>
<p>Du thuyền Green Ruby 1 được thiết kế cho các chuyến nghỉ đêm kết hợp tham quan danh thắng, trải nghiệm hoạt động trên vịnh và nghỉ dưỡng.</p>
<p>Thời gian ghé hang và các điểm tham quan sẽ được xác nhận theo chương trình chính thức, tùy thuộc điều kiện thời tiết và quy định khai thác tại thời điểm khởi hành.</p>
<p>Bạn có thể tham khảo <a href="/vi/hanh-trinh">các hành trình dự kiến của Green Ruby 1</a> để lựa chọn chuyến đi phù hợp.</p>
<h2>Những địa điểm gần hang Sửng Sốt</h2>
<h3><a href="/vi/dia-diem-tham-quan/hang-luon">Hang Luồn</a></h3>
<p>Nằm trên cùng đảo Bồ Hòn, hang Luồn nổi bật với khu vực mặt nước được bao quanh bởi núi đá vôi. Du khách có thể khám phá bằng thuyền kayak hoặc thuyền nan theo chương trình tham quan.</p>
<h3><a href="/vi/dia-diem-tham-quan/dao-ti-top">Đảo Ti Tốp</a></h3>
<p>Đảo Ti Tốp được biết đến với bãi tắm và điểm ngắm cảnh trên cao. Nếu có đủ thời gian và thể lực, bạn có thể leo lên điểm quan sát để ngắm toàn cảnh một phần vịnh Hạ Long.</p>
<p>Việc kết hợp các địa điểm này phụ thuộc vào tuyến tham quan và lịch trình của từng du thuyền.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Đảo Bồ Hòn, vịnh Hạ Long, Quảng Ninh'],
                ['label' => 'Tên tiếng Anh', 'value' => 'Sung Sot Cave (Surprise Cave)'],
                ['label' => 'Cách di chuyển', 'value' => 'Đi tàu tham quan hoặc du thuyền'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 45–60 phút'],
                ['label' => 'Mức độ di chuyển', 'value' => 'Trung bình, cần leo bậc đá và đi bộ trong hang'],
                ['label' => 'Giờ tham quan', 'value' => '7h30–17h30 (16/4–31/10); 7h30–17h00 (1/11–15/4). Có thể thay đổi, nên kiểm tra trước chuyến đi.'],
                ['label' => 'Vé tham quan', 'value' => 'Thuộc vé tham quan tuyến có hang Sửng Sốt; thường đã bao gồm trong giá tour theo chương trình'],
            ],
            'faq' => [
                ['q' => 'Tham quan hang Sửng Sốt mất bao lâu?', 'a' => 'Thông thường khoảng 45–60 phút, bao gồm thời gian đi bộ lên cửa hang, tham quan bên trong và trở xuống bến thuyền. Nếu đi vào giờ đông khách, thời gian có thể kéo dài hơn.'],
                ['q' => 'Người lớn tuổi và trẻ nhỏ có tham quan hang Sửng Sốt được không?', 'a' => 'Có thể, nếu sức khỏe và khả năng đi lại phù hợp. Tuy nhiên, đường lên hang có nhiều bậc đá, vì vậy người gặp khó khăn khi leo cầu thang nên cân nhắc trước khi tham quan. Trẻ nhỏ cần có người lớn đi cùng.'],
                ['q' => 'Hang Sửng Sốt có đông khách không?', 'a' => 'Hang Sửng Sốt là một trong những điểm tham quan nổi tiếng của vịnh Hạ Long nên có thể đông vào cuối tuần, ngày lễ và những thời điểm nhiều đoàn tàu đến cùng lúc.'],
                ['q' => 'Có thể tự đi tham quan hang Sửng Sốt không?', 'a' => 'Bạn cần sử dụng phương tiện đường thủy để đến đảo Bồ Hòn. Có thể chọn tàu tham quan trong ngày hoặc du thuyền nghỉ đêm có lịch trình ghé hang Sửng Sốt.'],
                ['q' => 'Có cần mua vé riêng để vào hang Sửng Sốt không?', 'a' => 'Phí tham quan hang thuộc hệ thống vé tham quan theo tuyến trên vịnh Hạ Long. Nếu đặt tour hoặc du thuyền, bạn nên kiểm tra xem khoản phí này đã nằm trong giá dịch vụ hay chưa.'],
                ['q' => 'Du thuyền Green Ruby nào dự kiến ghé hang Sửng Sốt?', 'a' => 'Hang Sửng Sốt dự kiến nằm trong một số chương trình tham quan của Green Ruby 1 trên vịnh Hạ Long. Các điểm dừng và thời gian tham quan sẽ được xác nhận trong lịch trình chính thức.'],
            ],
        ],
    ],
    [
        'bay' => 'ha-long',
        'category' => 'island',
        'ord' => 2,
        'latitude' => 20.858053,
        'longitude' => 107.081012,
        'coords_verified' => true,
        'coords_source' => 'GPS of a geotagged photo taken at the summit (Wikimedia Commons, David Stanley)',
        'image' => ['link' => '/2026/10/21/toan-canh-vinh-ha-long-tu-dinh-dao-ti-top.jpg'],
        'image_credit' => null,
        'itineraries' => ['en' => [3, 4], 'vi' => [8, 9]],
        'sources' => [
            $unesco,
            ['title' => 'Wikipedia: Ti Tốp Island', 'url' => 'https://en.wikipedia.org/wiki/Ti_T%E1%BB%91p_Island'],
            ['title' => 'Photo with GPS: Summit of Ti Top Island (Wikimedia Commons)', 'url' => 'https://commons.wikimedia.org/wiki/File:Summit_of_Ti_Top_Island.jpg'],
        ],
        'editor_notes' => "English Wikipedia lists 20.862601, 107.067218, about 1.5 km west of the summit photo GPS used here; the photo position matches 'opposite Sung Sot' and the bay geography. EN rewritten 2026-10-24 from the editorial brief; slug changed titop-island -> ti-top-island (308 redirect in greenruby-web next.config). Verified: route no. 2, ~12 km from Tuan Chau, former name Cat Nang, statue 2015 (Ha Long Bay Management Department); 400+ steps (Quang Ninh portal). Estimates, not sourced: 45-60 min visit, 15-20 min climb. Hero photo is the owner's upload (media library 2026-10-21).",
        'en' => [
            'slug' => 'ti-top-island',
            'name' => 'Ti Top Island',
            'image_alt' => 'Ha Long Bay seen from the summit of Ti Top Island',
            'seo_title' => 'Ti Top Island, Ha Long Bay: Beach, Viewpoint & 400 Steps',
            'seo_description' => 'Explore Ti Top Island in Ha Long Bay, from its crescent-shaped beach to the 400-step summit viewpoint. Find out what to expect and how to visit.',
            'summary' => 'Ti Top Island is a popular stop on Ha Long Bay cruises, known for its small crescent-shaped beach and hilltop viewpoint. Visitors can swim at the foot of the island or climb more than 400 steps for a wide view across the bay.',
            'sources' => [
                ['title' => 'Ha Long Bay Management Department – Ti Top Island', 'url' => 'https://halongbay.com.vn/en/places/3-dao-titop'],
                ['title' => 'Quang Ninh Provincial Portal – Ti Top Beach', 'url' => 'https://quangninh.gov.vn/Sites/en-us/Trang/ChiTietTinTuc.aspx?nid=6624'],
                ['title' => 'UNESCO World Heritage Centre – Ha Long Bay–Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'],
            ],
            'content' => <<<'HTML'
<h2>Where is Ti Top Island?</h2>
<p>Ti Top Island lies in central Ha Long Bay, roughly 12 km from Tuan Chau International Marina. It is part of the bay’s sightseeing Route 2, which also includes <a href="/destinations/sung-sot-cave">Sung Sot Cave</a> and <a href="/destinations/luon-cave">Luon Cave</a> on nearby Bo Hon Island.</p>
<p>The island is reached by boat, usually as part of a day trip or overnight Ha Long Bay cruise.</p>
<h2>Why visit Ti Top Island?</h2>
<p>There are two main reasons to stop here: the beach and the view.</p>
<p>Ti Top Beach curves around one side of the island, with a small stretch of pale sand at the foot of the limestone hill. It is a convenient place for a swim during a cruise, although it can get crowded when several boats arrive together.</p>
<p>The viewpoint is the bigger draw for many visitors. From the top, you can look out over the surrounding limestone islands, narrow channels and cruise boats moving through the bay.</p>
<p>The island also has an unusual history. On 22 January 1962, President Ho Chi Minh visited with Soviet cosmonaut Gherman Titov, the second person to orbit Earth. The island, previously known as Cat Nang, was renamed in Titov’s honour. A statue of the cosmonaut stands on the island today.</p>
<h2>Things to do on Ti Top Island</h2>
<h3>Climb to the Ti Top viewpoint</h3>
<p>The climb involves more than 400 stone steps. It is not particularly long, but the stairs are steep, and the heat can make the ascent tiring.</p>
<p>There are places to pause along the way. At the top, the viewpoint overlooks a broad stretch of Ha Long Bay. If the weather is clear, you can see layers of limestone islands stretching into the distance.</p>
<h3>Swim at Ti Top Beach</h3>
<p>The beach is small, but it offers a chance to get into the water during a sightseeing cruise.</p>
<p>Swimming is permitted only when local conditions allow. Follow the designated swimming areas and your guide’s instructions, especially when boat traffic is heavy.</p>
<h3>Relax by the shore</h3>
<p>You do not have to climb the hill to enjoy the stop. Visitors who prefer to stay at sea level can spend their time on the beach, take photographs or simply watch the boats coming and going.</p>
<h2>How to get to Ti Top Island</h2>
<p>Ti Top Island is accessible by boat, not by road. Most visitors arrive on organised day tours or overnight cruises departing from the Ha Long or Tuan Chau port areas.</p>
<p>Boats stop at the island’s landing pier. From there, the beach is close by, while the stairway to the viewpoint begins further up the island.</p>
<p><a href="/cruise/green-ruby-1-3.html">Green Ruby 1</a> includes Ti Top Island in its planned Ha Long Bay itineraries.</p>
<h2>How long do you need at Ti Top Island?</h2>
<p>Allow around 45–60 minutes for a typical visit (estimate).</p>
<p>That is usually enough time to climb to the viewpoint, take photographs and return to the pier. If you would rather swim, you can spend the stop at the beach instead.</p>
<p>Trying to do both may feel rushed, particularly on a busy day. Check how much time your guide has allocated before setting off up the stairs.</p>
<h2>What to expect when you arrive</h2>
<p>Ti Top is one of the better-known stops in Ha Long Bay. The pier, beach and stairs can become busy, particularly in the afternoon when several cruise groups arrive around the same time.</p>
<p>The climb is exposed in places, so it can feel hotter than expected during summer. The steps may also be slippery after rain.</p>
<p>Comfortable footwear is useful if you plan to go to the summit. Bring water, sun protection and swimwear if you intend to use the beach.</p>
<h2>Accessibility and practical tips</h2>
<p>The summit is reached by a steep stairway and is not wheelchair-accessible. Visitors who cannot manage the climb can remain near the beach and pier, although boarding and disembarking may still involve steps or uneven surfaces.</p>
<p>A few things worth knowing:</p>
<ul>
<li>Wear shoes with a good grip for the climb.</li>
<li>Carry drinking water, particularly in warm weather.</li>
<li>Bring a towel and swimwear if you plan to swim.</li>
<li>Keep an eye on your group’s departure time.</li>
<li>Follow the crew’s advice about swimming and weather conditions.</li>
</ul>
<h2>Best time to visit Ti Top Island</h2>
<p>Ti Top can be visited throughout the year, subject to weather and cruise operations.</p>
<p>From May to October, the warmer water makes swimming more appealing, but summer also brings heavier rain and a greater risk of storms.</p>
<p>From November to April, cooler conditions can make the climb more comfortable. Swimming may be less appealing, especially during the colder winter months.</p>
<p>For the viewpoint, clear visibility matters more than the season. Haze, cloud and rain can all affect how much of the bay you can see. See <a href="/blog/travel-tips/best-time-to-visit-halong-bay.html">the best time to visit Halong Bay</a> for month-by-month conditions.</p>
<h2>Visit Ti Top Island with Green Ruby Cruises</h2>
<p>Ti Top Island is included in the planned Ha Long Bay routes aboard Green Ruby 1.</p>
<p><strong><a href="/itinerary/the-halong-escape-2-days-1-night-3-4.html">The Halong Escape – 2 Days 1 Night</a></strong><br>A short overnight cruise combining Ti Top Island, Luon Cave and Sung Sot Cave.</p>
<p><strong><a href="/itinerary/the-halong-immersion-3-days-2-nights-3-3.html">The Halong Immersion – 3 Days 2 Nights</a></strong><br>A longer Ha Long Bay journey with an additional day to explore beyond the main sightseeing route.</p>
<p><em>These are planned stops. Final routes and timings will be confirmed once Green Ruby’s sailings begin and may change due to weather or port-authority instructions.</em></p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Central Ha Long Bay, Vietnam'],
                ['label' => 'Known for', 'value' => 'Hilltop viewpoint and crescent-shaped beach'],
                ['label' => 'Steps to summit', 'value' => 'More than 400'],
                ['label' => 'Access', 'value' => 'By sightseeing boat or cruise'],
                ['label' => 'Typical visit', 'value' => 'Approximately 45–60 minutes'],
                ['label' => 'Swimming', 'value' => 'Permitted when conditions allow'],
                ['label' => 'Former name', 'value' => 'Cat Nang Island'],
                ['label' => 'Named after', 'value' => 'Soviet cosmonaut Gherman Titov'],
                ['label' => 'Also spelled', 'value' => 'Titop Island'],
            ],
            'faq' => [
                ['q' => 'Why is it called Ti Top Island?', 'a' => 'The island was named after Soviet cosmonaut Gherman Titov, who visited Ha Long Bay with President Ho Chi Minh on 22 January 1962. It was previously known as Cat Nang Island.'],
                ['q' => 'Can you swim at Ti Top Beach?', 'a' => 'Yes, swimming is one of the main activities at Ti Top Beach. It depends on sea conditions and local safety instructions, so follow the advice of your cruise crew.'],
                ['q' => 'How many steps are there on Ti Top Island?', 'a' => 'There are more than 400 stone steps leading to the viewpoint. The climb is steep but manageable for many visitors with reasonable mobility. Allow extra time in hot or wet weather.'],
                ['q' => 'How long does it take to climb Ti Top Island?', 'a' => 'Allow roughly 15–20 minutes to reach the top, depending on your pace and how busy the stairs are. You will also need time to enjoy the view and walk back down.'],
                ['q' => 'Is Ti Top Island worth visiting?', 'a' => 'Yes, particularly if you want a high viewpoint over Ha Long Bay. The beach is relatively small, so the climb and panoramic view are often the more memorable parts of the visit.'],
                ['q' => 'When does Green Ruby plan to visit Ti Top Island?', 'a' => 'Ti Top Island is included in Green Ruby 1’s planned 2-day/1-night and 3-day/2-night Ha Long Bay itineraries. The published 2-day itinerary schedules the stop for approximately 4:30 PM on the first day. All stops remain subject to weather, local regulations and operational conditions.'],
            ],
        ],
        'vi' => [
            'slug' => 'dao-ti-top',
            'name' => 'Đảo Ti Tốp',
            'image_alt' => 'Vịnh Hạ Long nhìn từ đỉnh đảo Ti Tốp',
            'seo_title' => 'Đảo Ti Tốp Hạ Long: 400 Bậc Leo Núi, Bãi Tắm & Kinh Nghiệm',
            'seo_description' => 'Đảo Ti Tốp ở đâu, có gì chơi? Tìm hiểu đường leo khoảng 400 bậc lên đỉnh, bãi tắm Ti Tốp, thời gian tham quan và kinh nghiệm đi đảo.',
            'summary' => 'Đảo Ti Tốp nằm giữa vịnh Hạ Long, nổi tiếng với bãi tắm nhỏ hình vầng trăng khuyết và điểm ngắm cảnh trên đỉnh núi. Du khách có thể tắm biển dưới chân đảo hoặc leo khoảng 400 bậc đá để nhìn toàn cảnh vịnh từ trên cao.',
            'sources' => [
                ['title' => 'Ban Quản lý vịnh Hạ Long – Đảo Ti Tốp', 'url' => 'https://halongbay.com.vn/en/places/3-dao-titop'],
                ['title' => 'UNESCO – Vịnh Hạ Long và Quần đảo Cát Bà', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'Báo VietNamNet – Đảo Ti Tốp trên vịnh Hạ Long', 'url' => 'https://vietnamnet.vn/dao-ti-top-nam-nghieng-la-mat-khach-ru-nhau-tam-giai-nhiet-leo-nui-o-ha-long-2163857.html'],
            ],
            'content' => <<<'HTML'
<h2>Đảo Ti Tốp ở đâu?</h2>
<p>Đảo Ti Tốp nằm ở khu vực trung tâm vịnh Hạ Long, cách Bãi Cháy khoảng 7–8 km về phía đông nam và cách cảng tàu khách quốc tế Tuần Châu khoảng 12 km đường thủy.</p>
<p>Đảo nằm gần đảo Bồ Hòn, nơi có <a href="/vi/dia-diem-tham-quan/hang-sung-sot">hang Sửng Sốt</a> và <a href="/vi/dia-diem-tham-quan/hang-luon">hang Luồn</a>. Đây đều là những điểm tham quan thuộc tuyến số 2 trên vịnh Hạ Long, thường được kết hợp trong các chuyến tham quan trong ngày và hành trình du thuyền nghỉ đêm.</p>
<p>Du khách chỉ có thể đến đảo bằng tàu. Từ bến cập tàu, đi bộ một đoạn ngắn là tới bãi tắm và lối lên đỉnh núi.</p>
<h2>Đảo Ti Tốp có gì đặc biệt?</h2>
<p>Điểm hấp dẫn nhất của Ti Tốp là du khách có thể vừa tắm biển, vừa ngắm vịnh Hạ Long từ trên cao trong cùng một điểm dừng.</p>
<p>Bãi tắm nằm ngay dưới chân núi, có dải cát cong hình lưỡi liềm. Bãi không rộng, nhưng là một trong những nơi du khách có thể xuống tắm trong hành trình tham quan khu vực trung tâm vịnh.</p>
<p>Nếu thích ngắm cảnh, bạn có thể leo lên đỉnh đảo. Từ đây, những đảo đá vôi nằm xen giữa mặt nước hiện ra khá rõ. Vào ngày trời quang, có thể nhìn thấy các con tàu di chuyển giữa những dãy núi đá ở phía xa. Đây cũng là một trong những góc chụp ảnh quen thuộc nhất của vịnh Hạ Long.</p>
<p>Ngoài cảnh quan, Ti Tốp còn gắn với một câu chuyện lịch sử. Ngày 22/1/1962, Chủ tịch Hồ Chí Minh cùng nhà du hành vũ trụ Liên Xô Gherman Titov đến thăm đảo. Sau chuyến thăm, Bác Hồ đặt tên đảo theo tên ông Titov, người thứ hai bay vào quỹ đạo Trái Đất.</p>
<p>Trước đó, đảo từng được gọi là Cát Nàng. Ngày nay, tượng đài Gherman Titov được đặt trên đảo để ghi nhớ chuyến thăm lịch sử này.</p>
<h2>Những hoạt động nên trải nghiệm trên đảo Ti Tốp</h2>
<h3>Leo lên đỉnh đảo ngắm toàn cảnh vịnh Hạ Long</h3>
<p>Đường lên đỉnh Ti Tốp có khoảng 400 bậc đá, với lan can và một số vị trí có thể dừng nghỉ. Quãng đường không quá dài nhưng khá dốc, nhất là đoạn gần đỉnh.</p>
<p>Nếu đi với tốc độ vừa phải, bạn nên dành khoảng 15–20 phút để leo lên. Vào mùa hè, thời tiết nóng và độ ẩm cao có thể khiến việc leo bậc thang mất sức hơn.</p>
<p>Lên tới điểm ngắm cảnh, bạn có thể nhìn bao quát vùng biển xung quanh, những đảo đá lớn nhỏ và các con tàu đang neo đậu trên vịnh.</p>
<p>Nếu thích chụp ảnh, nên dành thêm một chút thời gian trên đỉnh thay vì vội vàng xuống ngay. Vào ngày có nắng, ánh sáng cuối chiều thường dịu hơn, nhưng tầm nhìn còn phụ thuộc vào mây, sương và thời tiết thực tế.</p>
<h3>Tắm biển tại bãi tắm Ti Tốp</h3>
<p>Bãi tắm Ti Tốp nằm dưới chân đảo, ngay gần khu vực cầu tàu. Bãi có hình vòng cung, diện tích không lớn nên đôi khi khá đông khách, nhất là vào mùa hè.</p>
<p>Đây là lựa chọn phù hợp nếu bạn muốn xuống nước thư giãn sau thời gian ngồi tàu tham quan vịnh.</p>
<p>Hoạt động tắm biển phụ thuộc vào điều kiện thời tiết, tình trạng mặt biển và quy định tại điểm tham quan. Du khách cần tắm trong khu vực cho phép và tuân thủ hướng dẫn an toàn.</p>
<h3>Nghỉ ngơi và chụp ảnh bên bờ biển</h3>
<p>Không nhất thiết phải leo núi mới có thể tận hưởng thời gian ở Ti Tốp.</p>
<p>Nếu đi cùng trẻ nhỏ, người lớn tuổi hoặc đơn giản là không muốn vận động nhiều, bạn có thể nghỉ ngơi tại bãi biển, chụp ảnh những đảo đá xung quanh hoặc quan sát tàu thuyền ra vào bến.</p>
<p>Khu vực gần bãi tắm cũng có một số tiện ích cơ bản phục vụ du khách.</p>
<h2>Cách đi đến đảo Ti Tốp</h2>
<p>Đảo Ti Tốp không có đường bộ nối với đất liền. Cách phổ biến nhất để đến đây là tham gia tour tham quan vịnh Hạ Long hoặc đi du thuyền có lịch trình ghé đảo.</p>
<p>Các chuyến tham quan thường xuất phát từ cảng tàu khách quốc tế Tuần Châu hoặc các cảng được cấp phép tại khu vực Hạ Long. Xem thêm <a href="/vi/cam-nang/ha-noi-di-ha-long">hướng dẫn di chuyển từ Hà Nội đến Hạ Long</a>.</p>
<p>Ti Tốp thuộc tuyến tham quan số 2, thường kết hợp với hang Sửng Sốt và hang Luồn. Tùy lịch trình, du khách có thể đến đảo vào buổi sáng hoặc buổi chiều.</p>
<p>Trong các hành trình dự kiến của <a href="/vi/du-thuyen/green-ruby-1-1.html">Green Ruby 1</a>, đảo Ti Tốp là một điểm dừng để du khách lựa chọn leo núi ngắm cảnh hoặc tắm biển.</p>
<h2>Tham quan đảo Ti Tốp mất bao lâu?</h2>
<p>Thông thường, bạn nên dành khoảng 45–60 phút để tham quan đảo Ti Tốp.</p>
<p>Nếu muốn lên đỉnh, hãy tính cả thời gian leo lên, nghỉ ngắm cảnh và đi xuống. Tổng thời gian có thể khoảng 40–50 phút, tùy thể lực và lượng khách trên cầu thang.</p>
<p>Nếu chỉ tắm biển hoặc nghỉ tại bãi cát, thời gian tham quan sẽ thoải mái hơn.</p>
<p>Trong trường hợp muốn vừa leo núi vừa tắm biển, bạn nên hỏi hướng dẫn viên trước để biết chính xác tàu dừng tại đảo bao lâu. Vào ngày đông khách, việc di chuyển lên xuống đỉnh có thể mất nhiều thời gian hơn dự kiến.</p>
<h2>Những điều cần biết trước khi lên đảo</h2>
<p>Ti Tốp là điểm tham quan nổi tiếng nên thường có nhiều đoàn khách ghé cùng lúc. Cầu tàu, bãi biển và lối lên đỉnh có thể đông vào những khung giờ cao điểm.</p>
<p>Đường lên đỉnh chủ yếu là bậc đá. Một số đoạn khá dốc, có thể trơn sau mưa. Nếu định leo núi, bạn nên mang giày hoặc dép có độ bám tốt, tránh đi dép quá trơn.</p>
<p>Một vài lưu ý khi tham quan:</p>
<ul>
<li>Mang theo nước uống, nhất là trong những ngày nắng nóng.</li>
<li>Chuẩn bị đồ bơi, khăn tắm nếu muốn xuống biển.</li>
<li>Sử dụng mũ, kính râm và kem chống nắng khi cần.</li>
<li>Không tự ý bơi ra ngoài khu vực được phép.</li>
<li>Để ý giờ tập trung của đoàn, tránh lên đỉnh quá sát giờ tàu rời bến.</li>
</ul>
<p>Đỉnh đảo chỉ có thể lên bằng cầu thang, không phù hợp với người gặp khó khăn khi leo bậc hoặc sử dụng xe lăn. Khu vực bãi tắm dễ tiếp cận hơn, nhưng việc lên xuống tàu vẫn có thể cần bước qua bậc hoặc cầu dẫn.</p>
<h2>Nên đi đảo Ti Tốp vào tháng mấy?</h2>
<p>Đảo Ti Tốp có thể tham quan quanh năm, miễn là thời tiết và hoạt động tàu du lịch cho phép.</p>
<p>Từ tháng 5 đến tháng 10, thời tiết thường nóng, nước biển ấm hơn, phù hợp với hoạt động tắm biển. Tuy nhiên, đây cũng là giai đoạn có thể xuất hiện mưa lớn, áp thấp nhiệt đới hoặc bão, khiến lịch trình trên vịnh phải thay đổi.</p>
<p>Từ tháng 11 đến tháng 4, thời tiết thường mát hoặc lạnh hơn. Việc leo núi có thể dễ chịu hơn những ngày hè nắng gắt, nhưng nước biển khá lạnh vào một số thời điểm nên ít người xuống tắm.</p>
<p>Nếu mục đích chính là lên đỉnh ngắm cảnh, bạn nên chú ý dự báo về mưa, sương mù và tầm nhìn. Một ngày trời quang sẽ giúp quan sát vịnh rõ hơn, bất kể mùa nào. Xem thêm <a href="/vi/blog/hanh-trinh/di-ha-long-mua-nao-dep.html">du lịch Hạ Long mùa nào đẹp nhất</a>.</p>
<h2>Tham quan đảo Ti Tốp cùng Green Ruby Cruises</h2>
<p>Đảo Ti Tốp nằm trong lịch trình dự kiến của du thuyền Green Ruby 1 tại vịnh Hạ Long.</p>
<p><strong><a href="/vi/hanh-trinh/2-ngay-1-dem-mot-thoang-ha-long-1-9.html">2 ngày 1 đêm – Một Thoáng Hạ Long</a></strong><br>Hành trình nghỉ đêm trên vịnh, kết hợp tham quan đảo Ti Tốp, hang Luồn và hang Sửng Sốt.</p>
<p><strong><a href="/vi/hanh-trinh/3-ngay-2-dem-dau-an-ha-long-1-8.html">3 ngày 2 đêm – Dấu Ấn Hạ Long</a></strong><br>Hành trình dài hơn, có thêm thời gian khám phá những địa điểm khác trên vịnh Hạ Long.</p>
<p><em>Các điểm dừng trên thuộc kế hoạch đã công bố của Green Ruby 1. Lịch trình chính thức sẽ được xác nhận khi tàu đi vào hoạt động và có thể thay đổi theo điều kiện thực tế.</em></p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Khu vực trung tâm vịnh Hạ Long, Quảng Ninh'],
                ['label' => 'Khoảng cách', 'value' => 'Cách Bãi Cháy khoảng 7–8 km; cách cảng Tuần Châu khoảng 12 km'],
                ['label' => 'Tuyến tham quan', 'value' => 'Tuyến số 2 vịnh Hạ Long'],
                ['label' => 'Điểm nổi bật', 'value' => 'Bãi tắm hình lưỡi liềm, đỉnh ngắm cảnh'],
                ['label' => 'Đường lên đỉnh', 'value' => 'Khoảng 400 bậc đá'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 45–60 phút'],
                ['label' => 'Cách di chuyển', 'value' => 'Tàu tham quan hoặc du thuyền'],
                ['label' => 'Tên cũ', 'value' => 'Cát Nàng'],
                ['label' => 'Nguồn gốc tên gọi', 'value' => 'Đặt theo tên nhà du hành vũ trụ Gherman Titov năm 1962'],
            ],
            'faq' => [
                ['q' => 'Vì sao đảo có tên Ti Tốp?', 'a' => 'Tên đảo được đặt theo nhà du hành vũ trụ Liên Xô Gherman Titov. Ngày 22/1/1962, ông cùng Chủ tịch Hồ Chí Minh đến thăm đảo và Bác Hồ đã đặt tên đảo theo tên ông. Trước đó, đảo từng có tên là Cát Nàng.'],
                ['q' => 'Đảo Ti Tốp có tắm biển được không?', 'a' => 'Có. Dưới chân đảo có bãi tắm dành cho du khách. Tuy nhiên, việc tắm biển chỉ được thực hiện khi điều kiện thời tiết và quy định an toàn tại điểm tham quan cho phép.'],
                ['q' => 'Leo lên đỉnh Ti Tốp có bao nhiêu bậc?', 'a' => 'Lối lên đỉnh có khoảng 400 bậc đá. Đường khá dốc nhưng có lan can và một số vị trí dừng nghỉ. Du khách có sức khỏe bình thường thường có thể hoàn thành quãng đường này nếu đi chậm và nghỉ khi cần.'],
                ['q' => 'Leo lên đỉnh đảo Ti Tốp mất bao lâu?', 'a' => 'Thông thường mất khoảng 15–20 phút để lên tới điểm ngắm cảnh, tùy thể lực và lượng khách. Bạn nên tính thêm thời gian chụp ảnh, nghỉ ngơi và đi xuống.'],
                ['q' => 'Có nên đi đảo Ti Tốp không?', 'a' => 'Nếu muốn ngắm vịnh Hạ Long từ trên cao, Ti Tốp là một điểm dừng đáng cân nhắc. Bãi tắm tương đối nhỏ, nhưng góc nhìn từ đỉnh đảo là trải nghiệm nổi bật nhất ở đây.'],
                ['q' => 'Green Ruby dự kiến ghé đảo Ti Tốp khi nào?', 'a' => 'Theo lịch trình dự kiến, Green Ruby 1 sẽ ghé đảo Ti Tốp vào khoảng 16:30 ngày đầu tiên trong các hành trình vịnh Hạ Long 2 ngày 1 đêm và 3 ngày 2 đêm. Thời gian thực tế có thể điều chỉnh theo thời tiết, quy định của cơ quan quản lý và lịch vận hành.'],
            ],
        ],
    ],
    [
        'bay' => 'ha-long',
        'category' => 'cave',
        'ord' => 3,
        'latitude' => 20.853563,
        'longitude' => 107.094566,
        'coords_verified' => true,
        'coords_source' => 'GPS of a geotagged photo taken at the cave (Wikimedia Commons, Jakub Hałun)',
        'image' => ['file' => 'luon-cave-ha-long-bay.jpg', 'link' => '/2026/10/24/luon-cave-ha-long-bay.jpg'],
        'image_credit' => 'Photo: Jakub Hałun, CC BY 4.0, via Wikimedia Commons',
        'itineraries' => ['en' => [3, 4], 'vi' => [8, 9]],
        'sources' => [
            $unesco,
            ['title' => 'Wikipedia (Vietnamese): Hang Sửng Sốt (Bo Hon Island, with Luon Cave)', 'url' => 'https://vi.wikipedia.org/wiki/Hang_S%E1%BB%ADng_S%E1%BB%91t'],
            ['title' => 'Photo with GPS: Luon Cave, Jakub Hałun (Wikimedia Commons)', 'url' => 'https://commons.wikimedia.org/wiki/File:Hang_Lu%E1%BB%93n_Cave,_Ha_Long_Bay,_Vietnam,_20240128_1452_3806.jpg'],
        ],
        'editor_notes' => "EN rewritten 2026-10-24 from the editorial brief. Verified (Ha Long Bay Management Department): NE of Bo Hon, ~13 km from Tuan Chau, route no. 2, bamboo boat/kayak only, crouch at high tide, several hundred rhesus macaques; kayak/bamboo service in the Luon area. Estimates, not sourced: 45-60 min stop, 30-45 min on the water. Management Board hours: 7h30-17h30 (16/4-31/10), 7h30-17h00 (1/11-15/4), not shown yet. Not published: tunnel length/height, tide thresholds, boat fees.",
        'en' => [
            'slug' => 'luon-cave',
            'name' => 'Luon Cave',
            'image_alt' => 'Rowing boats passing under the rock arch of Luon Cave into the enclosed lagoon, Ha Long Bay',
            'seo_title' => 'Luon Cave, Ha Long Bay: Kayaking, Bamboo Boats & Guide',
            'seo_description' => 'Explore Luon Cave in Ha Long Bay by kayak or bamboo boat. Learn about the lagoon, wildlife, tides, visiting time and what to expect before your trip.',
            'summary' => 'Luon Cave is a low limestone passage on Bo Hon Island in Ha Long Bay. Visitors travel through it by kayak or bamboo boat to reach a sheltered lagoon surrounded by steep, forested cliffs. Unlike the bay’s larger caves, Luon Cave is explored entirely from the water.',
            'sources' => [
                ['title' => 'Ha Long Bay Management Department – Luon Cave', 'url' => 'https://halongbay.com.vn/en/places/31-hang-luon'],
                ['title' => 'Ha Long Bay Management Department – Kayaking and Bamboo Boat Services', 'url' => 'https://halongbay.com.vn/en/p/94-dich-vu-cheo-do-cheo-kayak'],
                ['title' => 'UNESCO World Heritage Centre – Ha Long Bay–Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'Wikimedia Commons – Luon Cave Photograph', 'url' => 'https://commons.wikimedia.org/wiki/File:Hang_Lu%E1%BB%93n_Cave,_Ha_Long_Bay,_Vietnam,_20240128_1452_3806.jpg'],
            ],
            'content' => <<<'HTML'
<h2>Where is Luon Cave?</h2>
<p>Luon Cave lies on the northeastern side of Bo Hon Island, in central Ha Long Bay, approximately 13 km from Tuan Chau International Marina.</p>
<p>It is part of Ha Long Bay’s sightseeing Route 2, along with nearby <a href="/destinations/sung-sot-cave">Sung Sot Cave</a> and <a href="/destinations/ti-top-island">Ti Top Island</a>. These attractions are often combined on day trips and overnight cruises.</p>
<p>The name “Luon” refers to passing through. Here, the cave is a natural tunnel connecting the open bay with a lagoon hidden inside the limestone island.</p>
<p>There is no walking path through the cave. Visitors enter and leave by kayak or bamboo rowing boat.</p>
<h2>What makes Luon Cave worth visiting?</h2>
<p>The most interesting part of Luon Cave is what lies beyond its entrance.</p>
<p>From the outside, you see a low opening in the limestone cliff. Passing underneath brings you into a lagoon enclosed by high rock walls, with trees and other vegetation growing along the slopes.</p>
<p>The water is generally more sheltered than the open bay. You can paddle along the edge of the lagoon, look up at the limestone cliffs and take in the surroundings without travelling far from the cave entrance.</p>
<p>Wild macaques also live around Bo Hon Island and are sometimes spotted on the rocks or among the trees. Sightings are not guaranteed, and visitors should keep their distance.</p>
<p>It is a different experience from visiting <a href="/destinations/sung-sot-cave">Sung Sot Cave</a>, where you walk through large chambers. At Luon Cave, the limestone formations are best appreciated from a small boat, close to the water.</p>
<h2>Things to do at Luon Cave</h2>
<h3>Kayaking through Luon Cave</h3>
<p>Kayaking is one of the most popular ways to visit Luon Cave.</p>
<p>After putting on a life jacket and receiving basic instructions, you paddle towards the cave entrance. The passage is low, so you may need to lean forward as you move beneath the rock ceiling.</p>
<p>Beyond the tunnel, the lagoon opens up. You can paddle around the sheltered water, take photographs and enjoy views of the cliffs before returning through the same passage.</p>
<p>The route is relatively short and is generally suitable for beginners when conditions are calm. You do not usually need previous kayaking experience, although you should be comfortable sitting in a small boat and following safety instructions.</p>
<h3>Taking a bamboo boat</h3>
<p>If you would rather not paddle, a bamboo boat is a good alternative.</p>
<p>These small boats are rowed by local boatmen, allowing passengers to sit back and enjoy the journey through the cave and around the lagoon.</p>
<p>Bamboo boats can be a more comfortable choice for families with children, older visitors or anyone who prefers a less physically demanding activity.</p>
<p>They also leave your hands free for photographs, although it is worth protecting your phone or camera from splashes.</p>
<h3>Looking for wildlife around the lagoon</h3>
<p>The wooded cliffs around Luon Cave provide habitat for macaques and other wildlife.</p>
<p>You may see monkeys moving along the rocks or resting in the trees. They are wild animals, so avoid approaching them, making noise to attract them or offering food.</p>
<p>Even without a wildlife sighting, there is plenty to observe along the lagoon’s limestone walls, particularly where plants have taken root in cracks above the water.</p>
<h2>Kayak or bamboo boat: which should you choose?</h2>
<p>Both options follow a similar route through the cave and into the lagoon, but the experience is slightly different.</p>
<p>Choose a <strong>kayak</strong> if you enjoy being active and want to explore at your own pace. You will paddle the boat yourself, so some movement and coordination are required.</p>
<p>Choose a <strong>bamboo boat</strong> if you prefer to relax, take photographs or avoid the physical effort of paddling. A local boatman handles the rowing.</p>
<p>For visitors who are unsure about kayaking, the bamboo boat is often the simpler option. Either way, you will see the same main attraction: the lagoon beyond the limestone passage.</p>
<h2>How to get to Luon Cave</h2>
<p>Luon Cave is accessible only by boat as part of a trip into Ha Long Bay.</p>
<p>Most visitors arrive on a day cruise or overnight cruise departing from Tuan Chau or another authorised passenger port in the Ha Long area. See <a href="/guides/hanoi-to-halong-bay">how to get from Hanoi to Halong Bay</a>.</p>
<p>Sightseeing boats stop near a floating platform by the cave entrance. Passengers then transfer to kayaks or bamboo boats for the visit.</p>
<p>Life jackets are provided, and local staff help organise boarding and departures.</p>
<p><a href="/cruise/green-ruby-1-3.html">Green Ruby 1</a> includes Luon Cave in its planned Ha Long Bay itineraries, with kayaking or a bamboo boat ride among the scheduled activities.</p>
<h2>How long does a visit to Luon Cave take?</h2>
<p>Allow approximately 45–60 minutes for the full stop, including boarding, the boat ride and returning to your cruise (estimate).</p>
<p>The time actually spent on the water is often around 30–45 minutes, depending on the number of visitors, the chosen activity and local conditions.</p>
<p>During busy periods, you may need to wait briefly at the floating platform while other groups board or return their boats.</p>
<p>If you are visiting on an organised cruise, follow your guide’s departure time rather than planning to stay longer.</p>
<h2>What to expect when you arrive</h2>
<p>The first thing you will notice is how low the cave entrance sits above the water.</p>
<p>Depending on the tide, passengers may need to duck when passing underneath. At higher water levels, there may not be enough clearance for boats to enter safely.</p>
<p>The passage is shaded, but it is relatively short. Once through, you emerge into the lagoon, surrounded by limestone cliffs.</p>
<p>There is no dry cave floor or walking trail to explore. The visit takes place entirely on the water, and you return through the same entrance.</p>
<p>Luon Cave can be busy when several cruise groups arrive together, but traffic is managed around the boarding area and cave passage.</p>
<h2>Accessibility and practical tips</h2>
<p>Visiting Luon Cave involves stepping from a floating platform into a small boat. This requires some balance and may be difficult for visitors with limited mobility.</p>
<p>Bamboo boats require less physical effort than kayaks, but they are not necessarily wheelchair-accessible. Ask your cruise operator about boarding assistance if you have mobility concerns.</p>
<p>Before setting off:</p>
<ul>
<li>Wear the provided life jacket throughout the activity.</li>
<li>Keep your phone, camera and valuables in a waterproof bag.</li>
<li>Wear comfortable clothes that can get splashed.</li>
<li>Follow instructions when passing beneath the low cave ceiling.</li>
<li>Avoid standing up or leaning over the side of the boat.</li>
<li>Do not feed or approach the monkeys.</li>
</ul>
<p>Swimming is not part of the standard Luon Cave visit. Remain in your boat unless local staff explicitly advise otherwise.</p>
<h2>Best time to visit Luon Cave</h2>
<p>Luon Cave can be visited throughout the year, depending on weather, tides and cruise operations.</p>
<p>From roughly April to October, the weather is generally warmer, making kayaking and bamboo boat rides more comfortable. Summer can also bring heavy rain, strong winds and tropical storms that disrupt activities on the bay.</p>
<p>From November to March, conditions are often cooler. A light jacket may be useful on windy days, particularly when sitting in a bamboo boat.</p>
<p>The tide is especially important at Luon Cave. Because the passage is low, high water can reduce the space beneath the rock ceiling and sometimes prevent boats from entering.</p>
<p>Your cruise crew and the local operators will decide whether the visit can go ahead safely on the day. See <a href="/blog/travel-tips/best-time-to-visit-halong-bay.html">the best time to visit Halong Bay</a> for month-by-month conditions.</p>
<h2>Explore Luon Cave with Green Ruby Cruises</h2>
<p>Luon Cave is included in the planned Ha Long Bay itineraries aboard Green Ruby 1.</p>
<p><strong><a href="/itinerary/the-halong-escape-2-days-1-night-3-4.html">The Halong Escape – 2 Days 1 Night</a></strong><br>An overnight cruise combining a kayak or bamboo boat visit to Luon Cave with Ti Top Island and Sung Sot Cave.</p>
<p><strong><a href="/itinerary/the-halong-immersion-3-days-2-nights-3-3.html">The Halong Immersion – 3 Days 2 Nights</a></strong><br>A longer Ha Long Bay journey with additional time to explore beyond the main sightseeing route.</p>
<p><em>These are planned stops. Final itineraries and timings will be confirmed when Green Ruby’s sailings begin and may change due to tides, weather, local regulations or operational conditions.</em></p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Bo Hon Island, central Ha Long Bay, Vietnam'],
                ['label' => 'Sightseeing route', 'value' => 'Ha Long Bay Route 2'],
                ['label' => 'Known for', 'value' => 'Water-level limestone tunnel and enclosed lagoon'],
                ['label' => 'Main activities', 'value' => 'Kayaking and bamboo boat rides'],
                ['label' => 'Access', 'value' => 'By cruise boat, followed by kayak or bamboo boat'],
                ['label' => 'Typical visit', 'value' => 'Approximately 45–60 minutes'],
                ['label' => 'Walking access', 'value' => 'No walking route through the cave'],
                ['label' => 'Wildlife', 'value' => 'Macaques may be seen along the cliffs'],
                ['label' => 'Conditions', 'value' => 'Dependent on tides, weather and local safety rules'],
            ],
            'faq' => [
                ['q' => 'What is special about Luon Cave?', 'a' => 'Luon Cave is a natural limestone tunnel that leads into a lagoon surrounded by steep cliffs. Unlike larger caves in Ha Long Bay, it is explored by kayak or bamboo boat rather than on foot.'],
                ['q' => 'Should I choose a kayak or a bamboo boat at Luon Cave?', 'a' => 'Choose a kayak if you enjoy paddling and want a more active experience. A bamboo boat is better if you prefer to sit back while a local boatman rows. Both take visitors through the cave and into the lagoon.'],
                ['q' => 'Do I need kayaking experience to visit Luon Cave?', 'a' => 'No previous experience is usually required. The kayaking route is short and the lagoon is sheltered, but conditions vary. Follow the safety briefing and choose a bamboo boat if you are uncomfortable paddling.'],
                ['q' => 'Can children visit Luon Cave?', 'a' => 'Children can generally join a bamboo boat ride when the operator permits it and suitable life jackets are available. Kayaking age restrictions may vary, so check with your cruise operator before the trip.'],
                ['q' => 'Can you walk through Luon Cave?', 'a' => 'No. Luon Cave is a water-level passage with no pedestrian path. Visitors travel through it by kayak or bamboo rowing boat.'],
                ['q' => 'How long does it take to explore Luon Cave?', 'a' => 'The activity on the water typically takes around 30–45 minutes. Allow approximately 45–60 minutes for the full cruise stop, including boarding and returning to the main boat.'],
                ['q' => 'Are there monkeys at Luon Cave?', 'a' => 'Wild macaques live around the limestone cliffs and are sometimes visible from the lagoon. Sightings depend on their movements, and visitors should not feed or approach them.'],
                ['q' => 'When does Green Ruby plan to visit Luon Cave?', 'a' => 'Luon Cave is included in Green Ruby 1’s planned 2-day/1-night and 3-day/2-night Ha Long Bay itineraries. The published 2-day itinerary schedules the visit for approximately 3:30 PM on the first day. All stops remain subject to weather, tidal conditions and operational requirements.'],
            ],
        ],
        'vi' => [
            'slug' => 'hang-luon',
            'name' => 'Hang Luồn',
            'image_alt' => 'Thuyền nan đi qua vòm đá hang Luồn vào áng nước kín, vịnh Hạ Long',
            'seo_title' => 'Hang Luồn, vịnh Hạ Long: chèo kayak hay đi đò nan?',
            'seo_description' => 'Hang Luồn là hang nước xuyên qua núi đá dẫn vào một áng kín trên vịnh Hạ Long. Đi kayak hay đò nan, thủy triều, trẻ em và cần chuẩn bị gì.',
            'summary' => 'Hang nước thấp xuyên qua đảo Bồ Hòn, dẫn vào một áng nước được vách núi bao quanh. Du khách đi qua bằng kayak hoặc ngồi đò nan do người dân địa phương chèo.',
            'content' => <<<'HTML'
<h2>Vị trí</h2>
<p>Hang Luồn xuyên qua đảo Bồ Hòn ở trung tâm vịnh Hạ Long, cùng đảo với hang Sửng Sốt. Đúng như tên gọi, đây không phải hang để đi bộ bên trong mà là một vòm đá sát mặt nước, thuyền luồn qua để vào phía trong.</p>
<h2>Vì sao nên ghé</h2>
<p>Qua khỏi vòm đá là một áng nước kín, bốn bề là vách núi dựng đứng phủ cây. Mặt nước phẳng lặng, tiếng ồn ngoài vịnh gần như mất hẳn, và đây là một trong những nơi dễ nhất để ngắm núi đá vôi ở ngang tầm mặt nước. Thỉnh thoảng có thể thấy khỉ trên vách đá.</p>
<h2>Hoạt động</h2>
<ul>
<li><strong>Chèo kayak</strong>: tự chèo qua hang và dạo quanh áng. Mặt nước kín gió nên không cần biết chèo trước.</li>
<li><strong>Đi đò nan</strong>: ngồi thuyền để người lái đò địa phương chèo. Phù hợp với trẻ em, người lớn tuổi và ai không muốn tự chèo.</li>
</ul>
<h2>Cách đến</h2>
<p>Tàu neo ở bến nổi gần cửa hang, nơi có sẵn kayak và đò nan. Du khách được phát áo phao trước khi xuống thuyền.</p>
<h2>Khi đến nơi</h2>
<p>Vòm hang thấp nên khi đi qua phải cúi người. Thuyền có qua được hay không, khoảng trống trên đầu còn bao nhiêu, đều phụ thuộc thủy triều. Buổi chiều có thể phải chờ một chút vì các đoàn lần lượt vào.</p>
<h2>Lưu ý khi tham quan</h2>
<ul>
<li>Bước từ bến xuống kayak hay đò cần giữ thăng bằng; đội tàu sẽ hỗ trợ.</li>
<li>Cho điện thoại vào túi chống nước và mặc đồ không ngại ướt.</li>
<li>Không cho khỉ ăn và không lại gần khỉ.</li>
</ul>
<h2>Thời tiết và mùa</h2>
<p>Gió mạnh, mưa to hoặc triều quá cao có thể khiến việc qua hang không an toàn; khi đó hoạt động sẽ được rút ngắn hoặc thay thế. Mùa bão, tàu trên vịnh có thể bị tạm dừng hoàn toàn.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Đảo Bồ Hòn, trung tâm vịnh Hạ Long'],
                ['label' => 'Loại hình', 'value' => 'Hang nước xuyên núi dẫn vào áng kín'],
                ['label' => 'Cách đến', 'value' => 'Kayak hoặc đò nan từ bến nổi'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 1 giờ trên mặt nước (ước tính)'],
                ['label' => 'Phụ thuộc', 'value' => 'Thủy triều và thời tiết trong ngày'],
            ],
            'faq' => [
                ['q' => 'Ở hang Luồn nên chèo kayak hay đi đò nan?', 'a' => 'Chọn kayak nếu bạn muốn tự khám phá và không ngại ướt một chút. Chọn đò nan nếu muốn được chèo giúp, đi cùng trẻ nhỏ hoặc muốn rảnh tay chụp ảnh.'],
                ['q' => 'Chưa chèo kayak bao giờ có đi được không?', 'a' => 'Được. Áng nước kín gió, quãng chèo ngắn. Đội tàu sẽ hướng dẫn trước và bắt buộc mặc áo phao.'],
                ['q' => 'Trẻ em có qua hang Luồn được không?', 'a' => 'Được, thường là ngồi đò nan cùng người lớn. Trẻ mặc áo phao suốt thời gian trên thuyền.'],
                ['q' => 'Green Ruby dự kiến ghé hang Luồn khi nào?', 'a' => 'Chiều ngày đầu tiên trong cả hai hành trình vịnh Hạ Long của Green Ruby 1, trước khi đến đảo Ti Tốp. Lịch trình có thể điều chỉnh trước khi tàu chính thức hoạt động, dự kiến từ tháng 2/2027.'],
            ],
        ],
    ],
    [
        'bay' => 'ha-long',
        'category' => 'village',
        'ord' => 4,
        'latitude' => 20.801367,
        'longitude' => 107.125097,
        'coords_verified' => true,
        'coords_source' => 'GPS of a geotagged photo of the village (Wikimedia Commons, David Stanley)',
        'image' => ['file' => 'cua-van-floating-village-ha-long-bay.jpg', 'link' => '/2026/10/24/cua-van-floating-village-ha-long-bay.jpg'],
        'image_credit' => 'Photo: David Stanley, CC BY 4.0, via Wikimedia Commons',
        'itineraries' => ['en' => [3], 'vi' => [8]],
        'sources' => [
            $unesco,
            ['title' => 'Wikipedia: Hạ Long Bay (fishing villages of the bay)', 'url' => 'https://en.wikipedia.org/wiki/H%E1%BA%A1_Long_Bay'],
            ['title' => 'Photo with GPS: Cua Van Floating Village, David Stanley (Wikimedia Commons)', 'url' => 'https://commons.wikimedia.org/wiki/File:Cua_Van_Floating_Village.jpg'],
        ],
        'editor_notes' => "Resettlement: copy says most families now live on land, without a year. Confirm the year (widely reported as 2014) and current number of households with Quang Ninh / Ha Long Bay Management Board before adding them. Not published: floating culture centre opening times, boat fees.",
        'en' => [
            'slug' => 'cua-van-floating-village',
            'name' => 'Cua Van Floating Village',
            'image_alt' => 'Floating houses and fishing boats moored below limestone cliffs at Cua Van, Ha Long Bay',
            'seo_title' => 'Cua Van Floating Village, Ha Long Bay: What to Know',
            'seo_description' => 'Cua Van is one of Ha Long Bay\'s traditional fishing villages. What a visit involves today, how you get around, respectful photography and Green Ruby\'s plan.',
            'summary' => 'One of the traditional fishing villages of Ha Long Bay, where houses, fish pens and boats float in a sheltered cove. Visits are by rowing boat, with a focus on how the community has lived on the water.',
            'content' => <<<'HTML'
<h2>Where it is</h2>
<p>Cua Van sits in a sheltered stretch of water in the eastern part of Ha Long Bay, further from the busiest cruise routes than Sung Sot or Ti Top. It is one of the bay's traditional fishing communities, alongside villages such as Ba Hang, Cong Tau and Vong Vieng.</p>
<h2>Why it is worth visiting</h2>
<p>For generations, families here lived on floating houses, fished and farmed fish and shellfish in pens beside their homes. Most families have since moved to homes on land, and the village is quieter than older photographs suggest. A visit is still one of the few ways to understand the bay as a place where people live and work, rather than only as scenery.</p>
<h2>Activities</h2>
<ul>
<li>A rowing-boat tour between the floating houses and fish farms, usually rowed by local residents.</li>
<li>Time with a guide to talk about fishing, aquaculture and how life on the water has changed.</li>
</ul>
<h2>How visitors reach it</h2>
<p>Larger boats stop at the edge of the village. You transfer to small rowing boats to move between the houses.</p>
<h2>What to expect on arrival</h2>
<p>This is a working and lived-in place, not an attraction built for visitors. Expect a slow pace, the smell of fish farming in places, and simple facilities.</p>
<h2>Respectful visiting</h2>
<ul>
<li>Ask before photographing people or the inside of homes.</li>
<li>Keep voices down and follow your rower's directions.</li>
<li>If you want to buy something, buy directly from residents.</li>
</ul>
<h2>Accessibility and weather</h2>
<p>Boarding the small rowing boats needs some balance; the crew helps. Visits depend on calm water and may be shortened in wind or rain, and cancelled when storms suspend sailings.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Eastern Ha Long Bay, in a sheltered cove'],
                ['label' => 'Type', 'value' => 'Traditional fishing village with floating houses'],
                ['label' => 'Access', 'value' => 'Rowing boat from the day boat'],
                ['label' => 'Typical visit', 'value' => 'About 1 to 1.5 hours (estimate)'],
                ['label' => 'Good to know', 'value' => 'A lived-in community: ask before taking photos'],
            ],
            'faq' => [
                ['q' => 'Do people still live in Cua Van floating village?', 'a' => 'Some do, but most families who once lived on the water have moved to homes on land. The village remains a working place for fishing and aquaculture.'],
                ['q' => 'How do you get around the village?', 'a' => 'By small rowing boat, usually rowed by local residents. Your larger boat waits at the edge of the village.'],
                ['q' => 'Is Cua Van on the 2 days 1 night cruise?', 'a' => 'No. On Green Ruby\'s current plan, Cua Van is visited by day boat on the second day of the Halong Immersion (3 days 2 nights) itinerary of Green Ruby 1. The plan is provisional until sailings begin, expected in February 2027.'],
            ],
        ],
        'vi' => [
            'slug' => 'lang-chai-cua-van',
            'name' => 'Làng chài Cửa Vạn',
            'image_alt' => 'Nhà bè và thuyền đánh cá neo dưới chân núi đá vôi ở làng chài Cửa Vạn, vịnh Hạ Long',
            'seo_title' => 'Làng chài Cửa Vạn, vịnh Hạ Long: tham quan thế nào?',
            'seo_description' => 'Làng chài Cửa Vạn trên vịnh Hạ Long hôm nay ra sao, tham quan bằng gì, lưu ý khi chụp ảnh người dân và lịch ghé dự kiến của Green Ruby.',
            'summary' => 'Một trong những làng chài truyền thống của vịnh Hạ Long, nơi nhà bè, lồng bè nuôi cá và thuyền neo trong một vụng kín gió. Du khách đi đò quanh làng để hiểu nếp sống trên mặt nước.',
            'content' => <<<'HTML'
<h2>Vị trí</h2>
<p>Cửa Vạn nằm trong một vụng nước kín gió ở phía đông vịnh Hạ Long, cách xa các tuyến đông đúc như Sửng Sốt hay Ti Tốp. Đây là một trong những làng chài truyền thống của vịnh, cùng với Ba Hang, Cống Tàu và Vông Viêng.</p>
<h2>Vì sao nên ghé</h2>
<p>Nhiều thế hệ ngư dân từng sống trên nhà bè, đánh bắt và nuôi cá, nuôi hải sản ngay cạnh nhà. Phần lớn các gia đình nay đã lên bờ sinh sống, nên làng yên ắng hơn nhiều so với những bức ảnh cũ. Dù vậy, đến Cửa Vạn vẫn là cách hiếm hoi để thấy vịnh Hạ Long là nơi con người sống và làm việc, chứ không chỉ là cảnh đẹp.</p>
<h2>Hoạt động</h2>
<ul>
<li>Ngồi đò nan len giữa nhà bè và lồng nuôi cá, thường do chính người dân trong làng chèo.</li>
<li>Nghe hướng dẫn viên kể về nghề cá, nghề nuôi trồng và đời sống trên mặt nước đã đổi thay ra sao.</li>
</ul>
<h2>Cách đến</h2>
<p>Tàu lớn dừng ở rìa làng, du khách chuyển sang đò nhỏ để đi giữa các nhà bè.</p>
<h2>Khi đến nơi</h2>
<p>Đây là nơi người dân sinh sống và làm ăn, không phải khu du lịch dựng lên cho khách. Nhịp sống chậm, có chỗ nặng mùi lồng bè, tiện nghi đơn giản.</p>
<h2>Ứng xử khi tham quan</h2>
<ul>
<li>Hỏi trước khi chụp ảnh người dân hoặc bên trong nhà.</li>
<li>Nói nhỏ và làm theo hướng dẫn của người chèo đò.</li>
<li>Nếu muốn mua gì, hãy mua trực tiếp từ người dân.</li>
</ul>
<h2>Tiếp cận và thời tiết</h2>
<p>Bước xuống đò nhỏ cần giữ thăng bằng, đội tàu sẽ hỗ trợ. Chuyến thăm cần mặt nước êm, có thể rút ngắn khi gió mưa và bị hủy khi tàu phải tạm dừng vì bão.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Phía đông vịnh Hạ Long, trong vụng kín gió'],
                ['label' => 'Loại hình', 'value' => 'Làng chài truyền thống với nhà bè'],
                ['label' => 'Cách đến', 'value' => 'Đò nan từ tàu ngày'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 1–1,5 giờ (ước tính)'],
                ['label' => 'Cần biết', 'value' => 'Nơi người dân sinh sống: hỏi trước khi chụp ảnh'],
            ],
            'faq' => [
                ['q' => 'Làng chài Cửa Vạn còn người ở không?', 'a' => 'Vẫn còn một số hộ, nhưng phần lớn các gia đình từng sống trên mặt nước đã lên bờ. Làng vẫn là nơi đánh bắt và nuôi trồng thủy sản.'],
                ['q' => 'Tham quan làng chài bằng gì?', 'a' => 'Bằng đò nan nhỏ, thường do người dân trong làng chèo. Tàu lớn chờ ở rìa làng.'],
                ['q' => 'Hành trình 2 ngày 1 đêm có ghé Cửa Vạn không?', 'a' => 'Không. Theo kế hoạch hiện tại, Cửa Vạn nằm trong ngày thứ hai của hành trình 3 ngày 2 đêm vịnh Hạ Long trên Green Ruby 1, đi bằng tàu ngày. Lịch trình có thể điều chỉnh trước khi tàu chính thức hoạt động, dự kiến từ tháng 2/2027.'],
            ],
        ],
    ],
    [
        'bay' => 'ha-long',
        'category' => 'cave',
        'ord' => 5,
        'latitude' => null,
        'longitude' => null,
        'coords_verified' => false,
        'coords_source' => null,
        'image' => null,
        'image_credit' => null,
        'itineraries' => ['en' => [3], 'vi' => [8]],
        'sources' => [$unesco],
        'editor_notes' => "Needs: verified coordinates, a genuine photo (none licensed found on Wikimedia Commons), and an official source for the archaeological finds (the copy only says traces of prehistoric habitation have been recorded, as in the published itinerary). Not published: steps, size, fees.",
        'en' => [
            'slug' => 'tien-ong-cave',
            'name' => 'Tien Ong Cave',
            'image_alt' => '',
            'seo_title' => 'Tien Ong Cave, Ha Long Bay: A Quieter Cave Visit',
            'seo_description' => 'Tien Ong Cave is a smaller, quieter cave in Ha Long Bay known for traces of prehistoric habitation. What a visit involves and how it fits Green Ruby\'s 3-day plan.',
            'summary' => 'A smaller cave in the quieter eastern part of Ha Long Bay, named after a rock formation likened to an old sage. It is also known for traces of prehistoric habitation recorded inside.',
            'content' => <<<'HTML'
<h2>Where it is</h2>
<p>Tien Ong Cave is in the eastern part of Ha Long Bay, away from the central cluster of Sung Sot, Luon and Ti Top. Boats that visit it usually combine it with the fishing village of Cua Van nearby.</p>
<h2>Why it is worth visiting</h2>
<p>Tien Ong means "fairy old man", after a formation that local stories liken to an elderly sage. The cave is smaller and far less busy than Sung Sot, which makes it easier to take your time. It is also of interest for its past: traces of prehistoric habitation have been recorded in the cave, a reminder that people lived among these islands thousands of years before tourism.</p>
<h2>How visitors reach it</h2>
<p>Visits are made from a day boat, which moors close to the cave. A short climb on steps and uneven ground leads to the entrance.</p>
<h2>What to expect on arrival</h2>
<p>Expect a compact cave with lit formations and a guide explaining its stories and history. Because fewer boats come this way, it is usually quiet.</p>
<h2>Accessibility and practical tips</h2>
<ul>
<li>Steps and uneven ground at the entrance: not suitable for wheelchairs.</li>
<li>Wear shoes with grip; surfaces inside can be damp.</li>
<li>Please do not touch the formations.</li>
</ul>
<h2>Weather and seasonal limits</h2>
<p>As with every stop in the bay, the visit depends on boats being allowed to sail. Day-boat excursions are the first to be cut short in strong wind or storm warnings.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Eastern Ha Long Bay, near Cua Van'],
                ['label' => 'Type', 'value' => 'Small limestone cave'],
                ['label' => 'Access', 'value' => 'Day boat, then a short climb on steps'],
                ['label' => 'Typical visit', 'value' => 'About 45 minutes (estimate)'],
                ['label' => 'Known for', 'value' => 'A quiet visit and traces of prehistoric habitation'],
            ],
            'faq' => [
                ['q' => 'How is Tien Ong Cave different from Sung Sot Cave?', 'a' => 'It is much smaller and sees far fewer visitors. Sung Sot impresses with scale; Tien Ong is a slower, quieter visit with a focus on the cave\'s history and legends.'],
                ['q' => 'Is Tien Ong Cave on the 2 days 1 night cruise?', 'a' => 'No. In Green Ruby\'s current plan it is part of the day-boat excursion on the second day of the Halong Immersion (3 days 2 nights) on Green Ruby 1, together with Cua Van. The plan is provisional until sailings begin, expected in February 2027.'],
                ['q' => 'Is it a difficult visit?', 'a' => 'Not usually. The climb to the entrance is short, but there are steps and uneven ground, so it is not suitable for wheelchairs.'],
            ],
        ],
        'vi' => [
            'slug' => 'hang-tien-ong',
            'name' => 'Hang Tiên Ông',
            'image_alt' => '',
            'seo_title' => 'Hang Tiên Ông, vịnh Hạ Long: hang nhỏ, yên tĩnh',
            'seo_description' => 'Hang Tiên Ông là hang nhỏ, ít khách ở phía đông vịnh Hạ Long, gắn với dấu tích cư trú thời tiền sử. Tham quan thế nào và nằm trong lịch trình nào của Green Ruby.',
            'summary' => 'Hang nhỏ ở vùng phía đông yên tĩnh của vịnh Hạ Long, được đặt tên theo khối thạch nhũ giống một ông tiên. Trong hang từng ghi nhận dấu tích cư trú của người tiền sử.',
            'content' => <<<'HTML'
<h2>Vị trí</h2>
<p>Hang Tiên Ông nằm ở phía đông vịnh Hạ Long, xa cụm Sửng Sốt, hang Luồn và Ti Tốp ở trung tâm. Tàu ghé hang thường kết hợp thăm làng chài Cửa Vạn gần đó.</p>
<h2>Vì sao nên ghé</h2>
<p>Hang mang tên Tiên Ông theo một khối thạch nhũ mà dân gian ví như ông tiên đang ngồi. So với Sửng Sốt, hang nhỏ hơn và vắng hơn nhiều, nên bạn có thể thong thả ngắm. Hang còn đáng chú ý vì quá khứ của nó: tại đây đã ghi nhận dấu tích cư trú của người tiền sử, cho thấy con người đã sống giữa các đảo đá này từ hàng nghìn năm trước khi có du lịch.</p>
<h2>Cách đến</h2>
<p>Du khách đến hang bằng tàu ngày, tàu neo gần cửa hang. Đi thêm một đoạn ngắn qua bậc và nền đá không bằng phẳng là tới cửa hang.</p>
<h2>Khi đến nơi</h2>
<p>Hang không lớn, có đèn chiếu sáng thạch nhũ, hướng dẫn viên kể về truyền thuyết và lịch sử của hang. Vì ít tàu qua lại nên thường khá yên tĩnh.</p>
<h2>Lưu ý khi tham quan</h2>
<ul>
<li>Lối vào có bậc và nền đá gồ ghề, không phù hợp xe lăn.</li>
<li>Nên đi giày đế bám, nền trong hang có chỗ ẩm ướt.</li>
<li>Không chạm tay vào thạch nhũ.</li>
</ul>
<h2>Thời tiết và mùa</h2>
<p>Như mọi điểm trên vịnh, chuyến thăm phụ thuộc việc tàu có được ra khơi hay không. Khi gió mạnh hoặc có cảnh báo bão, các chuyến tàu ngày thường bị cắt giảm đầu tiên.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Phía đông vịnh Hạ Long, gần Cửa Vạn'],
                ['label' => 'Loại hình', 'value' => 'Hang đá vôi nhỏ'],
                ['label' => 'Cách đến', 'value' => 'Tàu ngày, sau đó đi bộ ngắn lên bậc'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 45 phút (ước tính)'],
                ['label' => 'Đáng chú ý', 'value' => 'Yên tĩnh, có dấu tích cư trú thời tiền sử'],
            ],
            'faq' => [
                ['q' => 'Hang Tiên Ông khác hang Sửng Sốt thế nào?', 'a' => 'Hang Tiên Ông nhỏ hơn nhiều và ít khách hơn. Sửng Sốt gây ấn tượng bằng quy mô; Tiên Ông là chuyến thăm chậm rãi, yên tĩnh, thiên về lịch sử và truyền thuyết của hang.'],
                ['q' => 'Hành trình 2 ngày 1 đêm có ghé hang Tiên Ông không?', 'a' => 'Không. Theo kế hoạch hiện tại của Green Ruby, hang Tiên Ông nằm trong chuyến tàu ngày của ngày thứ hai, hành trình 3 ngày 2 đêm vịnh Hạ Long trên Green Ruby 1, cùng với làng chài Cửa Vạn. Lịch trình có thể điều chỉnh trước khi tàu chính thức hoạt động, dự kiến từ tháng 2/2027.'],
                ['q' => 'Đi hang Tiên Ông có vất vả không?', 'a' => 'Thường không. Đoạn lên cửa hang ngắn, nhưng có bậc và nền đá gồ ghề nên không phù hợp xe lăn.'],
            ],
        ],
    ],
    [
        'bay' => 'lan-ha',
        'category' => 'cave',
        'ord' => 6,
        'latitude' => null,
        'longitude' => null,
        'coords_verified' => false,
        'coords_source' => null,
        'image' => ['link' => '/2026/10/21/cheo-kayak-qua-hang-da-voi-vinh-lan-ha.jpg'],
        'image_credit' => null,
        'itineraries' => ['en' => [1, 2], 'vi' => [5, 7]],
        'sources' => [$unesco, $catBaMab],
        'editor_notes' => "Needs verified coordinates and a photo of this exact cave: the hero is the owner's photo of kayaking through a limestone cave in Lan Ha Bay (media library 2026-10-21), and its alt text says only that. Not published: passage lengths, tide thresholds.",
        'en' => [
            'slug' => 'dark-and-bright-cave',
            'name' => 'Dark & Bright Cave',
            'image_alt' => 'Kayakers paddling through a limestone cave arch in Lan Ha Bay',
            'seo_title' => 'Dark and Bright Cave, Lan Ha Bay: Kayaking Guide',
            'seo_description' => 'Dark and Bright Cave in Lan Ha Bay is explored by kayak or bamboo boat. How the visit works, tides, who it suits and when Green Ruby plans to stop.',
            'summary' => 'A pair of sea-level passages in Lan Ha Bay, one dim and enclosed, the other open to daylight, explored by kayak or bamboo boat. A calm first taste of the bay at water level.',
            'content' => <<<'HTML'
<h2>Where it is</h2>
<p>Dark &amp; Bright Cave is in Lan Ha Bay, the quieter waters off the east coast of Cat Ba Island. Lan Ha belongs to the Cat Ba Archipelago, part of the Ha Long Bay – Cat Ba Archipelago World Heritage Site since 2023 and a UNESCO Biosphere Reserve since 2004.</p>
<h2>Why it is worth visiting</h2>
<p>The name describes the experience. One passage is low and dim, and you paddle through half-light with the rock close overhead. The other opens to daylight, with the green of the cliffs reflected in still water. Because Lan Ha sees fewer boats than central Ha Long Bay, the visit usually feels unhurried.</p>
<h2>Activities</h2>
<ul>
<li><strong>Kayaking</strong> through the passages and along the cliffs.</li>
<li><strong>Bamboo boat</strong>, rowed by a local boatman, for those who prefer not to paddle.</li>
</ul>
<h2>How visitors reach it</h2>
<p>The ship anchors nearby and guests transfer by tender to where kayaks and bamboo boats are launched. Life jackets are provided.</p>
<h2>What to expect on arrival</h2>
<p>The guide gives a short briefing on the route and the tide before launching. In the dark passage you will need to keep your head low; the bright side is open and easy.</p>
<h2>Accessibility and practical tips</h2>
<ul>
<li>Getting into a kayak or bamboo boat needs some balance; the crew helps with boarding.</li>
<li>Bring a dry bag for your phone and expect to get a little wet.</li>
<li>Children usually travel by bamboo boat with an adult.</li>
</ul>
<h2>Weather and seasonal limits</h2>
<p>Whether the passages can be entered depends on the tide and the weather. In strong wind or heavy rain the activity may be changed, and storm warnings can suspend sailings.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Lan Ha Bay, east of Cat Ba Island'],
                ['label' => 'Type', 'value' => 'Two sea-level passages, one dim and one open'],
                ['label' => 'Access', 'value' => 'Kayak or bamboo boat'],
                ['label' => 'Typical visit', 'value' => 'About 1 to 1.5 hours (estimate)'],
                ['label' => 'Depends on', 'value' => 'Tide and weather on the day'],
            ],
            'faq' => [
                ['q' => 'Do I need to know how to kayak?', 'a' => 'No. The water is sheltered and the guide briefs everyone before setting off. If you prefer, you can take a bamboo boat instead.'],
                ['q' => 'Is the dark passage suitable for people who dislike enclosed spaces?', 'a' => 'It is short, but low and dim. If enclosed spaces bother you, the bamboo boat lets you stay on the open, bright side.'],
                ['q' => 'When does Green Ruby plan to visit Dark & Bright Cave?', 'a' => 'On the first afternoon of both Lan Ha Bay itineraries of Green Ruby 2. Times are provisional until sailings begin, expected in February 2027.'],
            ],
        ],
        'vi' => [
            'slug' => 'hang-sang-toi',
            'name' => 'Hang Sáng Tối',
            'image_alt' => 'Du khách chèo kayak qua vòm hang đá vôi trên vịnh Lan Hạ',
            'seo_title' => 'Hang Sáng Tối, vịnh Lan Hạ: kinh nghiệm chèo kayak',
            'seo_description' => 'Hang Sáng Tối trên vịnh Lan Hạ được khám phá bằng kayak hoặc đò nan. Cách tham quan, thủy triều, ai nên đi và lịch ghé dự kiến của Green Ruby.',
            'summary' => 'Hai lối hang sát mặt nước trên vịnh Lan Hạ, một bên tối và kín, một bên mở ra ánh sáng, khám phá bằng kayak hoặc đò nan. Cách nhẹ nhàng để làm quen với vịnh ở ngang tầm mặt nước.',
            'content' => <<<'HTML'
<h2>Vị trí</h2>
<p>Hang Sáng Tối nằm trên vịnh Lan Hạ, vùng nước yên tĩnh phía đông đảo Cát Bà. Lan Hạ thuộc quần đảo Cát Bà, một phần của Di sản Thiên nhiên Thế giới vịnh Hạ Long – quần đảo Cát Bà từ năm 2023 và Khu dự trữ sinh quyển thế giới từ năm 2004.</p>
<h2>Vì sao nên ghé</h2>
<p>Cái tên đã nói lên trải nghiệm. Một lối hang thấp và tối, thuyền đi trong ánh sáng lờ mờ, vòm đá ngay sát đầu. Lối còn lại mở ra ánh sáng, vách núi xanh in bóng trên mặt nước lặng. Lan Hạ ít tàu hơn trung tâm vịnh Hạ Long nên chuyến đi thường thong thả.</p>
<h2>Hoạt động</h2>
<ul>
<li><strong>Chèo kayak</strong> qua các lối hang và men theo vách đá.</li>
<li><strong>Đi đò nan</strong> do người lái đò địa phương chèo, cho ai không muốn tự chèo.</li>
</ul>
<h2>Cách đến</h2>
<p>Du thuyền neo gần đó, khách đi xuồng đến điểm xuống kayak và đò nan. Áo phao được phát trước khi xuất phát.</p>
<h2>Khi đến nơi</h2>
<p>Hướng dẫn viên phổ biến ngắn về lộ trình và thủy triều trước khi xuống nước. Trong lối hang tối cần cúi thấp người; phía hang sáng thì thoáng và dễ đi.</p>
<h2>Lưu ý khi tham quan</h2>
<ul>
<li>Bước xuống kayak hay đò cần giữ thăng bằng; đội tàu sẽ hỗ trợ.</li>
<li>Mang túi chống nước cho điện thoại và chuẩn bị tinh thần bị ướt một chút.</li>
<li>Trẻ em thường đi đò nan cùng người lớn.</li>
</ul>
<h2>Thời tiết và mùa</h2>
<p>Có vào được các lối hang hay không phụ thuộc thủy triều và thời tiết. Khi gió mạnh hoặc mưa to, hoạt động có thể thay đổi; khi có cảnh báo bão, tàu có thể phải tạm dừng.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Vịnh Lan Hạ, phía đông đảo Cát Bà'],
                ['label' => 'Loại hình', 'value' => 'Hai lối hang sát mặt nước, một tối một sáng'],
                ['label' => 'Cách đến', 'value' => 'Kayak hoặc đò nan'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 1–1,5 giờ (ước tính)'],
                ['label' => 'Phụ thuộc', 'value' => 'Thủy triều và thời tiết trong ngày'],
            ],
            'faq' => [
                ['q' => 'Chưa biết chèo kayak có đi được không?', 'a' => 'Được. Mặt nước kín gió và hướng dẫn viên sẽ hướng dẫn trước khi xuất phát. Nếu muốn, bạn có thể đi đò nan.'],
                ['q' => 'Người sợ không gian hẹp có nên vào hang tối không?', 'a' => 'Lối hang tối ngắn nhưng thấp và thiếu sáng. Nếu ngại không gian kín, bạn có thể đi đò nan và ở lại phía hang sáng thoáng đãng.'],
                ['q' => 'Green Ruby dự kiến ghé hang Sáng Tối khi nào?', 'a' => 'Chiều ngày đầu tiên trong cả hai hành trình vịnh Lan Hạ của Green Ruby 2. Giờ giấc có thể thay đổi trước khi tàu chính thức hoạt động, dự kiến từ tháng 2/2027.'],
            ],
        ],
    ],
    [
        'bay' => 'lan-ha',
        'category' => 'village',
        'ord' => 7,
        'latitude' => 20.800882,
        'longitude' => 107.040609,
        'coords_verified' => true,
        'coords_source' => 'GPS of a geotagged photo of the Viet Hai paddy fields (Wikimedia Commons, Jakub Hałun)',
        'image' => ['file' => 'viet-hai-village-cat-ba-paddy-fields.jpg', 'link' => '/2026/10/24/viet-hai-village-cat-ba-paddy-fields.jpg'],
        'image_credit' => 'Photo: Jakub Hałun, CC BY 4.0, via Wikimedia Commons',
        'itineraries' => ['en' => [1], 'vi' => [7]],
        'sources' => [
            $catBaMab,
            ['title' => 'Wikipedia: Cát Bà National Park (established 31 March 1986)', 'url' => 'https://en.wikipedia.org/wiki/C%C3%A1t_B%C3%A0_National_Park'],
            ['title' => 'Wikipedia (Vietnamese): Vườn quốc gia Cát Bà (communes incl. Việt Hải)', 'url' => 'https://vi.wikipedia.org/wiki/V%C6%B0%E1%BB%9Dn_qu%E1%BB%91c_gia_C%C3%A1t_B%C3%A0'],
            ['title' => 'Photo with GPS: Paddy fields in Việt Hải, Jakub Hałun (Wikimedia Commons)', 'url' => 'https://commons.wikimedia.org/wiki/File:Vi%E1%BB%87t_H%E1%BA%A3i,_C%C3%A1t_B%C3%A0_Island,_Vietnam,_20240131_1211_4613.jpg'],
        ],
        'editor_notes' => "Not published: distance from the pier to the village (sources vary), household numbers, buggy/bike fees. 2025 administrative changes renamed local units; copy avoids commune/district names.",
        'en' => [
            'slug' => 'viet-hai-village',
            'name' => 'Viet Hai Village',
            'image_alt' => 'Paddy fields and forested limestone hills in the valley of Viet Hai, Cat Ba Island',
            'seo_title' => 'Viet Hai Village, Cat Ba Island: How to Visit',
            'seo_description' => 'Viet Hai is a small farming village in a valley of Cat Ba National Park. How visitors get there by boat, bike or electric cart, what to expect and practical tips.',
            'summary' => 'A small farming village in a valley on Cat Ba Island, surrounded by forested limestone hills inside the Cat Ba National Park area. Visitors arrive by boat and continue on foot, by bike or by electric cart.',
            'content' => <<<'HTML'
<h2>Where it is</h2>
<p>Viet Hai lies in a valley on the eastern side of Cat Ba Island, facing Lan Ha Bay. The village is within the area of Cat Ba National Park, established in 1986, and the Cat Ba Archipelago has been a UNESCO Biosphere Reserve since 2004.</p>
<h2>Why it is worth visiting</h2>
<p>After a day or two on the water, Viet Hai is a change of pace: rice fields, vegetable gardens, scattered houses and steep green hills on every side. It shows a side of the region that cruise passengers rarely see, where life runs on farming rather than tourism.</p>
<h2>Activities</h2>
<ul>
<li>Walk or cycle the paved path between the fields and houses.</li>
<li>Ride an electric cart if you prefer to cover the distance without effort.</li>
<li>Stop for a meal of local dishes in the village.</li>
</ul>
<h2>How visitors reach it</h2>
<p>Boats land at a small pier near the mouth of the valley. From there a paved path leads inland to the village, used by walkers, bicycles and electric carts.</p>
<h2>What to expect on arrival</h2>
<p>The village is small and rural, with simple facilities. Expect heat and humidity in summer and mud after rain. Some homes welcome visitors; others are simply homes.</p>
<h2>Accessibility and practical tips</h2>
<ul>
<li>The path is mostly level, and electric carts make the visit possible for those who cannot walk far.</li>
<li>Bring a hat, sun protection, water and insect repellent.</li>
<li>Ask before photographing people, and keep to the path in fields.</li>
</ul>
<h2>Weather and seasonal limits</h2>
<p>The visit relies on the boat transfer from the bay, so strong wind or storm warnings can cancel it. The rice fields change with the seasons, green during the growing months and brown or flooded at other times.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Eastern Cat Ba Island, facing Lan Ha Bay'],
                ['label' => 'Type', 'value' => 'Farming village in a limestone valley'],
                ['label' => 'Access', 'value' => 'Boat to a small pier, then on foot, by bike or electric cart'],
                ['label' => 'Typical visit', 'value' => 'Half a day including lunch (estimate)'],
                ['label' => 'Protected area', 'value' => 'Cat Ba National Park area (est. 1986)'],
            ],
            'faq' => [
                ['q' => 'How do you get to Viet Hai village?', 'a' => 'By boat to a small pier, then along a paved path into the valley on foot, by bicycle or by electric cart. There is no need to drive across Cat Ba Island.'],
                ['q' => 'Is the walk to Viet Hai long?', 'a' => 'It is a gentle, mostly level route, but in summer heat it can feel long. Electric carts are the easy option.'],
                ['q' => 'Is Viet Hai suitable for children?', 'a' => 'Yes. The path is quiet and mostly level. Bring sun protection and insect repellent.'],
                ['q' => 'Which Green Ruby cruise visits Viet Hai?', 'a' => 'In the current plan, Viet Hai is visited by day boat on the second day of the Lan Ha Immersion (3 days 2 nights) on Green Ruby 2, with lunch in the village. It is not part of the 2 days 1 night route. The plan is provisional until sailings begin, expected in February 2027.'],
            ],
        ],
        'vi' => [
            'slug' => 'lang-viet-hai',
            'name' => 'Làng Việt Hải',
            'image_alt' => 'Ruộng lúa và núi đá vôi phủ rừng trong thung lũng Việt Hải, đảo Cát Bà',
            'seo_title' => 'Làng Việt Hải, Cát Bà: đi thế nào, có gì?',
            'seo_description' => 'Việt Hải là làng nhỏ trong thung lũng thuộc Vườn quốc gia Cát Bà. Cách đến bằng thuyền, đi bộ, xe đạp hay xe điện, nên chuẩn bị gì và cần biết gì.',
            'summary' => 'Ngôi làng nhỏ nằm trong thung lũng trên đảo Cát Bà, bốn bề núi đá phủ rừng, thuộc khu vực Vườn quốc gia Cát Bà. Du khách đến bằng thuyền rồi đi bộ, đạp xe hoặc đi xe điện vào làng.',
            'content' => <<<'HTML'
<h2>Vị trí</h2>
<p>Việt Hải nằm trong một thung lũng phía đông đảo Cát Bà, hướng ra vịnh Lan Hạ. Làng thuộc khu vực Vườn quốc gia Cát Bà, thành lập năm 1986; quần đảo Cát Bà là Khu dự trữ sinh quyển thế giới từ năm 2004.</p>
<h2>Vì sao nên ghé</h2>
<p>Sau một hai ngày trên mặt nước, Việt Hải mang lại nhịp sống khác hẳn: ruộng lúa, vườn rau, những nếp nhà lác đác và núi xanh dựng đứng xung quanh. Đây là góc ít khách du thuyền nhìn thấy, nơi đời sống vẫn gắn với ruộng vườn hơn là du lịch.</p>
<h2>Hoạt động</h2>
<ul>
<li>Đi bộ hoặc đạp xe trên con đường bê tông giữa đồng ruộng và nhà dân.</li>
<li>Đi xe điện nếu muốn vào làng mà không tốn sức.</li>
<li>Dùng bữa với các món địa phương trong làng.</li>
</ul>
<h2>Cách đến</h2>
<p>Thuyền cập một bến nhỏ ở cửa thung lũng. Từ bến có đường bê tông dẫn vào làng, dành cho người đi bộ, xe đạp và xe điện.</p>
<h2>Khi đến nơi</h2>
<p>Làng nhỏ và thuần nông, tiện nghi đơn giản. Mùa hè nóng ẩm, sau mưa đường có thể lầy. Có nhà đón khách, có nhà chỉ đơn giản là nơi người dân sinh sống.</p>
<h2>Lưu ý khi tham quan</h2>
<ul>
<li>Đường vào làng phần lớn bằng phẳng; xe điện giúp người không đi bộ xa được vẫn tham quan thoải mái.</li>
<li>Mang mũ, đồ chống nắng, nước uống và thuốc chống côn trùng.</li>
<li>Hỏi trước khi chụp ảnh người dân và không đi vào ruộng.</li>
</ul>
<h2>Thời tiết và mùa</h2>
<p>Chuyến thăm phụ thuộc vào thuyền đưa đón từ vịnh, nên gió lớn hoặc cảnh báo bão có thể khiến chuyến đi bị hủy. Cánh đồng đổi màu theo mùa vụ: xanh khi lúa đang lớn, nâu hoặc ngập nước vào những thời điểm khác.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Phía đông đảo Cát Bà, hướng ra vịnh Lan Hạ'],
                ['label' => 'Loại hình', 'value' => 'Làng nông nghiệp trong thung lũng núi đá'],
                ['label' => 'Cách đến', 'value' => 'Thuyền đến bến nhỏ, rồi đi bộ, xe đạp hoặc xe điện'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng nửa ngày kể cả bữa trưa (ước tính)'],
                ['label' => 'Khu bảo tồn', 'value' => 'Khu vực Vườn quốc gia Cát Bà (thành lập 1986)'],
            ],
            'faq' => [
                ['q' => 'Đến làng Việt Hải bằng cách nào?', 'a' => 'Đi thuyền đến một bến nhỏ, sau đó theo đường bê tông vào thung lũng bằng cách đi bộ, đạp xe hoặc đi xe điện. Không cần đi đường bộ xuyên đảo Cát Bà.'],
                ['q' => 'Đường vào Việt Hải có xa không?', 'a' => 'Đường khá bằng phẳng, dễ đi, nhưng trời nóng sẽ thấy xa hơn. Xe điện là lựa chọn nhẹ nhàng nhất.'],
                ['q' => 'Trẻ em đi Việt Hải có phù hợp không?', 'a' => 'Phù hợp. Đường vào làng yên tĩnh và phần lớn bằng phẳng. Nhớ mang đồ chống nắng và thuốc chống côn trùng.'],
                ['q' => 'Hành trình nào của Green Ruby ghé Việt Hải?', 'a' => 'Theo kế hoạch hiện tại, Việt Hải nằm trong chuyến tàu ngày của ngày thứ hai, hành trình 3 ngày 2 đêm vịnh Lan Hạ trên Green Ruby 2, có bữa trưa trong làng. Hành trình 2 ngày 1 đêm không ghé. Lịch trình có thể điều chỉnh trước khi tàu chính thức hoạt động, dự kiến từ tháng 2/2027.'],
            ],
        ],
    ],
    [
        'bay' => 'lan-ha',
        'category' => 'lagoon',
        'ord' => 8,
        'latitude' => null,
        'longitude' => null,
        'coords_verified' => false,
        'coords_source' => null,
        'image' => ['link' => '/2026/10/21/cheo-kayak-ao-ech-vinh-lan-ha.jpg'],
        'image_credit' => null,
        'itineraries' => ['en' => [2], 'vi' => [5]],
        'sources' => [$unesco, $catBaMab],
        'editor_notes' => "Needs verified coordinates. Hero is the owner's photo described in the media library as kayaking at Ao Ech (2026-10-21). The origin of the name (frog shape) is not confirmed and is left out.",
        'en' => [
            'slug' => 'ao-ech-lagoon',
            'name' => 'Ao Ech Lagoon',
            'image_alt' => 'Kayaking on calm water at Ao Ech lagoon in Lan Ha Bay',
            'seo_title' => 'Ao Ech Lagoon (Frog Lake), Lan Ha Bay: Kayak & Swim',
            'seo_description' => 'Ao Ech is a sheltered lagoon in Lan Ha Bay for early-morning kayaking and swimming. What to expect, who it suits, conditions and Green Ruby\'s plan.',
            'summary' => 'A sheltered stretch of water among limestone islets in Lan Ha Bay, used for kayaking and swimming when the sea is calm. Early morning is the quietest time.',
            'content' => <<<'HTML'
<h2>Where it is</h2>
<p>Ao Ech, often translated as Frog Lake or Frog Lagoon, is in Lan Ha Bay, among the islets off the east coast of Cat Ba Island. It is part of the Cat Ba Archipelago, which joined the Ha Long Bay World Heritage Site in 2023.</p>
<h2>Why it is worth visiting</h2>
<p>The lagoon is protected by limestone on most sides, so the water is usually flat even when the open bay is choppy. That makes it a good place for an unhurried paddle or a swim, with cliffs close by and few other boats, especially early in the day.</p>
<h2>Activities</h2>
<ul>
<li>Kayaking around the lagoon and along the cliffs.</li>
<li>Swimming near the boat when the crew confirms conditions are safe.</li>
</ul>
<h2>How visitors reach it</h2>
<p>The ship anchors close by. Kayaks are launched from the ship or from a tender, and swimmers enter the water from the boat.</p>
<h2>What to expect on arrival</h2>
<p>In the early morning the light is soft and the lagoon is quiet. Later in the day more boats arrive from Cat Ba and the Ha Long side.</p>
<h2>Accessibility and practical tips</h2>
<ul>
<li>Life jackets are required for kayaking. Swimmers should stay within the area the crew indicates.</li>
<li>Water temperature varies a lot through the year: warm from about May to October, cool in winter.</li>
<li>Anyone can stay on board and enjoy the view from deck.</li>
</ul>
<h2>Weather and seasonal limits</h2>
<p>Kayaking and swimming depend on wind, visibility and water conditions on the morning, and storm warnings can suspend sailings.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Lan Ha Bay, off the east coast of Cat Ba Island'],
                ['label' => 'Type', 'value' => 'Sheltered lagoon among limestone islets'],
                ['label' => 'Activities', 'value' => 'Kayaking, swimming in suitable conditions'],
                ['label' => 'Typical visit', 'value' => 'About 1.5 hours (estimate)'],
                ['label' => 'Best time', 'value' => 'Early morning, when it is quietest'],
            ],
            'faq' => [
                ['q' => 'Can you swim at Ao Ech?', 'a' => 'Yes, near the boat when the crew confirms conditions are safe. The water is warmest from late spring to autumn.'],
                ['q' => 'Is Ao Ech suitable for beginners?', 'a' => 'Yes. The lagoon is sheltered and usually calm, and life jackets are compulsory.'],
                ['q' => 'When does Green Ruby plan to visit Ao Ech?', 'a' => 'On the morning of the second day of the Lan Ha Escape (2 days 1 night) on Green Ruby 2, before brunch and the return to port. Times are provisional until sailings begin, expected in February 2027.'],
            ],
        ],
        'vi' => [
            'slug' => 'ao-ech',
            'name' => 'Ao Ếch',
            'image_alt' => 'Chèo kayak trên mặt nước phẳng lặng ở Ao Ếch, vịnh Lan Hạ',
            'seo_title' => 'Ao Ếch, vịnh Lan Hạ: chèo kayak và tắm biển',
            'seo_description' => 'Ao Ếch là vùng nước kín gió trên vịnh Lan Hạ, hợp để chèo kayak và tắm biển buổi sáng sớm. Có gì, ai nên đi, điều kiện thời tiết và lịch của Green Ruby.',
            'summary' => 'Vùng nước kín gió giữa các đảo đá vôi trên vịnh Lan Hạ, dùng để chèo kayak và tắm biển khi biển êm. Sáng sớm là lúc vắng và đẹp nhất.',
            'content' => <<<'HTML'
<h2>Vị trí</h2>
<p>Ao Ếch nằm trên vịnh Lan Hạ, giữa các đảo nhỏ ngoài khơi bờ đông đảo Cát Bà. Khu vực này thuộc quần đảo Cát Bà, được đưa vào Di sản Thiên nhiên Thế giới cùng vịnh Hạ Long từ năm 2023.</p>
<h2>Vì sao nên ghé</h2>
<p>Áng nước được núi đá che chắn gần hết các phía, nên mặt nước thường phẳng lặng kể cả khi ngoài vịnh có sóng. Đây là chỗ lý tưởng để thong thả chèo thuyền hay bơi một vòng, vách đá ở ngay bên cạnh và ít tàu qua lại, nhất là vào buổi sớm.</p>
<h2>Hoạt động</h2>
<ul>
<li>Chèo kayak quanh áng và men theo vách đá.</li>
<li>Bơi gần tàu khi đội tàu xác nhận điều kiện an toàn.</li>
</ul>
<h2>Cách đến</h2>
<p>Du thuyền neo gần Ao Ếch. Kayak được thả từ tàu hoặc từ xuồng, người bơi xuống nước ngay từ tàu.</p>
<h2>Khi đến nơi</h2>
<p>Sáng sớm ánh sáng dịu và mặt nước yên ắng. Càng về trưa càng có thêm tàu từ Cát Bà và phía Hạ Long kéo đến.</p>
<h2>Lưu ý khi tham quan</h2>
<ul>
<li>Bắt buộc mặc áo phao khi chèo kayak. Người bơi chỉ bơi trong khu vực đội tàu chỉ định.</li>
<li>Nhiệt độ nước thay đổi nhiều theo mùa: ấm từ khoảng tháng 5 đến tháng 10, mùa đông khá lạnh.</li>
<li>Ai không muốn xuống nước có thể ở lại ngắm cảnh trên boong.</li>
</ul>
<h2>Thời tiết và mùa</h2>
<p>Chèo kayak và tắm biển phụ thuộc gió, tầm nhìn và điều kiện mặt nước buổi sáng hôm đó; khi có cảnh báo bão, tàu có thể phải tạm dừng.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Vịnh Lan Hạ, ngoài khơi bờ đông đảo Cát Bà'],
                ['label' => 'Loại hình', 'value' => 'Áng nước kín gió giữa các đảo đá vôi'],
                ['label' => 'Hoạt động', 'value' => 'Chèo kayak, tắm biển khi điều kiện cho phép'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 1,5 giờ (ước tính)'],
                ['label' => 'Thời điểm đẹp', 'value' => 'Sáng sớm, lúc vắng nhất'],
            ],
            'faq' => [
                ['q' => 'Có tắm biển ở Ao Ếch được không?', 'a' => 'Được, bơi gần tàu khi đội tàu xác nhận an toàn. Nước ấm nhất từ cuối xuân đến mùa thu.'],
                ['q' => 'Người mới chèo kayak có đi Ao Ếch được không?', 'a' => 'Được. Áng nước kín gió, thường rất êm, và bắt buộc mặc áo phao.'],
                ['q' => 'Green Ruby dự kiến ghé Ao Ếch khi nào?', 'a' => 'Sáng ngày thứ hai của hành trình 2 ngày 1 đêm vịnh Lan Hạ trên Green Ruby 2, trước bữa brunch và khi tàu quay về bến. Giờ giấc có thể thay đổi trước khi tàu chính thức hoạt động, dự kiến từ tháng 2/2027.'],
            ],
        ],
    ],
];
