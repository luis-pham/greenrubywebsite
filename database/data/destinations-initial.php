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
        'editor_notes' => "EN and VI rewritten 2026-10-24 from the editorial briefs. Verified (Ha Long Bay Management Department): NE of Bo Hon, ~13 km from Tuan Chau, route no. 2, bamboo boat/kayak only, crouch at high tide, several hundred rhesus macaques; kayak/bamboo service in the Luon area. Estimates, not sourced: 45-60 min stop, 30-45 min on the water. Management Board hours: 7h30-17h30 (16/4-31/10), 7h30-17h00 (1/11-15/4), not shown yet. Official prices on the VI services page (updated 2024-11-09): kayak 50,000 VND/turn/2 people, bamboo boat 30,000 VND/person/turn; not published yet. Not published: tunnel length/height, tide thresholds.",
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
            'image_credit' => 'Ảnh: Jakub Hałun, CC BY 4.0, qua Wikimedia Commons',
            'seo_title' => 'Hang Luồn Hạ Long: Chèo Kayak, Đi Đò Nan & Kinh Nghiệm',
            'seo_description' => 'Hang Luồn ở đâu, có gì chơi? Khám phá trải nghiệm chèo kayak, đi đò nan qua hang, ngắm khỉ vàng và những lưu ý khi tham quan vịnh Hạ Long.',
            'summary' => 'Hang Luồn nằm trên đảo Bồ Hòn, giữa vịnh Hạ Long, nổi tiếng với lối đi xuyên qua vòm đá thấp dẫn vào một hồ nước được núi đá vôi bao quanh. Du khách có thể tự chèo kayak hoặc ngồi đò nan để khám phá khu vực bên trong hang, nơi đôi khi có thể bắt gặp những đàn khỉ sống trên vách núi.',
            'sources' => [
                ['title' => 'Ban Quản lý vịnh Hạ Long – Hang Luồn', 'url' => 'https://halongbay.com.vn/places/31-hang-luon'],
                ['title' => 'Ban Quản lý vịnh Hạ Long – Dịch vụ chèo đò, chèo kayak', 'url' => 'https://halongbay.com.vn/vi/p/94-dich-vu-cheo-do-cheo-kayak'],
                ['title' => 'UNESCO – Vịnh Hạ Long và Quần đảo Cát Bà', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'Wikimedia Commons – Ảnh Hang Luồn của Jakub Hałun', 'url' => 'https://commons.wikimedia.org/wiki/File:Hang_Lu%E1%BB%93n_Cave,_Ha_Long_Bay,_Vietnam,_20240128_1452_3806.jpg'],
            ],
            'content' => <<<'HTML'
<h2>Hang Luồn ở đâu?</h2>
<p>Hang Luồn nằm ở phía đông bắc đảo Bồ Hòn, thuộc khu vực trung tâm vịnh Hạ Long, cách cảng tàu khách quốc tế Tuần Châu khoảng 13 km đường thủy.</p>
<p>Đây là một điểm tham quan trên tuyến số 2 của vịnh Hạ Long, cùng với <a href="/vi/dia-diem-tham-quan/hang-sung-sot">hang Sửng Sốt</a> và <a href="/vi/dia-diem-tham-quan/dao-ti-top">đảo Ti Tốp</a>. Ba địa điểm này thường được kết hợp trong các chuyến tham quan vịnh trong ngày hoặc hành trình du thuyền nghỉ đêm.</p>
<p>Khác với hang Sửng Sốt, nơi du khách đi bộ qua những khoang hang rộng lớn, Hang Luồn là một đường hầm tự nhiên nằm sát mặt nước. Muốn vào bên trong, du khách phải đi bằng kayak hoặc đò nan, luồn qua vòm đá nối mặt vịnh với hồ nước phía trong.</p>
<h2>Hang Luồn có gì đặc biệt?</h2>
<p>Điều thú vị nhất ở Hang Luồn là khung cảnh thay đổi ngay sau khi đi qua cửa hang.</p>
<p>Nhìn từ ngoài vịnh, cửa hang giống một vòm đá thấp nằm dưới chân núi. Khi thuyền đi qua, phía trước mở ra một khoảng nước được bao quanh bởi những vách đá vôi cao, có cây xanh mọc trên các sườn núi.</p>
<p>Khu vực hồ khá kín gió, mặt nước thường êm hơn bên ngoài. Du khách có thể chèo thuyền dọc theo chân núi, quan sát những vách đá dựng đứng và các mảng cây mọc trong khe đá.</p>
<p>Đây cũng là nơi sinh sống của khỉ vàng. Nếu may mắn, bạn có thể bắt gặp chúng di chuyển trên vách núi hoặc giữa những tán cây. Tuy nhiên, khỉ là động vật hoang dã nên không phải chuyến tham quan nào cũng nhìn thấy.</p>
<p>Không có những khoang hang lớn hay hệ thống lối đi bộ như ở hang Sửng Sốt. Trải nghiệm chính tại Hang Luồn là ngồi trên mặt nước, đi qua một vòm đá tự nhiên rồi khám phá khoảng hồ nằm sâu bên trong đảo.</p>
<h2>Những hoạt động nên trải nghiệm tại Hang Luồn</h2>
<h3>Chèo kayak xuyên qua Hang Luồn</h3>
<p>Chèo kayak là một trong những hoạt động được nhiều du khách lựa chọn khi đến Hang Luồn.</p>
<p>Sau khi mặc áo phao và nghe hướng dẫn, bạn sẽ tự chèo thuyền từ khu vực bến nổi đến cửa hang. Vòm đá khá thấp nên ở một số thời điểm, người ngồi trên kayak cần cúi xuống khi đi qua.</p>
<p>Qua khỏi cửa hang, bạn có thể chèo chậm quanh hồ, ngắm những vách đá vôi và chụp ảnh trước khi quay trở ra theo lối cũ.</p>
<p>Quãng đường chèo không quá dài, mặt nước trong hồ thường tương đối êm nên người mới cũng có thể tham gia khi điều kiện an toàn cho phép. Tuy nhiên, bạn vẫn cần làm theo hướng dẫn và biết cách điều khiển thuyền cơ bản.</p>
<h3>Ngồi đò nan tham quan hồ nước</h3>
<p>Nếu không muốn tự chèo, bạn có thể chọn đi đò nan do người lái đò địa phương chèo.</p>
<p>Đò đưa khách qua vòm hang và đi một vòng trong khu vực hồ. Bạn chỉ cần ngồi trên thuyền để ngắm cảnh, chụp ảnh và quan sát những vách núi xung quanh.</p>
<p>Hình thức này phù hợp với những người không quen chèo kayak, muốn vận động ít hơn hoặc đi cùng gia đình có trẻ nhỏ, người lớn tuổi.</p>
<p>Dù vậy, việc bước xuống đò vẫn đòi hỏi giữ thăng bằng, và khả năng tham gia còn phụ thuộc vào điều kiện sức khỏe cũng như quy định an toàn của đơn vị vận hành.</p>
<h3>Quan sát khỉ vàng trên vách núi</h3>
<p>Khu vực đảo Bồ Hòn có những đàn khỉ vàng sinh sống tự nhiên. Chúng thường xuất hiện trên các mỏm đá, sườn núi hoặc giữa những tán cây gần mặt nước.</p>
<p>Khi đi thuyền trong hồ, đôi lúc bạn có thể nhìn thấy khỉ di chuyển dọc vách núi hoặc ngồi trên những cành cây.</p>
<p>Du khách nên quan sát từ xa, không gọi dụ, ném thức ăn hay tìm cách tiếp cận. Việc cho động vật hoang dã ăn có thể khiến chúng thay đổi tập tính và gây nguy hiểm cho cả người lẫn khỉ.</p>
<h2>Nên chèo kayak hay đi đò nan ở Hang Luồn?</h2>
<p>Cả hai hình thức đều đưa du khách qua cửa hang và tham quan hồ nước phía trong. Khác biệt chủ yếu nằm ở cách trải nghiệm.</p>
<p><strong>Chèo kayak</strong> phù hợp nếu bạn thích vận động, muốn tự điều khiển thuyền và chủ động quan sát cảnh vật theo tốc độ của mình. Bạn sẽ cần dùng sức tay để chèo và phối hợp với người ngồi cùng nếu đi kayak đôi.</p>
<p><strong>Đi đò nan</strong> phù hợp nếu bạn muốn thư thả ngắm cảnh, chụp ảnh hoặc không muốn mất sức chèo thuyền. Người lái đò sẽ điều khiển thuyền trong suốt chuyến tham quan.</p>
<p>Nếu chưa từng chèo kayak và không tự tin khi xuống nước, đò nan thường là lựa chọn đơn giản hơn. Dù chọn cách nào, bạn vẫn có thể khám phá phần hồ nằm phía sau vòm đá.</p>
<h2>Cách đi đến Hang Luồn</h2>
<p>Hang Luồn không có đường bộ nối với đất liền. Du khách thường đến đây bằng tàu tham quan vịnh Hạ Long hoặc du thuyền có lịch trình ghé Hang Luồn.</p>
<p>Các chuyến đi thường xuất phát từ cảng tàu khách quốc tế Tuần Châu hoặc những cảng được cấp phép tại khu vực Hạ Long. Xem thêm <a href="/vi/cam-nang/ha-noi-di-ha-long">hướng dẫn di chuyển từ Hà Nội đến Hạ Long</a>.</p>
<p>Khi đến gần Hang Luồn, tàu dừng tại khu vực bến nổi để du khách chuyển sang kayak hoặc đò nan. Nhân viên tại điểm tham quan hướng dẫn mặc áo phao, lên xuống thuyền và di chuyển qua cửa hang.</p>
<p>Hang Luồn nằm trong lịch trình dự kiến của du thuyền <a href="/vi/du-thuyen/green-ruby-1-1.html">Green Ruby 1</a>, với hoạt động chèo kayak hoặc tham quan bằng đò nan.</p>
<h2>Tham quan Hang Luồn mất bao lâu?</h2>
<p>Thông thường, du khách nên dành khoảng 45–60 phút cho điểm dừng tại Hang Luồn, bao gồm thời gian lên xuống thuyền, tham quan hồ và quay lại tàu.</p>
<p>Riêng thời gian chèo kayak hoặc ngồi đò thường khoảng 30–45 phút, tùy lượng khách, cách tổ chức của đơn vị vận hành và điều kiện trong ngày.</p>
<p>Vào những thời điểm đông khách, bạn có thể phải chờ một chút tại bến nổi để các đoàn lần lượt lên xuống thuyền.</p>
<p>Nếu đi theo tour hoặc du thuyền nghỉ đêm, nên tuân thủ giờ tập trung do hướng dẫn viên thông báo, tránh tự ý kéo dài thời gian tham quan.</p>
<h2>Khi đến Hang Luồn cần lưu ý gì?</h2>
<p>Cửa Hang Luồn khá thấp, đặc biệt vào những ngày thủy triều lên cao. Khi đi qua, bạn có thể phải cúi người để tránh chạm đầu vào trần đá.</p>
<p>Mực nước thay đổi theo thủy triều nên khoảng trống dưới vòm hang không phải lúc nào cũng giống nhau. Nếu nước lên quá cao hoặc thời tiết không đảm bảo, hoạt động đi thuyền qua hang có thể bị hạn chế hoặc tạm dừng.</p>
<p>Toàn bộ chuyến tham quan diễn ra trên mặt nước. Không có đường đi bộ xuyên hang, và du khách sẽ quay trở ra qua chính cửa hang đã đi vào.</p>
<p>Một số điều nên chuẩn bị:</p>
<ul>
<li>Mặc áo phao trong suốt thời gian chèo kayak hoặc ngồi đò.</li>
<li>Cho điện thoại, máy ảnh và đồ có giá trị vào túi chống nước.</li>
<li>Mặc quần áo thoải mái, không ngại bị nước bắn vào.</li>
<li>Không đứng lên hoặc nghiêng người quá nhiều khi thuyền đang di chuyển.</li>
<li>Làm theo hướng dẫn khi đi qua đoạn vòm đá thấp.</li>
<li>Không cho khỉ ăn hoặc cố tiếp cận khỉ trên vách núi.</li>
</ul>
<p>Tắm biển không phải hoạt động thông thường tại Hang Luồn. Du khách nên ở trên thuyền và không tự ý xuống nước.</p>
<h2>Người lớn tuổi và trẻ nhỏ có tham quan Hang Luồn được không?</h2>
<p>Trẻ em và người lớn tuổi vẫn có thể tham quan Hang Luồn nếu sức khỏe phù hợp và đáp ứng yêu cầu an toàn của đơn vị tổ chức.</p>
<p>Với trẻ nhỏ, đi đò nan thường thuận tiện hơn vì không phải tự chèo. Trẻ cần mặc áo phao đúng kích cỡ và có người lớn đi cùng theo quy định.</p>
<p>Người lớn tuổi có thể lựa chọn đò nan để hạn chế vận động. Tuy nhiên, việc bước từ bến nổi xuống thuyền đòi hỏi giữ thăng bằng, nhất là khi có sóng hoặc mặt bến chuyển động.</p>
<p>Đối với người sử dụng xe lăn hoặc gặp khó khăn khi di chuyển, hoạt động này có thể không phù hợp do điều kiện lên xuống thuyền. Bạn nên trao đổi trước với đơn vị tổ chức để được tư vấn.</p>
<h2>Nên đi Hang Luồn vào tháng mấy?</h2>
<p>Hang Luồn có thể tham quan quanh năm, tùy điều kiện thời tiết, thủy triều và hoạt động của tàu du lịch trên vịnh.</p>
<p>Từ khoảng tháng 4 đến tháng 10, thời tiết thường ấm hơn, thuận lợi cho những hoạt động ngoài trời như chèo kayak. Tuy nhiên, mùa hè cũng có thể xuất hiện mưa lớn, gió mạnh hoặc bão khiến lịch trình bị thay đổi.</p>
<p>Từ tháng 11 đến tháng 3, thời tiết mát hoặc lạnh hơn. Nếu đi đò nan vào ngày nhiều gió, bạn nên mang thêm áo khoác mỏng.</p>
<p>Riêng với Hang Luồn, thủy triều là yếu tố quan trọng không kém thời tiết. Vì cửa hang nằm thấp sát mặt nước, mực nước dâng cao có thể khiến thuyền không đủ khoảng trống để đi qua an toàn.</p>
<p>Đơn vị vận hành và nhân viên tại điểm tham quan sẽ căn cứ vào điều kiện thực tế để quyết định có tổ chức hoạt động hay không. Xem thêm <a href="/vi/blog/hanh-trinh/di-ha-long-mua-nao-dep.html">du lịch Hạ Long mùa nào đẹp nhất</a>.</p>
<h2>Tham quan Hang Luồn cùng Green Ruby Cruises</h2>
<p>Hang Luồn nằm trong hai hành trình dự kiến của du thuyền Green Ruby 1 trên vịnh Hạ Long.</p>
<p><strong><a href="/vi/hanh-trinh/2-ngay-1-dem-mot-thoang-ha-long-1-9.html">2 ngày 1 đêm – Một Thoáng Hạ Long</a></strong><br>Hành trình nghỉ đêm kết hợp chèo kayak hoặc đi đò nan tại Hang Luồn, tham quan đảo Ti Tốp và khám phá hang Sửng Sốt.</p>
<p><strong><a href="/vi/hanh-trinh/3-ngay-2-dem-dau-an-ha-long-1-8.html">3 ngày 2 đêm – Dấu Ấn Hạ Long</a></strong><br>Hành trình dài hơn với thêm một ngày khám phá các địa điểm khác trên vịnh, bên cạnh những điểm tham quan quen thuộc của tuyến số 2.</p>
<p><em>Các điểm dừng trên thuộc lịch trình dự kiến của Green Ruby 1. Thời gian và hoạt động thực tế sẽ được xác nhận khi tàu đi vào vận hành, đồng thời có thể thay đổi theo thời tiết, thủy triều và quy định của cơ quan quản lý.</em></p>
<h2>Điểm đến gần và liên quan</h2>
<h3><a href="/vi/dia-diem-tham-quan/hang-sung-sot">Hang Sửng Sốt</a></h3>
<p>Nằm cùng đảo Bồ Hòn với Hang Luồn, nổi tiếng với những khoang hang rộng, nhiều khối thạch nhũ và lối đi bộ xuyên núi.</p>
<h3><a href="/vi/dia-diem-tham-quan/dao-ti-top">Đảo Ti Tốp</a></h3>
<p>Điểm dừng có bãi tắm nhỏ dưới chân đảo và cầu thang lên đỉnh ngắm toàn cảnh vịnh Hạ Long.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Đảo Bồ Hòn, vịnh Hạ Long, Quảng Ninh'],
                ['label' => 'Khoảng cách', 'value' => 'Khoảng 13 km từ cảng Tuần Châu'],
                ['label' => 'Tuyến tham quan', 'value' => 'Tuyến số 2 vịnh Hạ Long'],
                ['label' => 'Đặc điểm', 'value' => 'Hang nước xuyên núi dẫn vào hồ kín'],
                ['label' => 'Hoạt động', 'value' => 'Chèo kayak, đi đò nan, ngắm cảnh'],
                ['label' => 'Cách đến', 'value' => 'Tàu tham quan hoặc du thuyền'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 45–60 phút'],
                ['label' => 'Đi bộ xuyên hang', 'value' => 'Không'],
                ['label' => 'Động vật hoang dã', 'value' => 'Có thể bắt gặp khỉ vàng'],
                ['label' => 'Điều kiện tham quan', 'value' => 'Phụ thuộc thời tiết và thủy triều'],
            ],
            'faq' => [
                ['q' => 'Hang Luồn có gì đặc biệt?', 'a' => 'Hang Luồn là một đường hầm đá vôi nằm sát mặt nước, nối vịnh Hạ Long với một hồ nước được núi đá bao quanh. Du khách khám phá hang bằng kayak hoặc đò nan thay vì đi bộ như ở những hang động lớn.'],
                ['q' => 'Ở Hang Luồn nên chèo kayak hay đi đò nan?', 'a' => 'Nếu thích vận động và muốn tự chèo thuyền, bạn nên chọn kayak. Nếu muốn ngồi thư giãn, chụp ảnh và không cần tự chèo, đò nan sẽ phù hợp hơn. Cả hai hình thức đều tham quan cùng khu vực hồ phía trong hang.'],
                ['q' => 'Chưa chèo kayak bao giờ có tham gia được không?', 'a' => 'Có thể. Tuyến chèo tại Hang Luồn tương đối ngắn và hồ thường kín gió, phù hợp với nhiều người mới bắt đầu. Tuy nhiên, bạn cần mặc áo phao, nghe hướng dẫn và chỉ tham gia khi điều kiện an toàn cho phép.'],
                ['q' => 'Trẻ em có qua Hang Luồn được không?', 'a' => 'Trẻ em có thể tham gia đi đò nan nếu đáp ứng quy định về độ tuổi, áo phao và người đi kèm của đơn vị vận hành. Đối với chèo kayak, cần kiểm tra điều kiện cụ thể trước chuyến đi.'],
                ['q' => 'Có thể đi bộ vào Hang Luồn không?', 'a' => 'Không. Phần nền hang ngập nước nên du khách phải đi bằng kayak hoặc đò nan để qua cửa hang và vào hồ bên trong.'],
                ['q' => 'Tham quan Hang Luồn mất bao lâu?', 'a' => 'Thời gian cho cả điểm dừng thường khoảng 45–60 phút, trong đó hoạt động trên mặt nước có thể kéo dài khoảng 30–45 phút. Thời gian thực tế tùy lịch trình và lượng khách trong ngày.'],
                ['q' => 'Hang Luồn có khỉ không?', 'a' => 'Có. Khu vực này là nơi sinh sống của khỉ vàng. Du khách đôi khi có thể thấy chúng trên vách đá hoặc giữa các tán cây, nhưng không phải lúc nào khỉ cũng xuất hiện.'],
                ['q' => 'Green Ruby dự kiến ghé Hang Luồn khi nào?', 'a' => 'Theo lịch trình dự kiến của Green Ruby 1, du khách sẽ tham quan Hang Luồn vào khoảng 15:30 ngày đầu tiên trong cả hai hành trình 2 ngày 1 đêm và 3 ngày 2 đêm. Thời gian có thể điều chỉnh theo thủy triều, thời tiết và yêu cầu vận hành.'],
            ],
        ],
    ],
    [
        'bay' => 'ha-long',
        'category' => 'village',
        'ord' => 4,
        'latitude' => 20.800278,
        'longitude' => 107.124333,
        'coords_verified' => true,
        'coords_source' => 'Ha Long Bay Management Board, Cua Van page: 20°48\'1.00"N, 107°7\'27.60"E (photo GPS 20.801367, 107.125097 was ~150 m away)',
        'image' => ['file' => 'cua-van-floating-village-ha-long-bay.jpg', 'link' => '/2026/10/24/cua-van-floating-village-ha-long-bay.jpg'],
        'image_credit' => 'Photo: David Stanley, CC BY 4.0, via Wikimedia Commons',
        'itineraries' => ['en' => [3], 'vi' => [8]],
        'sources' => [
            $unesco,
            ['title' => 'Wikipedia: Hạ Long Bay (fishing villages of the bay)', 'url' => 'https://en.wikipedia.org/wiki/H%E1%BA%A1_Long_Bay'],
            ['title' => 'Photo with GPS: Cua Van Floating Village, David Stanley (Wikimedia Commons)', 'url' => 'https://commons.wikimedia.org/wiki/File:Cua_Van_Floating_Village.jpg'],
        ],
        'editor_notes' => "VI rewritten 2026-10-24 from the editorial brief. Verified: route 3, ~20 km from Tuan Chau, resettlement from 2014, Centre opened 19/5/2006 (Management Board Cua Van page); Centre closed to visitors from 25/4/2024 (Board notice via Nguoi Quan Sat 18/4/2024); Yagi damage 9/2024, restoration planned 2026 (Bao Quang Ninh); giao duyen singing resumed (Quang Ninh portal 17/3/2026). EN rewritten 2026-10-24 too (2014 relocation, Centre closure from VTV 15/4/2024, giao duyen page). Old note: Resettlement: copy says most families now live on land, without a year. Confirm the year (widely reported as 2014) and current number of households with Quang Ninh / Ha Long Bay Management Board before adding them. Not published: floating culture centre opening times, boat fees.",
        'en' => [
            'slug' => 'cua-van-floating-village',
            'name' => 'Cua Van Floating Village',
            'image_alt' => 'Floating houses and fishing boats moored below limestone cliffs at Cua Van, Ha Long Bay',
            'seo_title' => 'Cua Van Floating Village, Ha Long Bay: History & Visiting Guide',
            'seo_description' => 'Discover Cua Van Floating Village in Ha Long Bay. Learn about its fishing heritage, bamboo boat tours, the 2014 relocation and what visitors can see today.',
            'summary' => 'Cua Van Floating Village is a former fishing community tucked between the limestone islands of Ha Long Bay. For generations, families lived in houses built on floating rafts and made their living from the sea. Today, visitors can explore the sheltered cove by bamboo boat or kayak and learn about a way of life that has largely moved ashore.',
            'sources' => [
                ['title' => 'Ha Long Bay Management Department – Cua Van Floating Cultural Centre', 'url' => 'https://halongbay.com.vn/en/places/37-cua-van-floating-cutural-center'],
                ['title' => 'VTV – Suspension of Visits to Cua Van Floating Cultural Centre', 'url' => 'https://vtv.vn/doi-song/tam-dung-don-khach-toi-trung-tam-van-hoa-noi-cua-van-tren-vinh-ha-long-20240415083201442.htm'],
                ['title' => 'Ha Long Bay Management Department – Preserving Giao Duyen Folk Singing', 'url' => 'https://halongbay.com.vn/index.php/en/p/413-tiep-tuc-trien-khai-tap-luyen-som-dua-hat-giao-duyen-tro-lai-bieu-dien-phuc-vu-khach-tham-quan-vinh-ha-long'],
                ['title' => 'UNESCO World Heritage Centre – Ha Long Bay–Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'Wikimedia Commons – Cua Van Floating Village, photograph by David Stanley', 'url' => 'https://commons.wikimedia.org/wiki/File:Cua_Van_Floating_Village.jpg'],
            ],
            'content' => <<<'HTML'
<h2>Where is Cua Van Floating Village?</h2>
<p>Cua Van lies in a sheltered cove in Ha Long Bay, approximately 20 km by water from Tuan Chau International Marina.</p>
<p>The village is surrounded by limestone islands that provide natural protection from wind and waves. This made the area a suitable place for fishing families to anchor their boats, build floating homes and raise seafood in fish pens.</p>
<p>Cua Van is part of Ha Long Bay’s sightseeing Route 3, farther from the main cruise stops around <a href="/destinations/sung-sot-cave">Sung Sot Cave</a>, <a href="/destinations/luon-cave">Luon Cave</a> and <a href="/destinations/ti-top-island">Ti Top Island</a> on Route 2.</p>
<p>Because of its location, Cua Van is more commonly visited on longer excursions and overnight cruises than on short sightseeing trips.</p>
<h2>What makes Cua Van Floating Village special?</h2>
<p>Cua Van tells a different story about Ha Long Bay.</p>
<p>Most visitors come to the bay for its limestone islands, caves and beaches. At Cua Van, the focus is on the people who once made their homes among those islands.</p>
<p>For generations, fishing families lived in wooden houses supported by floating rafts. Small boats were their main means of transport, and daily life revolved around fishing, maintaining nets and tending seafood pens beside their homes.</p>
<p>Children grew up on the water. Neighbours travelled between houses by boat, and work depended on the tides, weather and fishing grounds.</p>
<p>That life has changed considerably.</p>
<p>In 2014, the authorities relocated the bay’s floating-village communities to homes on the mainland, improving access to schools, healthcare and other essential services.</p>
<p>As a result, Cua Van is no longer the busy residential village shown in many older photographs. Some floating structures remain for conservation and tourism, and people with connections to the former community continue to work on the bay.</p>
<p>A visit today offers a chance to understand this history, rather than observe an unchanged fishing village.</p>
<h2>The history of Cua Van Fishing Village</h2>
<p>Cua Van was one of several traditional fishing settlements in Ha Long Bay, alongside communities such as Ba Hang, Cong Tau and Vung Vieng.</p>
<p>Life on the water required practical knowledge passed down through generations. Families learned where to fish, how to maintain boats and nets, and how to protect their homes when the weather changed.</p>
<p>Fishing and aquaculture provided a livelihood, while small rowing boats connected households across the sheltered cove.</p>
<p>Living offshore also brought difficulties. Access to education, medical care and basic services was limited, particularly during bad weather.</p>
<p>The relocation programme in 2014 marked a turning point. Former residents settled on land, and many moved into different kinds of work. Some remained connected to the sea through fishing, boat services and tourism.</p>
<p>Today, the floating houses and remaining structures offer a glimpse of the village’s past, while local guides and boat operators help keep its stories alive.</p>
<h2>Things to do at Cua Van Floating Village</h2>
<h3>Take a bamboo boat ride around the village</h3>
<p>A bamboo boat is one of the easiest ways to explore Cua Van.</p>
<p>Visitors transfer from their cruise boat to a smaller rowing boat, usually operated by someone from the local community.</p>
<p>The boat moves slowly through the sheltered water, passing floating structures and areas associated with the former village.</p>
<p>Along the way, you can look at the limestone cliffs surrounding the cove and see how the location offered protection for families who once lived here.</p>
<p>Depending on the tour, your guide or boat operator may explain how people built their floating homes, fished for a living and adapted to life on the water.</p>
<p>The ride requires little physical effort, making it a suitable choice for visitors who prefer a relaxed experience.</p>
<h3>Kayak through the sheltered cove</h3>
<p>Kayaking is another way to explore Cua Van, where the activity is available and conditions allow.</p>
<p>You can paddle through designated areas of the cove, following the route and safety instructions provided by local operators.</p>
<p>The surrounding limestone islands help shelter the water, although wind and weather can still affect kayaking conditions.</p>
<p>Keep a safe distance from floating structures, working boats and areas that are not open to visitors.</p>
<p>If you are new to kayaking or would rather focus on the scenery, a bamboo boat may be the more comfortable choice.</p>
<h3>Learn about fishing traditions and local culture</h3>
<p>Cua Van is associated with the fishing traditions of Ha Long Bay, including the songs once shared among the village’s residents.</p>
<p>One of these traditions is <em>hat giao duyen</em>, a form of Vietnamese call-and-response folk singing, often performed by men and women exchanging verses.</p>
<p>The songs reflect relationships, everyday life and the experiences of people who lived and worked on the water.</p>
<p>Efforts to preserve and revive this tradition have continued, including performances associated with Cua Van.</p>
<p>However, cultural performances are not necessarily included in every visit. If you would like to hear traditional singing, check with your cruise operator about current arrangements.</p>
<h3>Photograph the floating village and limestone scenery</h3>
<p>Cua Van sits among steep limestone islands, with sheltered water and scattered floating structures.</p>
<p>From a small boat, you can photograph the wider cove, the remaining raft houses and the cliffs rising behind them.</p>
<p>Some older images of Cua Van show a much larger floating community than exists today. Keep that difference in mind when comparing historical photographs with the present-day village.</p>
<p>If you want to photograph boat operators or people working on the water, ask permission first. Do not board floating houses or other structures unless visitors are specifically allowed to enter.</p>
<h2>Do people still live at Cua Van Floating Village?</h2>
<p>This is one of the most important things to know before visiting.</p>
<p>Cua Van was once home to families who lived permanently on floating rafts. In 2014, the authorities relocated the fishing communities of Ha Long Bay to the mainland.</p>
<p>Today, Cua Van is no longer a large residential community on the water.</p>
<p>The people you meet may include boat operators, former residents, their families, conservation staff and others involved in tourism.</p>
<p>Some floating houses have been preserved to represent the village’s history, but visitors should not expect to see the same everyday household activities shown in older travel photographs.</p>
<p>The experience is now primarily about the area’s history, cultural heritage and the surrounding landscape.</p>
<h2>Cua Van Floating Cultural Centre</h2>
<p>The Cua Van Floating Cultural Centre opened in 2006 to help preserve and explain the heritage of Ha Long Bay’s fishing communities.</p>
<p>Its exhibitions included photographs, objects and information about traditional fishing, floating homes and everyday life on the water.</p>
<p>The centre also supported cultural activities, including traditional folk singing.</p>
<p>However, it suspended visitor access on 25 April 2024 for maintenance and repairs. The facilities were subsequently damaged by Typhoon Yagi in September 2024.</p>
<p>As of the latest information reviewed for this article, the centre’s reopening has not been confirmed.</p>
<p>Visitors may still be able to explore the surrounding village area by bamboo boat or kayak, but this should not be confused with access to the cultural centre itself.</p>
<h2>How to get to Cua Van Floating Village</h2>
<p>Cua Van is accessible only by boat.</p>
<p>Most visitors reach the area on organised Ha Long Bay tours or overnight cruises departing from Tuan Chau or another authorised passenger port. See <a href="/guides/hanoi-to-halong-bay">how to get from Hanoi to Halong Bay</a>.</p>
<p>Because the village is on sightseeing Route 3, not every Ha Long Bay cruise includes it.</p>
<p>Once in the area, passengers usually transfer from the main sightseeing boat to smaller bamboo rowing boats or kayaks.</p>
<p>The route, activities and time available depend on the tour operator and local conditions.</p>
<p>If Cua Van is one of the places you particularly want to visit, check that it appears by name in your cruise itinerary before booking.</p>
<h2>How long should you spend at Cua Van?</h2>
<p>Allow approximately 60–90 minutes for a typical visit around Cua Van, depending on the activities included.</p>
<p>A straightforward bamboo boat ride may take less time, while a programme with cultural interpretation or other activities may take longer.</p>
<p>This estimate does not include travelling to the village from the harbour or from other sightseeing stops.</p>
<p>On an overnight cruise, the duration will depend on the ship’s published schedule and the activities available on the day.</p>
<h2>What to expect when you arrive</h2>
<p>Cua Van is generally quieter than the busiest sightseeing areas of Ha Long Bay, although tour groups may still arrive at similar times.</p>
<p>The visit takes place mainly on the water, where you can see remaining floating structures and the limestone islands surrounding the cove.</p>
<p>The facilities are relatively simple. There may be working boats or aquaculture equipment nearby, and some areas may carry the ordinary smells associated with fishing and seafood production.</p>
<p>Importantly, Cua Van is not a recreated village with residents performing everyday life for tourists. It is a former community whose history is now preserved and interpreted through the site and local tourism activities.</p>
<p>Understanding this before arriving can make the visit more meaningful.</p>
<h2>Visiting respectfully</h2>
<p>Cua Van’s history belongs to the families who once lived here and the people who continue to work around the village.</p>
<p>A few things worth remembering:</p>
<ul>
<li>Ask permission before taking close-up photographs of people.</li>
<li>Do not board floating houses or working rafts without permission.</li>
<li>Wear the provided life jacket during boat or kayak activities.</li>
<li>Avoid dropping rubbish into the water.</li>
<li>Keep noise to a reasonable level around people working.</li>
<li>Follow your boat operator’s instructions, especially when boarding and disembarking.</li>
</ul>
<p>If you are interested in the village’s history, ask your guide about the 2014 relocation and how former residents have adapted to life on the mainland.</p>
<h2>Can children and older visitors visit Cua Van?</h2>
<p>A bamboo boat ride can be a good option for families because passengers do not need to paddle.</p>
<p>However, transferring from a larger boat to a small rowing boat requires some balance and care.</p>
<p>Children should wear properly fitted life jackets and be accompanied by adults according to the operator’s rules.</p>
<p>Older visitors or anyone with limited mobility should ask about boarding assistance before the trip.</p>
<p>Kayaking may have additional age, fitness or safety requirements. Small rowing boats are not necessarily wheelchair-accessible.</p>
<h2>Best time to visit Cua Van Floating Village</h2>
<p>Cua Van can be visited throughout the year when weather and cruise operations allow.</p>
<p>From April to October, the warmer weather is generally comfortable for outdoor activities such as kayaking. However, summer also brings periods of heavy rain, strong winds and tropical storms.</p>
<p>From November to March, conditions are often cooler. A light jacket can be useful when sitting in an open rowing boat, especially on windy days.</p>
<p>The cove is sheltered by limestone islands, but strong winds, heavy rain or official weather warnings can still affect boat operations.</p>
<p>Tours may be shortened, changed or cancelled when conditions are unsafe. See <a href="/blog/travel-tips/best-time-to-visit-halong-bay.html">the best time to visit Halong Bay</a> for month-by-month conditions.</p>
<h2>Visit Cua Van with Green Ruby Cruises</h2>
<p>Cua Van Floating Village is included in the planned 3-day/2-night Ha Long Bay itinerary aboard <a href="/cruise/green-ruby-1-3.html">Green Ruby 1</a>.</p>
<p><strong><a href="/itinerary/the-halong-immersion-3-days-2-nights-3-3.html">The Halong Immersion – 3 Days 2 Nights</a></strong><br>The second day takes guests beyond the main sightseeing route on a dedicated day boat, with planned visits to Cua Van Floating Village, <a href="/destinations/tien-ong-cave">Tien Ong Cave</a> and a pearl farm.</p>
<p>Cua Van provides a different perspective on Ha Long Bay, connecting its limestone scenery with the history of the fishing communities who once made their homes here.</p>
<p><em>These are planned stops. Final timings and activities will be confirmed when Green Ruby’s sailings begin and may change due to weather, local regulations or operational requirements.</em></p>
<h2>Nearby places and related experiences</h2>
<h3><a href="/destinations/tien-ong-cave">Tien Ong Cave</a></h3>
<p>An archaeological cave in Ha Long Bay, known for evidence of prehistoric human habitation and its limestone formations.</p>
<h3><a href="/destinations/sung-sot-cave">Sung Sot Cave</a></h3>
<p>One of Ha Long Bay’s best-known limestone caves, with large chambers, stalactites and stalagmites, and a walking route through the island.</p>
<h3><a href="/destinations/ti-top-island">Ti Top Island</a></h3>
<p>A small island with a crescent-shaped beach and a steep stairway leading to a viewpoint overlooking Ha Long Bay.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Ha Long Bay, Quang Ninh Province, Vietnam'],
                ['label' => 'Distance', 'value' => 'Approximately 20 km by water from Tuan Chau Marina'],
                ['label' => 'Sightseeing route', 'value' => 'Ha Long Bay Route 3'],
                ['label' => 'Known for', 'value' => 'Traditional floating-village history and fishing heritage'],
                ['label' => 'Main activities', 'value' => 'Bamboo boat rides, kayaking and cultural interpretation'],
                ['label' => 'Access', 'value' => 'By sightseeing boat or cruise, followed by a smaller boat'],
                ['label' => 'Typical visit', 'value' => 'Approximately 60–90 minutes'],
                ['label' => 'Former residents', 'value' => 'Fishing communities relocated ashore in 2014'],
                ['label' => 'Cultural centre', 'value' => 'Visitor access suspended; reopening not confirmed'],
                ['label' => 'Good to know', 'value' => 'The village is no longer a permanent residential community'],
            ],
            'faq' => [
                ['q' => 'Where is Cua Van Floating Village?', 'a' => 'Cua Van is located in Ha Long Bay, Quang Ninh Province, Vietnam, approximately 20 km by water from Tuan Chau International Marina. It is part of the bay’s sightseeing Route 3.'],
                ['q' => 'What is Cua Van Floating Village known for?', 'a' => 'Cua Van is known for its history as a fishing community that lived in floating houses surrounded by limestone islands. Today, visitors come to explore the sheltered cove and learn about the traditional way of life on the water.'],
                ['q' => 'Do people still live in Cua Van Floating Village?', 'a' => 'The original floating-village community was relocated to the mainland in 2014. Cua Van is no longer a large residential village, although local people continue to work in tourism and other activities on the bay.'],
                ['q' => 'Can you visit Cua Van by kayak?', 'a' => 'Kayaking is available in designated areas when local operators and weather conditions allow. Visitors can also explore the village area by bamboo rowing boat.'],
                ['q' => 'Is the Cua Van Floating Cultural Centre open?', 'a' => 'The centre suspended visitor access in April 2024 for repairs and was subsequently damaged by Typhoon Yagi. Its reopening has not been confirmed in the information reviewed for this article. Check the latest local notices before travelling.'],
                ['q' => 'How long does it take to visit Cua Van?', 'a' => 'A typical visit takes approximately 60–90 minutes, depending on the activities included. Travel time from the port or other cruise stops is additional.'],
                ['q' => 'Is Cua Van included in a 2-day/1-night Ha Long Bay cruise?', 'a' => 'Not all overnight cruises include Cua Van. Green Ruby’s planned 2-day/1-night itinerary does not visit the village. It is included in the longer 3-day/2-night programme aboard Green Ruby 1.'],
                ['q' => 'When does Green Ruby plan to visit Cua Van?', 'a' => 'Cua Van is scheduled for approximately 10:00 AM on the second day of Green Ruby 1’s planned 3-day/2-night itinerary. The stop and its activities remain subject to weather, local regulations and operational conditions.'],
            ],
        ],
        'vi' => [
            'slug' => 'lang-chai-cua-van',
            'name' => 'Làng chài Cửa Vạn',
            'image_alt' => 'Nhà bè và thuyền đánh cá neo dưới chân núi đá vôi ở làng chài Cửa Vạn, vịnh Hạ Long',
            'image_credit' => 'Ảnh: David Stanley, CC BY 4.0, qua Wikimedia Commons',
            'seo_title' => 'Làng Chài Cửa Vạn Hạ Long: Lịch Sử & Trải Nghiệm',
            'seo_description' => 'Khám phá làng chài Cửa Vạn trên vịnh Hạ Long: lịch sử làng nổi, đời sống ngư dân, trải nghiệm đi đò nan, chèo kayak và những thay đổi hiện nay.',
            'summary' => 'Làng chài Cửa Vạn nằm giữa những dãy núi đá vôi của vịnh Hạ Long, từng là nơi sinh sống của nhiều thế hệ ngư dân trên những ngôi nhà nổi. Ngày nay, du khách có thể ngồi đò nan hoặc chèo kayak tham quan khu vực làng, tìm hiểu nghề biển và những nét văn hóa còn được lưu giữ của cộng đồng vạn chài.',
            'sources' => [
                ['title' => 'Ban Quản lý vịnh Hạ Long – Trung tâm Văn hóa nổi Cửa Vạn', 'url' => 'https://halongbay.com.vn/places/37-trung-tam-van-hoa-noi-cua-van'],
                ['title' => 'Cổng thông tin tỉnh Quảng Ninh – Life by the sea in Cua Van Fishing Village', 'url' => 'https://www.quangninh.gov.vn/Sites/en-us/Trang/ChiTietTinTuc.aspx?nid=7156'],
                ['title' => 'Báo Quảng Ninh – Hồi sinh "bảo tàng sống" trong lòng vịnh', 'url' => 'https://baoquangninh.vn/hoi-sinh-bao-tang-song-trong-long-vinh-post3389317.html'],
                ['title' => 'Người Quan Sát – Trung tâm Văn hóa nổi Cửa Vạn tạm dừng đón khách (4/2024)', 'url' => 'https://nguoiquansat.vn/lang-chai-co-hut-khach-du-lich-nhat-tai-vinh-ha-long-bat-ngo-dung-don-khach-127045.html'],
                ['title' => 'UNESCO – Vịnh Hạ Long và Quần đảo Cát Bà', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'Wikimedia Commons – Cua Van Floating Village', 'url' => 'https://commons.wikimedia.org/wiki/File:Cua_Van_Floating_Village.jpg'],
            ],
            'content' => <<<'HTML'
<h2>Làng chài Cửa Vạn ở đâu?</h2>
<p>Làng chài Cửa Vạn nằm trong một vùng nước kín gió thuộc vịnh Hạ Long, cách cảng tàu khách quốc tế Tuần Châu khoảng 20 km đường thủy.</p>
<p>Khu vực làng được những dãy núi đá vôi bao quanh, tạo thành một vụng nước tương đối yên ả. Đây từng là điều kiện thuận lợi để ngư dân neo đậu thuyền, dựng nhà bè và sinh sống bằng nghề đánh bắt hải sản.</p>
<p>Cửa Vạn thuộc tuyến tham quan số 3 của vịnh Hạ Long, nằm xa hơn các điểm du lịch quen thuộc như <a href="/vi/dia-diem-tham-quan/hang-sung-sot">hang Sửng Sốt</a>, <a href="/vi/dia-diem-tham-quan/hang-luon">Hang Luồn</a> và <a href="/vi/dia-diem-tham-quan/dao-ti-top">đảo Ti Tốp</a> trên tuyến số 2.</p>
<p>Do khoảng cách từ cảng khá xa, Cửa Vạn thường xuất hiện trong các hành trình tham quan dài hoặc du thuyền nghỉ đêm, thay vì những chuyến đi ngắn chỉ ghé các điểm gần bờ.</p>
<h2>Làng chài Cửa Vạn có gì đặc biệt?</h2>
<p>Điều khiến Cửa Vạn khác với nhiều điểm tham quan trên vịnh Hạ Long là câu chuyện về con người từng sinh sống giữa biển.</p>
<p>Trước đây, những ngôi nhà nổi được dựng trên bè, neo sát nhau trong vùng nước kín gió. Thuyền vừa là phương tiện đi lại, vừa gắn với công việc mưu sinh hằng ngày. Nhiều gia đình sống bằng nghề đánh bắt, nuôi trồng hải sản và truyền lại kinh nghiệm đi biển qua nhiều thế hệ.</p>
<p>Cuộc sống ở đây từng gắn liền với con nước, thời tiết và những chuyến ra khơi. Trẻ em lớn lên trên nhà bè, người lớn làm việc trên thuyền, còn việc đi lại giữa các gia đình chủ yếu bằng những chiếc đò nhỏ.</p>
<p>Tuy nhiên, Cửa Vạn ngày nay đã thay đổi nhiều. Từ năm 2014, phần lớn các hộ dân làng chài trên vịnh được di dời lên đất liền để có điều kiện sinh sống ổn định hơn, thuận tiện cho việc học hành, chăm sóc y tế và tiếp cận các dịch vụ thiết yếu.</p>
<p>Vì vậy, du khách đến Cửa Vạn hiện nay sẽ không còn thấy một cộng đồng đông đúc sống thường xuyên trên mặt nước như trong những bức ảnh chụp nhiều năm trước.</p>
<p>Thay vào đó là những dấu tích của làng nổi, các nhà bè được bảo tồn hoặc phục vụ hoạt động tham quan, cùng những người địa phương tiếp tục làm việc trên vịnh.</p>
<p>Dù không còn giữ nguyên nhịp sống cũ, Cửa Vạn vẫn là nơi giúp du khách hiểu thêm về một phần lịch sử ít được nhắc đến của Hạ Long: cuộc sống của những gia đình từng coi mặt biển là quê nhà.</p>
<h2>Lịch sử và đời sống của ngư dân Cửa Vạn</h2>
<p>Trong nhiều thế hệ, cư dân Cửa Vạn đã thích nghi với cuộc sống trên mặt nước. Họ dựng nhà trên bè nổi, sử dụng thuyền nhỏ để đi lại và dựa vào nguồn lợi biển để sinh sống.</p>
<p>Những công việc quen thuộc của người dân trước đây gồm đánh bắt cá, thả lưới, sửa ngư cụ và chăm sóc các lồng bè nuôi hải sản.</p>
<p>Sống giữa vịnh cũng đồng nghĩa với việc phải thích nghi với những khó khăn riêng. Thời tiết xấu ảnh hưởng trực tiếp đến công việc, việc đưa trẻ đến trường không thuận tiện và khả năng tiếp cận dịch vụ y tế còn nhiều hạn chế.</p>
<p>Việc di dời cư dân lên bờ từ năm 2014 đã làm thay đổi sâu sắc đời sống của các gia đình làng chài. Nhiều người chuyển sang nghề nghiệp khác, trong khi một số vẫn gắn bó với biển thông qua các hoạt động dịch vụ và du lịch.</p>
<p>Ngày nay, khi đi đò quanh Cửa Vạn, du khách có thể nghe kể về cách người dân từng sinh sống, làm nghề và nuôi dạy con cái trên những ngôi nhà nổi.</p>
<h2>Những hoạt động nên trải nghiệm tại làng chài Cửa Vạn</h2>
<h3>Đi đò nan tham quan khu vực làng chài</h3>
<p>Ngồi đò nan là một trong những cách phổ biến để khám phá Cửa Vạn.</p>
<p>Từ tàu tham quan, du khách chuyển sang những chiếc đò nhỏ do người địa phương chèo. Đò đi chậm qua vùng nước giữa các dãy núi đá, tới những khu vực còn lưu giữ dấu tích của làng nổi.</p>
<p>Trong lúc di chuyển, bạn có thể quan sát những ngôi nhà bè, các công trình nổi còn lại và khung cảnh núi đá bao quanh vụng nước.</p>
<p>Đi đò nan không đòi hỏi nhiều sức lực, phù hợp với những người muốn thư thả ngắm cảnh và tìm hiểu về làng chài.</p>
<p>Tùy chương trình tham quan, người chèo đò hoặc hướng dẫn viên có thể giới thiệu thêm về công việc đánh bắt, nghề nuôi trồng hải sản và cuộc sống trước đây của cư dân trên vịnh.</p>
<h3>Chèo kayak quanh làng chài</h3>
<p>Nếu thích tự mình khám phá, bạn có thể chọn chèo kayak tại những khu vực được phép.</p>
<p>Khác với đi đò nan, kayak cho phép bạn chủ động điều khiển thuyền và di chuyển theo tuyến được hướng dẫn.</p>
<p>Vùng nước quanh Cửa Vạn thường được núi đá che chắn, nhưng điều kiện thực tế vẫn phụ thuộc vào gió, sóng và thời tiết trong ngày.</p>
<p>Khi chèo, cần giữ khoảng cách an toàn với các công trình nổi, tàu thuyền và khu vực không được phép tiếp cận.</p>
<h3>Tìm hiểu văn hóa làng chài và hát giao duyên</h3>
<p>Bên cạnh nghề cá, Cửa Vạn còn gắn với những nét văn hóa của cộng đồng ngư dân Hạ Long, trong đó có hát giao duyên.</p>
<p>Đây là hình thức hát đối đáp nam nữ, từng gắn với sinh hoạt tinh thần của cư dân vùng biển. Lời hát thể hiện tình cảm, cuộc sống lao động và những câu chuyện quen thuộc của người dân vạn chài.</p>
<p>Hoạt động hát giao duyên phục vụ khách tham quan tại Cửa Vạn đã được khôi phục trong năm 2026 sau thời gian gián đoạn.</p>
<p>Tuy nhiên, chương trình biểu diễn không nhất thiết diễn ra trong mọi chuyến tham quan. Nếu muốn trải nghiệm, bạn nên hỏi trước đơn vị tổ chức về lịch biểu diễn.</p>
<h3>Ngắm cảnh và chụp ảnh làng nổi</h3>
<p>Cửa Vạn nằm trong một vùng nước được núi đá vôi bao quanh. Từ đò nhỏ, du khách có thể quan sát các vách núi ở khoảng cách gần và nhìn lại khung cảnh vùng làng chài từ mặt nước.</p>
<p>Những ngôi nhà bè và thuyền gỗ cũng là hình ảnh gắn liền với lịch sử nơi đây.</p>
<p>Nếu muốn chụp ảnh người chèo đò hoặc những người đang làm việc trên bè, bạn nên xin phép trước. Đối với nhà bè hoặc công trình không mở cửa đón khách, không tự ý bước lên chỉ để chụp ảnh.</p>
<h2>Làng chài Cửa Vạn hiện nay còn người sinh sống không?</h2>
<p>Đây là câu hỏi nhiều du khách quan tâm khi tìm hiểu về Cửa Vạn.</p>
<p>Trước năm 2014, Cửa Vạn là một cộng đồng làng chài nổi có nhiều gia đình sinh sống lâu dài. Sau chương trình di dời của tỉnh Quảng Ninh, phần lớn cư dân đã chuyển lên đất liền.</p>
<p>Vì vậy, Cửa Vạn hiện nay không còn là một khu dân cư nổi đông đúc như trước.</p>
<p>Những người làm việc tại khu vực làng có thể là người dân địa phương, cư dân làng chài cũ hoặc người tham gia cung cấp dịch vụ du lịch và bảo tồn văn hóa.</p>
<p>Du khách vẫn có thể tìm hiểu lịch sử của làng thông qua hoạt động tham quan, nhưng không nên kỳ vọng được chứng kiến đầy đủ sinh hoạt thường ngày của một làng nổi như cách đây nhiều thập kỷ.</p>
<p>Đây là sự khác biệt quan trọng giữa Cửa Vạn trong những bức ảnh cũ và Cửa Vạn mà du khách nhìn thấy ngày nay.</p>
<h2>Trung tâm Văn hóa nổi Cửa Vạn</h2>
<p>Trung tâm Văn hóa nổi Cửa Vạn được thành lập năm 2006 nhằm giới thiệu và lưu giữ những giá trị văn hóa của cộng đồng ngư dân trên vịnh Hạ Long.</p>
<p>Trước đây, nơi này trưng bày hình ảnh, tư liệu, hiện vật liên quan đến nghề cá và đời sống trên mặt nước. Trung tâm cũng từng tổ chức những hoạt động giới thiệu văn hóa truyền thống, trong đó có hát giao duyên.</p>
<p>Tuy nhiên, Trung tâm đã tạm dừng đón khách từ ngày 25/4/2024 để phục vụ công tác sửa chữa, nâng cấp. Công trình sau đó tiếp tục chịu thiệt hại do bão Yagi vào tháng 9/2024.</p>
<p>Các cơ quan chức năng đã xây dựng phương án phục hồi và bảo tồn những giá trị văn hóa tại đây.</p>
<p>Tính đến thời điểm rà soát bài viết, du khách không nên mặc định Trung tâm đã mở cửa trở lại. Hoạt động đi đò và chèo kayak quanh khu vực làng chài vẫn có thể được tổ chức độc lập với việc tham quan bên trong Trung tâm.</p>
<h2>Cách đi đến làng chài Cửa Vạn</h2>
<p>Làng chài Cửa Vạn chỉ có thể đến bằng đường thủy.</p>
<p>Du khách thường tham gia tour hoặc hành trình du thuyền có lịch trình đi qua tuyến số 3 của vịnh Hạ Long. Các chuyến đi có thể xuất phát từ cảng Tuần Châu hoặc những cảng được cấp phép khác. Xem thêm <a href="/vi/cam-nang/ha-noi-di-ha-long">hướng dẫn di chuyển từ Hà Nội đến Hạ Long</a>.</p>
<p>Do vị trí nằm khá sâu trong vịnh, không phải tour Hạ Long nào cũng ghé Cửa Vạn.</p>
<p>Khi đến khu vực làng, tàu du lịch dừng tại vị trí được phép, sau đó du khách chuyển sang đò nan hoặc kayak để tham quan.</p>
<p>Nếu muốn đến Cửa Vạn, bạn nên kiểm tra tên điểm dừng trong lịch trình trước khi đặt chuyến đi, thay vì chỉ dựa vào mô tả chung là tham quan vịnh Hạ Long.</p>
<h2>Tham quan làng chài Cửa Vạn mất bao lâu?</h2>
<p>Thông thường, bạn có thể dành khoảng 60–90 phút để tham quan khu vực làng chài, tùy chương trình và hình thức trải nghiệm.</p>
<p>Nếu chỉ ngồi đò nan ngắm cảnh, thời gian có thể ngắn hơn. Những chương trình kết hợp giới thiệu văn hóa hoặc các hoạt động khác có thể kéo dài lâu hơn.</p>
<p>Thời gian trên chưa bao gồm quãng đường di chuyển từ cảng hoặc từ các điểm tham quan khác trên vịnh.</p>
<p>Đối với du khách đi du thuyền nghỉ đêm, thời gian dừng tại Cửa Vạn sẽ phụ thuộc vào lịch trình cụ thể của tàu.</p>
<h2>Những điều cần lưu ý khi tham quan làng chài</h2>
<p>Khác với các bãi tắm hay hang động, Cửa Vạn là không gian gắn với lịch sử và sinh kế của một cộng đồng ngư dân. Vì vậy, cách ứng xử khi tham quan cũng cần được chú ý.</p>
<p>Một vài điều nên nhớ:</p>
<ul>
<li>Xin phép trước khi chụp ảnh cận mặt người dân hoặc người đang làm việc.</li>
<li>Không tự ý bước lên nhà bè, công trình nổi hoặc khu vực không mở cửa cho khách.</li>
<li>Mặc áo phao khi đi đò nan hoặc chèo kayak.</li>
<li>Không xả rác hoặc làm rơi đồ xuống biển.</li>
<li>Hạn chế gây tiếng ồn khi đi qua khu vực có người làm việc.</li>
<li>Tuân thủ hướng dẫn của người chèo đò và hướng dẫn viên.</li>
</ul>
<p>Nếu muốn tìm hiểu sâu hơn về đời sống làng chài, bạn có thể hỏi hướng dẫn viên về quá trình di dời cư dân, nghề đánh bắt truyền thống và các hoạt động bảo tồn hiện nay.</p>
<h2>Trẻ em và người lớn tuổi có đi Cửa Vạn được không?</h2>
<p>Đi đò nan là hình thức tham quan tương đối nhẹ nhàng vì du khách không phải tự chèo.</p>
<p>Tuy nhiên, việc chuyển từ tàu lớn sang đò nhỏ vẫn đòi hỏi giữ thăng bằng. Người lớn tuổi hoặc người gặp khó khăn khi di chuyển nên trao đổi trước với đơn vị tổ chức để được hỗ trợ.</p>
<p>Trẻ em cần có người lớn đi cùng và mặc áo phao phù hợp. Điều kiện tham gia chèo kayak có thể khác nhau tùy độ tuổi và quy định của đơn vị vận hành.</p>
<p>Đối với người sử dụng xe lăn, việc lên xuống đò nhỏ có thể gặp nhiều hạn chế. Nên kiểm tra khả năng hỗ trợ thực tế trước chuyến đi.</p>
<h2>Nên tham quan làng chài Cửa Vạn vào thời gian nào?</h2>
<p>Cửa Vạn có thể tham quan quanh năm khi thời tiết và điều kiện vận hành cho phép.</p>
<p>Từ khoảng tháng 4 đến tháng 10, thời tiết thường ấm hơn, thích hợp cho các hoạt động ngoài trời. Tuy nhiên, mưa lớn, dông hoặc bão có thể khiến các chuyến tàu trên vịnh phải thay đổi hoặc tạm dừng.</p>
<p>Từ tháng 11 đến tháng 3, thời tiết thường mát hoặc lạnh hơn. Những ngày nhiều gió, du khách nên mang áo khoác khi ngồi đò.</p>
<p>Cửa Vạn nằm trong vùng nước khá kín gió, nhưng điều đó không có nghĩa là hoạt động chèo đò luôn được tổ chức trong mọi điều kiện thời tiết.</p>
<p>Nếu gặp mưa lớn, gió mạnh hoặc có cảnh báo an toàn trên vịnh, lịch trình tham quan có thể bị rút ngắn, thay thế hoặc hủy bỏ. Xem thêm <a href="/vi/blog/hanh-trinh/di-ha-long-mua-nao-dep.html">du lịch Hạ Long mùa nào đẹp nhất</a>.</p>
<h2>Tham quan làng chài Cửa Vạn cùng Green Ruby Cruises</h2>
<p>Làng chài Cửa Vạn là một trong những điểm dừng dự kiến của du thuyền <a href="/vi/du-thuyen/green-ruby-1-1.html">Green Ruby 1</a> trong hành trình khám phá vịnh Hạ Long 3 ngày 2 đêm.</p>
<p><strong><a href="/vi/hanh-trinh/3-ngay-2-dem-dau-an-ha-long-1-8.html">3 ngày 2 đêm – Dấu Ấn Hạ Long</a></strong><br>Bên cạnh các điểm tham quan quen thuộc như Hang Luồn, đảo Ti Tốp và hang Sửng Sốt, hành trình có thêm một ngày khám phá những khu vực xa hơn trên vịnh, trong đó có làng chài Cửa Vạn và <a href="/vi/dia-diem-tham-quan/hang-tien-ong">động Tiên Ông</a>.</p>
<p>Cửa Vạn mang đến một góc nhìn khác về Hạ Long, không chỉ qua những dãy núi đá vôi mà còn qua câu chuyện về cộng đồng ngư dân từng sinh sống giữa vịnh.</p>
<p><em>Đây là điểm dừng theo lịch trình dự kiến của Green Ruby 1. Thời gian, hoạt động và tuyến tham quan chính thức sẽ được xác nhận khi tàu đi vào vận hành và có thể điều chỉnh theo điều kiện thực tế.</em></p>
<h2>Những địa điểm có thể kết hợp tham quan</h2>
<h3><a href="/vi/dia-diem-tham-quan/hang-tien-ong">Động Tiên Ông</a></h3>
<p>Hang động nằm trong khu vực vịnh Hạ Long, được biết đến với giá trị khảo cổ và những dấu tích cư trú của cư dân tiền sử.</p>
<h3><a href="/vi/dia-diem-tham-quan/hang-sung-sot">Hang Sửng Sốt</a></h3>
<p>Hang động nổi tiếng trên đảo Bồ Hòn, có những khoang hang rộng và hệ thống thạch nhũ được hình thành qua thời gian dài.</p>
<h3><a href="/vi/dia-diem-tham-quan/dao-ti-top">Đảo Ti Tốp</a></h3>
<p>Điểm tham quan có bãi tắm nhỏ dưới chân đảo và lối leo lên đỉnh để ngắm toàn cảnh vịnh Hạ Long.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Vịnh Hạ Long, tỉnh Quảng Ninh'],
                ['label' => 'Khoảng cách', 'value' => 'Khoảng 20 km đường thủy từ cảng Tuần Châu'],
                ['label' => 'Tuyến tham quan', 'value' => 'Tuyến số 3 vịnh Hạ Long'],
                ['label' => 'Loại hình', 'value' => 'Làng chài nổi truyền thống, điểm tham quan văn hóa'],
                ['label' => 'Điểm nổi bật', 'value' => 'Nhà bè, lịch sử cư dân vạn chài, cảnh quan vùng nước kín'],
                ['label' => 'Hoạt động', 'value' => 'Đi đò nan, chèo kayak, tìm hiểu văn hóa làng chài'],
                ['label' => 'Cách đến', 'value' => 'Tàu tham quan hoặc du thuyền'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 60–90 phút'],
                ['label' => 'Cư dân', 'value' => 'Phần lớn đã chuyển lên đất liền từ năm 2014'],
                ['label' => 'Trung tâm Văn hóa nổi', 'value' => 'Tạm dừng đón khách, chờ xác nhận mở cửa trở lại'],
            ],
            'faq' => [
                ['q' => 'Làng chài Cửa Vạn nằm ở đâu?', 'a' => 'Làng chài Cửa Vạn nằm trong vịnh Hạ Long, tỉnh Quảng Ninh, cách cảng Tuần Châu khoảng 20 km đường thủy. Đây là điểm tham quan thuộc tuyến số 3 của vịnh.'],
                ['q' => 'Làng chài Cửa Vạn có gì đặc biệt?', 'a' => 'Cửa Vạn từng là nơi sinh sống của nhiều thế hệ ngư dân trên những ngôi nhà nổi. Ngày nay, du khách có thể tham quan bằng đò nan hoặc kayak và tìm hiểu về nghề cá, đời sống thủy cư và văn hóa truyền thống của người dân Hạ Long.'],
                ['q' => 'Làng chài Cửa Vạn còn người ở không?', 'a' => 'Cửa Vạn không còn cộng đồng cư dân nổi đông đúc như trước. Từ năm 2014, phần lớn các hộ dân đã được di dời lên đất liền. Hoạt động tham quan và một số công việc gắn với nghề biển vẫn tiếp tục tại khu vực này.'],
                ['q' => 'Có thể tham quan làng chài Cửa Vạn bằng cách nào?', 'a' => 'Du khách thường đến bằng tàu du lịch, sau đó chuyển sang đò nan hoặc kayak để khám phá khu vực làng. Những hình thức tham quan được tổ chức tùy điều kiện thực tế và quy định tại điểm đến.'],
                ['q' => 'Trung tâm Văn hóa nổi Cửa Vạn có mở cửa không?', 'a' => 'Trung tâm đã tạm dừng đón khách từ ngày 25/4/2024 để phục vụ việc sửa chữa và sau đó bị ảnh hưởng bởi bão Yagi. Du khách nên kiểm tra thông báo mới nhất trước chuyến đi; hoạt động tham quan khu vực làng không đồng nghĩa với việc Trung tâm mở cửa.'],
                ['q' => 'Tham quan làng chài Cửa Vạn mất bao lâu?', 'a' => 'Thời gian tham quan thường khoảng 1–1,5 giờ, tùy chương trình. Quãng đường đi tàu từ cảng đến Cửa Vạn chưa được tính trong khoảng thời gian này.'],
                ['q' => 'Hành trình Hạ Long 2 ngày 1 đêm có ghé Cửa Vạn không?', 'a' => 'Theo lịch trình Green Ruby hiện công bố, chương trình 2 ngày 1 đêm không có điểm dừng tại Cửa Vạn. Làng chài nằm trong hành trình 3 ngày 2 đêm của Green Ruby 1.'],
                ['q' => 'Green Ruby dự kiến ghé làng chài Cửa Vạn khi nào?', 'a' => 'Cửa Vạn nằm trong chương trình ngày thứ hai của hành trình 3 ngày 2 đêm – Dấu Ấn Hạ Long. Hoạt động và thời gian cụ thể có thể thay đổi khi tàu chính thức vận hành, tùy điều kiện thời tiết và quy định của cơ quan quản lý.'],
            ],
        ],
    ],
    [
        'bay' => 'ha-long',
        'category' => 'cave',
        'ord' => 5,
        'latitude' => 20.81589,
        'longitude' => 107.12264,
        'coords_verified' => true,
        'coords_source' => 'Supplied by the owner 2026-10-24 (20° 48′ 57″ N, 107° 7′ 22″ E)',
        'image' => ['file' => 'tien-ong-cave-ha-long-bay.jpg', 'link' => '/2026/10/24/tien-ong-cave-ha-long-bay.jpg'],
        'image_credit' => null,
        'itineraries' => ['en' => [3], 'vi' => [8]],
        'sources' => [$unesco],
        'editor_notes' => "EN and VI rewritten 2026-10-24 from the editorial briefs. Verified: cave area ~1000 m2 (National Museum of History); Cai Tai islet, ~18 km from Tuan Chau, Hang Ren/Hang Duc, Soi Nhu culture 10,000-8,000 BP, 2017 exhibition (Management Board); Andersson 1938, excavation 6/11-31/12/2007 (National Museum of History). Route 3 per tour listings (numbering varies). Cover image supplied by the owner 2026-10-24 (confirm it is a real photo). Coordinates supplied by the owner 2026-10-24. Old note: Needs: verified coordinates, a genuine photo (none licensed found on Wikimedia Commons), and an official source for the archaeological finds (the copy only says traces of prehistoric habitation have been recorded, as in the published itinerary). Not published: steps, size, fees.",
        'en' => [
            'slug' => 'tien-ong-cave',
            'name' => 'Tien Ong Cave',
            'image_alt' => 'Lit stalactites and stalagmites inside Tien Ong Cave, with the wide cave mouth opening onto Ha Long Bay',
            'seo_title' => 'Tien Ong Cave, Ha Long Bay: Archaeology & Visiting Guide',
            'seo_description' => 'Explore Tien Ong Cave in Ha Long Bay, home to prehistoric remains dating back 8,000–10,000 years. Discover its archaeology, location and visiting tips.',
            'summary' => 'Tien Ong Cave is a limestone cave on Cai Tai Islet in Ha Long Bay, known for its wide entrance, unusual rock formations and evidence of human habitation dating back 8,000–10,000 years. Visitors can explore the cave on foot and see an archaeological exhibition showing how people lived in this area long before Ha Long Bay became a tourist destination.',
            'sources' => [
                ['title' => 'Ha Long Bay Management Department – Tien Ong Cave', 'url' => 'https://halongbay.com.vn/en/places/35-tien-ong-cave'],
                ['title' => 'Ha Long Bay Management Department – Exploring the Archaeological Museum Inside Tien Ong Cave', 'url' => 'https://halongbay.com.vn/en/p/269-kham-pha-bao-tang-khao-co-trong-long-hang-tien-ong'],
                ['title' => 'Vietnam National Museum of History – Archaeological Excavations at Tien Ong Cave', 'url' => 'https://baotanglichsu.vn/vi/Articles/1502/49134/khai-quat-khao-co-di-tich-hang-tien-ong-tren-vinh-ha-long.html'],
                ['title' => 'UNESCO World Heritage Centre – Ha Long Bay–Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'],
            ],
            'content' => <<<'HTML'
<h2>Where is Tien Ong Cave?</h2>
<p>Tien Ong Cave is located on Cai Tai Islet, in the Hang Trai island group of Ha Long Bay, approximately 18 km from Tuan Chau International Marina.</p>
<p>It lies near <a href="/destinations/cua-van-floating-village">Cua Van Floating Village</a>, and both sites are part of Ha Long Bay’s sightseeing Route 3. Cruises visiting this area often combine the cave with a boat tour around the former fishing village.</p>
<p>Tien Ong is farther from the main sightseeing stops around <a href="/destinations/sung-sot-cave">Sung Sot Cave</a>, <a href="/destinations/luon-cave">Luon Cave</a> and <a href="/destinations/ti-top-island">Ti Top Island</a>. It is therefore more commonly included in longer excursions and 3-day/2-night cruise itineraries.</p>
<p>The cave is also known locally as Hang Ren and was referred to as Hang Duc in early archaeological records.</p>
<h2>What makes Tien Ong Cave special?</h2>
<p>Tien Ong Cave is worth visiting for two reasons: its limestone formations and its archaeological history.</p>
<p>The cave has a broad entrance and a series of chambers and smaller recesses. Stalactites hang from the ceiling, while stalagmites rise from the cave floor. One formation resembles an elderly man with long hair and a beard, giving the cave its name.</p>
<p>In Vietnamese, <em>Tien Ong</em> refers to a legendary old sage or immortal. Local tradition associates the human-like rock formation with this figure.</p>
<p>But the cave’s most significant feature is found closer to the ground.</p>
<p>Archaeologists have uncovered evidence that prehistoric people lived here thousands of years ago. The discoveries include stone tools, animal bones and large deposits of shells left by people who used the cave.</p>
<p>Today, parts of this archaeological record are presented in an exhibition inside the cave, allowing visitors to see how the site connects Ha Long Bay’s landscape with its much older human history.</p>
<h2>The prehistoric history of Tien Ong Cave</h2>
<p>Tien Ong Cave is one of the important archaeological sites associated with the Soi Nhu culture in the Ha Long Bay region.</p>
<p>Archaeological research indicates that people occupied the cave approximately 8,000–10,000 years ago, during the transition from the late Pleistocene to the early Holocene.</p>
<p>These early inhabitants lived in a landscape very different from the Ha Long Bay we see today. Sea levels were lower, and areas now covered by water were once dry land.</p>
<p>The people who used the cave relied on resources available around them, including animals and freshwater molluscs. Archaeologists have found thick deposits of discarded shells, along with animal remains and stone and bone tools.</p>
<p>The findings suggest that the cave served as a place of human occupation rather than simply a temporary shelter.</p>
<p>In 2007, excavations by Vietnamese archaeologists helped establish the site’s age and significance. Earlier research had also been conducted here, including work by Swedish archaeologist J. G. Andersson in 1938.</p>
<p>These discoveries have helped researchers better understand prehistoric settlement and changing environments in what is now Ha Long Bay.</p>
<h2>What can you see inside Tien Ong Cave?</h2>
<h3>Limestone formations and the old sage</h3>
<p>The entrance to Tien Ong Cave is unusually wide, allowing natural light into its outer section.</p>
<p>Inside, the rock surfaces are marked by stalactites, stalagmites and smaller cavities formed over long periods as water moved through the limestone.</p>
<p>One stalagmite resembles the face and figure of an elderly man. This formation is the source of the cave’s present name.</p>
<p>The cave is illuminated for visitors, making it possible to examine the formations as you follow the designated walking route.</p>
<h3>Archaeological remains</h3>
<p>What distinguishes Tien Ong from many other sightseeing caves in Ha Long Bay is the evidence preserved in its floor deposits.</p>
<p>Excavations have revealed stone implements, bone tools, animal remains and layers of freshwater snail shells.</p>
<p>These objects tell us something about how prehistoric communities obtained food, made tools and adapted to their surroundings.</p>
<p>Rather than imagining the cave only as a natural attraction, visitors can see it as a place where people once spent part of their everyday lives.</p>
<h3>The archaeological exhibition</h3>
<p>In 2017, the Ha Long Bay Management Board worked with the Vietnam Institute of Archaeology and Quang Ninh Museum to establish an archaeological exhibition inside Tien Ong Cave.</p>
<p>The exhibition presents information and material connected with the site’s prehistoric occupation, helping visitors understand the discoveries made during archaeological investigations.</p>
<p>If you are particularly interested in history, allow time to read the displays rather than walking straight through the cave.</p>
<p>Exhibition access and individual displays may change, so follow the information available on the day of your visit.</p>
<h2>How is Tien Ong Cave different from Sung Sot Cave?</h2>
<p>Both caves are part of Ha Long Bay’s limestone landscape, but they offer quite different experiences.</p>
<p><a href="/destinations/sung-sot-cave">Sung Sot Cave</a> is known for its large chambers, elaborate rock formations and established walking route. It is one of the best-known stops on the bay’s main sightseeing circuit.</p>
<p>Tien Ong Cave is particularly interesting for its archaeological evidence. Its broad entrance, relatively accessible cave floor and on-site displays make it a place to learn about the people who lived in the region long before modern tourism.</p>
<p>Tien Ong is also on a different sightseeing route, so it generally receives visitors travelling farther into the bay.</p>
<p>If you are mainly interested in dramatic cave scenery, Sung Sot is likely to be the stronger choice. If you enjoy archaeology, early human history or less heavily visited parts of Ha Long Bay, Tien Ong offers something different.</p>
<h2>How to get to Tien Ong Cave</h2>
<p>Tien Ong Cave is accessible by boat as part of a Ha Long Bay sightseeing tour or cruise.</p>
<p>Most visitors travel from Tuan Chau International Marina or another authorised passenger port in the Ha Long area. See <a href="/guides/hanoi-to-halong-bay">how to get from Hanoi to Halong Bay</a>.</p>
<p>Because the cave is on sightseeing Route 3, it is not included in every day trip or overnight cruise.</p>
<p>Boats visiting the cave stop near the island, and passengers continue on foot from the landing area to the entrance.</p>
<p>The approach involves a short walk with some steps and uneven surfaces. Once inside, visitors follow the designated route through the accessible parts of the cave.</p>
<p><a href="/cruise/green-ruby-1-3.html">Green Ruby 1</a> includes Tien Ong Cave in its planned 3-day/2-night Ha Long Bay itinerary, alongside Cua Van Floating Village and a pearl farm visit.</p>
<h2>How long does a visit to Tien Ong Cave take?</h2>
<p>Allow approximately 30–45 minutes for a typical visit, including the walk from the landing area, time inside the cave and returning to the boat (estimate).</p>
<p>Visitors interested in the archaeological displays may wish to spend longer reading the information and looking at the exhibits.</p>
<p>The duration also depends on the tour schedule, walking pace and number of visitors.</p>
<p>For an organised cruise, check the time allocated by your guide before entering.</p>
<h2>What to expect when you arrive</h2>
<p>Tien Ong Cave has a wide entrance rather than the narrow opening found at some other caves around Ha Long Bay.</p>
<p>The floor lies only a few metres above sea level, so the approach is relatively short. There may still be steps, uneven surfaces and damp areas that require care.</p>
<p>Inside, visitors will find limestone formations and sections associated with the archaeological history of the cave.</p>
<p>It is generally less busy than Sung Sot Cave because fewer sightseeing itineraries travel along Route 3. However, visitor numbers vary, and the cave is not guaranteed to be quiet at every time of day.</p>
<p>Your guide may explain the local story behind the cave’s name and the archaeological discoveries made here.</p>
<h2>Accessibility and practical tips</h2>
<p>Tien Ong Cave is explored on foot and involves walking over surfaces that may be uneven or damp.</p>
<p>The route is not designed for wheelchair access, and visitors with limited mobility should ask their cruise operator about the approach and walking conditions before travelling.</p>
<p>A few things worth remembering:</p>
<ul>
<li>Wear shoes with good grip rather than slippery sandals.</li>
<li>Watch your footing on steps and damp surfaces.</li>
<li>Follow the designated walking route.</li>
<li>Do not touch or damage stalactites, stalagmites or archaeological deposits.</li>
<li>Avoid using flash where photography restrictions apply.</li>
<li>Allow enough time to read the archaeological displays if history interests you.</li>
</ul>
<p>The cave is a protected natural and archaeological site. Removing stones, shells or other material is not permitted.</p>
<h2>Best time to visit Tien Ong Cave</h2>
<p>Tien Ong Cave can be visited throughout the year when weather and cruise operations allow.</p>
<p>Because most of the visit takes place inside the cave, seasonal temperatures are less important than the conditions affecting boat travel across Ha Long Bay.</p>
<p>From April to October, the weather is generally warmer, but heavy rain, strong winds and tropical storms can disrupt cruises.</p>
<p>From November to March, conditions are usually cooler. Walking to the cave may be more comfortable, although damp weather can still make steps and pathways slippery.</p>
<p>Visits may be shortened, rescheduled or cancelled when authorities restrict boat movements for safety reasons. See <a href="/blog/travel-tips/best-time-to-visit-halong-bay.html">the best time to visit Halong Bay</a> for month-by-month conditions.</p>
<h2>Explore Tien Ong Cave with Green Ruby Cruises</h2>
<p>Tien Ong Cave is included in Green Ruby 1’s planned 3-day/2-night journey through Ha Long Bay.</p>
<p><strong><a href="/itinerary/the-halong-immersion-3-days-2-nights-3-3.html">The Halong Immersion – 3 Days 2 Nights</a></strong><br>On the second day, guests transfer to a dedicated day boat to visit Cua Van Floating Village, Tien Ong Cave and a pearl cultivation facility.</p>
<p>The cave adds a historical perspective to the journey, showing that Ha Long Bay has been part of human life for thousands of years.</p>
<p><em>These are planned stops. Final timings and activities will be confirmed when Green Ruby’s sailings begin and may change due to weather, local regulations or operational conditions.</em></p>
<h2>Nearby places and related experiences</h2>
<h3><a href="/destinations/cua-van-floating-village">Cua Van Floating Village</a></h3>
<p>A former fishing village where families once lived in floating homes. Visitors can explore the sheltered cove by bamboo boat or kayak and learn about the area’s fishing heritage.</p>
<h3><a href="/destinations/sung-sot-cave">Sung Sot Cave</a></h3>
<p>One of Ha Long Bay’s best-known caves, with large limestone chambers and an established walking route through Bo Hon Island.</p>
<h3><a href="/destinations/ti-top-island">Ti Top Island</a></h3>
<p>A small island known for its crescent-shaped beach and hilltop viewpoint overlooking the limestone islands of Ha Long Bay.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Cai Tai Islet, Hang Trai area, Ha Long Bay'],
                ['label' => 'Distance', 'value' => 'Approximately 18 km from Tuan Chau International Marina'],
                ['label' => 'Sightseeing route', 'value' => 'Ha Long Bay Route 3'],
                ['label' => 'Known for', 'value' => 'Prehistoric archaeological remains and limestone formations'],
                ['label' => 'Archaeological period', 'value' => 'Approximately 8,000–10,000 years ago'],
                ['label' => 'Cultural association', 'value' => 'Soi Nhu culture'],
                ['label' => 'Main activity', 'value' => 'Walking through the cave and viewing archaeological displays'],
                ['label' => 'Access', 'value' => 'By sightseeing boat or cruise, followed by a short walk'],
                ['label' => 'Typical visit', 'value' => 'Approximately 30–45 minutes'],
                ['label' => 'Accessibility', 'value' => 'Uneven walking surfaces; not wheelchair-accessible'],
            ],
            'faq' => [
                ['q' => 'Where is Tien Ong Cave?', 'a' => 'Tien Ong Cave is located on Cai Tai Islet in the Hang Trai island group of Ha Long Bay, approximately 18 km from Tuan Chau International Marina. It is part of sightseeing Route 3, near Cua Van Floating Village.'],
                ['q' => 'Why is it called Tien Ong Cave?', 'a' => 'The name comes from a limestone formation that resembles an elderly man with long hair and a beard. Local tradition associates it with the image of a legendary old sage or immortal.'],
                ['q' => 'How old is Tien Ong Cave?', 'a' => 'The limestone cave itself formed through geological processes over a much longer period. Archaeological evidence shows that people occupied the site approximately 8,000–10,000 years ago.'],
                ['q' => 'What archaeological discoveries were made at Tien Ong Cave?', 'a' => 'Excavations uncovered stone tools, bone implements, animal remains and large deposits of mollusc shells. These findings help explain how prehistoric communities lived and obtained food in the Ha Long Bay region.'],
                ['q' => 'How is Tien Ong Cave different from Sung Sot Cave?', 'a' => 'Sung Sot Cave is best known for its large chambers and spectacular limestone formations. Tien Ong Cave is especially significant for its prehistoric archaeological remains and on-site exhibition. The caves are also on different sightseeing routes.'],
                ['q' => 'Is Tien Ong Cave difficult to visit?', 'a' => 'The walk is relatively short, but the approach and cave floor include uneven or potentially slippery surfaces. Visitors should wear suitable footwear. The site is not wheelchair-accessible.'],
                ['q' => 'How long does it take to explore Tien Ong Cave?', 'a' => 'Allow approximately 30–45 minutes for a typical visit, depending on your walking pace and how much time you spend looking at the archaeological displays.'],
                ['q' => 'Is Tien Ong Cave included in a 2-day/1-night Ha Long Bay cruise?', 'a' => 'Not in Green Ruby’s currently planned 2-day/1-night itinerary. Tien Ong Cave is included in the longer 3-day/2-night programme aboard Green Ruby 1.'],
                ['q' => 'When does Green Ruby plan to visit Tien Ong Cave?', 'a' => 'Tien Ong Cave is scheduled for approximately 11:30 AM on the second day of Green Ruby 1’s planned 3-day/2-night itinerary. The visit remains subject to weather, local regulations and operational conditions.'],
            ],
        ],
        'vi' => [
            'slug' => 'hang-tien-ong',
            'name' => 'Hang Tiên Ông',
            'image_alt' => 'Thạch nhũ được chiếu sáng trong hang Tiên Ông, cửa hang rộng nhìn ra vịnh Hạ Long',
            'seo_title' => 'Hang Tiên Ông Hạ Long: Di Chỉ Khảo Cổ & Kinh Nghiệm Tham Quan',
            'seo_description' => 'Hang Tiên Ông ở đâu, có gì? Tìm hiểu di chỉ khảo cổ văn hóa Soi Nhụ, dấu tích cư trú 8.000–10.000 năm, thạch nhũ hình ông tiên và kinh nghiệm tham quan.',
            'summary' => 'Hang Tiên Ông nằm trên hòn Cái Tai, giữa vịnh Hạ Long, nổi bật với cửa hang rộng, những khối thạch nhũ có hình dáng đặc biệt và dấu tích cư trú của người tiền sử cách đây khoảng 8.000–10.000 năm. Đến đây, du khách không chỉ được ngắm cảnh hang động mà còn có thể tìm hiểu về cuộc sống của cư dân cổ từng sinh sống trên vùng đất nay đã trở thành vịnh Hạ Long.',
            'sources' => [
                ['title' => 'Ban Quản lý vịnh Hạ Long – Hang Tiên Ông', 'url' => 'https://halongbay.com.vn/places/35-hang-tien-ong'],
                ['title' => 'Ban Quản lý vịnh Hạ Long – Khám phá “Bảo tàng” khảo cổ trong lòng hang Tiên Ông', 'url' => 'https://halongbay.com.vn/p/269-kham-pha-bao-tang-khao-co-trong-long-hang-tien-ong'],
                ['title' => 'Bảo tàng Lịch sử Quốc gia – Khai quật khảo cổ di tích hang Tiên Ông', 'url' => 'https://baotanglichsu.vn/vi/Articles/1502/49134/khai-quat-khao-co-di-tich-hang-tien-ong-tren-vinh-ha-long.html'],
                ['title' => 'Bảo tàng Lịch sử Quốc gia – Khám phá hang Tiên Ông, hành trình tìm về Hạ Long thời tiền sử', 'url' => 'https://baotanglichsu.vn/vi/Articles/3127/61922/kham-pha-hang-tien-ong-hanh-trinh-tim-ve-ha-long-thoi-tien-su-phan-1.html'],
                ['title' => 'UNESCO – Vịnh Hạ Long và Quần đảo Cát Bà', 'url' => 'https://whc.unesco.org/en/list/672'],
            ],
            'content' => <<<'HTML'
<h2>Hang Tiên Ông ở đâu?</h2>
<p>Hang Tiên Ông nằm trên hòn Cái Tai, thuộc khu vực đảo Hang Trai ở phía nam vịnh Hạ Long, cách cảng tàu khách quốc tế Tuần Châu khoảng 18 km đường thủy.</p>
<p>Hang nằm gần <a href="/vi/dia-diem-tham-quan/lang-chai-cua-van">làng chài Cửa Vạn</a>, cùng thuộc tuyến tham quan số 3 của vịnh Hạ Long. Những chuyến tàu đi qua khu vực này thường kết hợp tham quan hang với trải nghiệm tìm hiểu đời sống ngư dân ở Cửa Vạn.</p>
<p>So với các điểm quen thuộc như <a href="/vi/dia-diem-tham-quan/hang-sung-sot">hang Sửng Sốt</a>, <a href="/vi/dia-diem-tham-quan/hang-luon">Hang Luồn</a> hay <a href="/vi/dia-diem-tham-quan/dao-ti-top">đảo Ti Tốp</a>, Hang Tiên Ông nằm xa hơn trên tuyến tham quan khác. Vì vậy, địa điểm này thường được đưa vào những hành trình dài, đặc biệt là các chuyến du thuyền 3 ngày 2 đêm.</p>
<p>Hang còn được biết đến với tên gọi dân gian là hang Rền, hoặc hang Đục trong những tài liệu khảo cổ trước đây.</p>
<h2>Hang Tiên Ông có gì đặc biệt?</h2>
<p>Điều khiến Hang Tiên Ông đáng chú ý không chỉ nằm ở cảnh quan đá vôi, mà còn ở những dấu vết con người để lại từ hàng nghìn năm trước.</p>
<p>Hang có cửa rộng, nền đá tương đối cao so với mặt biển và nhiều ngách nhỏ phía trong. Trên trần và dọc các vách hang là những khối nhũ đá hình thành qua thời gian dài.</p>
<p>Nổi bật trong số đó là một khối măng đá có hình dáng giống ông lão với khuôn mặt hiền hậu, râu tóc dài. Người dân liên tưởng đến hình ảnh một ông tiên trong chuyện dân gian và từ đó gọi nơi này là Hang Tiên Ông.</p>
<p>Tuy nhiên, giá trị đặc biệt hơn của hang nằm ngay dưới nền đất.</p>
<p>Các cuộc khảo cổ đã phát hiện công cụ đá, công cụ xương, xương động vật và những lớp vỏ ốc tích tụ từ thời tiền sử. Đây là bằng chứng cho thấy con người từng sử dụng hang làm nơi cư trú từ khoảng 8.000–10.000 năm trước.</p>
<p>Vì vậy, khi tham quan Hang Tiên Ông, du khách có thể tìm hiểu cả quá trình hình thành cảnh quan đá vôi lẫn lịch sử cư trú của con người ở vùng Hạ Long.</p>
<h2>Dấu tích người tiền sử tại Hang Tiên Ông</h2>
<p>Hang Tiên Ông là một trong những di chỉ khảo cổ gắn với văn hóa Soi Nhụ, thuộc giai đoạn đầu thời đại Đá mới ở khu vực Hạ Long.</p>
<p>Theo các nghiên cứu khảo cổ, con người đã cư trú tại đây cách ngày nay khoảng 8.000–10.000 năm.</p>
<p>Thời điểm đó, cảnh quan khu vực này khác nhiều so với hiện tại. Mực nước biển thấp hơn, nhiều vùng ngày nay chìm dưới nước từng là đất liền hoặc vùng ven những dòng suối.</p>
<p>Cư dân cổ sử dụng các nguồn thức ăn sẵn có trong môi trường xung quanh, trong đó có ốc suối, ốc núi và động vật nhỏ. Họ dùng những công cụ thô sơ làm từ đá và xương để phục vụ cuộc sống.</p>
<p>Trong hang, các nhà khảo cổ tìm thấy những lớp vỏ nhuyễn thể tích tụ với số lượng lớn, cùng nhiều công cụ và di vật khác. Những lớp trầm tích này giúp các nhà nghiên cứu xác định phần nào môi trường sống, nguồn thức ăn và hoạt động của cư dân thời đó.</p>
<p>Năm 1938, nhà khảo cổ học người Thụy Điển J. G. Andersson đã nghiên cứu địa điểm này và gọi là hang Đục.</p>
<p>Đến cuối năm 2007, Bảo tàng Lịch sử Việt Nam phối hợp với cơ quan văn hóa tỉnh Quảng Ninh tiến hành khai quật để làm rõ hơn các tầng văn hóa và giá trị khảo cổ của hang.</p>
<p>Những phát hiện tại Hang Tiên Ông góp phần bổ sung hiểu biết về lịch sử cư trú của con người ở Hạ Long từ trước khi vùng vịnh có cảnh quan biển đảo như ngày nay.</p>
<h2>Những điều đáng xem khi tham quan Hang Tiên Ông</h2>
<h3>Ngắm thạch nhũ và khối đá hình ông tiên</h3>
<p>Cửa Hang Tiên Ông khá rộng, cho phép ánh sáng tự nhiên chiếu vào khu vực phía ngoài.</p>
<p>Bên trong là những khối nhũ đá với nhiều hình dáng khác nhau, có nơi buông xuống từ trần hang, có nơi nhô lên từ nền đá. Các ngách nhỏ nằm dọc theo vách tạo nên cấu trúc khá phức tạp.</p>
<p>Điểm được nhắc đến nhiều nhất là khối măng đá trông giống một ông lão có râu tóc dài. Đây cũng là hình ảnh gắn với tên gọi Hang Tiên Ông.</p>
<p>Du khách có thể quan sát các khối nhũ đá và tìm hiểu câu chuyện dân gian đằng sau tên hang trong lúc đi theo lối tham quan.</p>
<h3>Tìm hiểu những dấu tích cư trú thời tiền sử</h3>
<p>Khác với nhiều hang động trên vịnh Hạ Long chủ yếu được biết đến nhờ cảnh quan, Hang Tiên Ông còn lưu giữ những dấu tích sinh hoạt của cư dân cổ.</p>
<p>Trong các đợt khai quật, nhà khảo cổ đã phát hiện công cụ đá, công cụ xương, xương động vật và lượng lớn vỏ nhuyễn thể.</p>
<p>Đặc biệt, những lớp vỏ ốc suối và ốc núi cho thấy nguồn thức ăn mà cư dân thời đó từng khai thác.</p>
<p>Những dấu tích này giúp người xem hiểu rằng khu vực vịnh Hạ Long không chỉ có giá trị về địa chất và cảnh quan, mà còn gắn với lịch sử sinh sống lâu dài của con người.</p>
<h3>Tham quan khu trưng bày khảo cổ trong hang</h3>
<p>Từ năm 2017, Hang Tiên Ông được tổ chức thêm không gian giới thiệu khảo cổ ngay tại di tích.</p>
<p>Khu trưng bày cung cấp thông tin về những cuộc nghiên cứu, quá trình khai quật và các dấu tích liên quan đến đời sống người tiền sử.</p>
<p>Nếu quan tâm đến lịch sử, bạn nên dành thời gian đọc các bảng giới thiệu thay vì chỉ đi qua để ngắm thạch nhũ.</p>
<p>Nội dung trưng bày và khu vực được phép tiếp cận có thể thay đổi theo tình trạng bảo tồn, vì vậy cần tuân thủ hướng dẫn tại điểm tham quan.</p>
<h2>Hang Tiên Ông khác hang Sửng Sốt thế nào?</h2>
<p>Cả Hang Tiên Ông và <a href="/vi/dia-diem-tham-quan/hang-sung-sot">hang Sửng Sốt</a> đều là hang đá vôi trên vịnh Hạ Long, nhưng mỗi nơi có nét đáng chú ý riêng.</p>
<p>Hang Sửng Sốt nổi tiếng với những khoang hang lớn, hệ thống thạch nhũ phong phú và lối tham quan được tổ chức qua nhiều khu vực. Đây là một trong những điểm dừng phổ biến nhất của tuyến tham quan số 2.</p>
<p>Hang Tiên Ông lại có giá trị nổi bật về khảo cổ. Bên cạnh những khối nhũ đá tự nhiên, hang còn lưu giữ bằng chứng về cuộc sống của cư dân tiền sử thuộc văn hóa Soi Nhụ.</p>
<p>Do nằm trên tuyến tham quan khác, Hang Tiên Ông cũng thường có ít đoàn khách hơn hang Sửng Sốt, dù lượng khách thực tế còn tùy thời điểm.</p>
<p>Nếu muốn ngắm những khoang hang rộng và hệ thống thạch nhũ, hang Sửng Sốt là lựa chọn phù hợp. Còn nếu thích tìm hiểu lịch sử, khảo cổ và những địa điểm ít đông đúc hơn, Hang Tiên Ông sẽ mang đến một trải nghiệm khác.</p>
<h2>Cách đi đến Hang Tiên Ông</h2>
<p>Du khách đến Hang Tiên Ông bằng tàu tham quan vịnh Hạ Long hoặc du thuyền có lịch trình đi qua tuyến số 3.</p>
<p>Các chuyến đi thường xuất phát từ cảng Tuần Châu hoặc những cảng tàu khách được cấp phép tại khu vực Hạ Long. Xem thêm <a href="/vi/cam-nang/ha-noi-di-ha-long">cách đi từ Hà Nội đến Hạ Long</a>.</p>
<p>Do nằm cách xa các điểm tham quan thuộc tuyến số 2, không phải chương trình du lịch Hạ Long nào cũng ghé Hang Tiên Ông.</p>
<p>Khi đến khu vực hang, tàu dừng tại điểm được phép, sau đó du khách đi bộ từ nơi cập tàu lên cửa hang. Đường đi không quá dài nhưng có bậc và một số đoạn nền đá gồ ghề.</p>
<p>Trong lịch trình dự kiến của <a href="/vi/du-thuyen/green-ruby-1-1.html">Green Ruby 1</a>, Hang Tiên Ông nằm trong chương trình tham quan ngày thứ hai của hành trình Hạ Long 3 ngày 2 đêm, cùng với làng chài Cửa Vạn và hoạt động tìm hiểu nghề nuôi cấy ngọc trai.</p>
<h2>Tham quan Hang Tiên Ông mất bao lâu?</h2>
<p>Bạn nên dành khoảng 30–45 phút cho một lượt tham quan Hang Tiên Ông, bao gồm thời gian di chuyển từ khu vực cập tàu, đi bộ trong hang và quay trở lại.</p>
<p>Nếu quan tâm đến khảo cổ, bạn có thể cần thêm thời gian để đọc thông tin trưng bày và quan sát các dấu tích được giới thiệu.</p>
<p>Thời gian thực tế cũng phụ thuộc vào tốc độ di chuyển, lượng khách tại điểm tham quan và lịch trình của tàu.</p>
<p>Nếu tham gia tour hoặc du thuyền nghỉ đêm, bạn nên hỏi hướng dẫn viên về thời gian tập trung trước khi vào hang.</p>
<h2>Khi đến Hang Tiên Ông cần lưu ý gì?</h2>
<p>Hang Tiên Ông có cửa khá rộng, không phải đi qua lối hẹp kéo dài như một số hang động khác.</p>
<p>Nền hang nằm cao hơn mực nước biển vài mét, nhưng đường đi vẫn có những đoạn gồ ghề, có thể ẩm hoặc trơn sau mưa.</p>
<p>Bên trong hang có nhiều khối nhũ đá và những khu vực mang giá trị khảo cổ cần được bảo vệ.</p>
<p>Một số lưu ý khi tham quan:</p>
<ul>
<li>Mang giày hoặc dép có độ bám tốt.</li>
<li>Đi cẩn thận trên những đoạn có bậc và nền đá không bằng phẳng.</li>
<li>Không chạm tay, bẻ hoặc làm hư hại thạch nhũ.</li>
<li>Không nhặt đá, vỏ ốc hay bất kỳ vật liệu nào trong khu vực di tích.</li>
<li>Đi theo lối được phép, không tự ý vào các ngách hang.</li>
<li>Tuân thủ quy định về chụp ảnh và sử dụng đèn flash tại khu trưng bày.</li>
</ul>
<p>Đối với người đi lại khó khăn hoặc sử dụng xe lăn, lối vào và nền đá bên trong có thể không phù hợp. Nên hỏi trước đơn vị tổ chức về điều kiện tiếp cận thực tế.</p>
<h2>Nên tham quan Hang Tiên Ông vào tháng mấy?</h2>
<p>Hang Tiên Ông có thể tham quan quanh năm khi thời tiết và hoạt động tàu du lịch cho phép.</p>
<p>Vì phần lớn thời gian tham quan diễn ra bên trong hang, mùa nóng hay mùa lạnh không tạo ra khác biệt lớn như với những hoạt động tắm biển hoặc chèo kayak.</p>
<p>Từ khoảng tháng 4 đến tháng 10, thời tiết thường ấm hơn, nhưng cũng có thể xuất hiện mưa lớn, gió mạnh hoặc bão khiến lịch trình tham quan vịnh thay đổi.</p>
<p>Từ tháng 11 đến tháng 3, thời tiết thường mát hoặc lạnh hơn. Việc đi bộ có thể dễ chịu hơn vào những ngày không mưa, nhưng các bậc đá và lối đi vẫn có thể ẩm ướt.</p>
<p>Khi có cảnh báo thời tiết xấu, tàu tham quan có thể phải thay đổi tuyến, rút ngắn hành trình hoặc tạm dừng hoạt động theo quy định. Xem thêm <a href="/vi/blog/hanh-trinh/di-ha-long-mua-nao-dep.html">đi Hạ Long mùa nào đẹp</a>.</p>
<h2>Tham quan Hang Tiên Ông cùng Green Ruby Cruises</h2>
<p>Hang Tiên Ông nằm trong lịch trình dự kiến của du thuyền Green Ruby 1 trên vịnh Hạ Long.</p>
<p><strong><a href="/vi/hanh-trinh/3-ngay-2-dem-dau-an-ha-long-1-8.html">3 ngày 2 đêm – Dấu Ấn Hạ Long</a></strong><br>Trong ngày thứ hai, du khách dự kiến chuyển sang tàu tham quan riêng để khám phá làng chài Cửa Vạn, Hang Tiên Ông và khu vực nuôi cấy ngọc trai.</p>
<p>Điểm dừng tại Hang Tiên Ông mang đến cơ hội tìm hiểu thêm về lịch sử cư trú của con người trên vùng vịnh, bên cạnh những trải nghiệm ngắm cảnh và nghỉ dưỡng trong hành trình.</p>
<p><em>Các hoạt động trên thuộc lịch trình dự kiến của Green Ruby 1. Thời gian và điểm dừng chính thức sẽ được xác nhận khi tàu đi vào vận hành và có thể điều chỉnh theo điều kiện thực tế.</em></p>
<h2>Những địa điểm gần và liên quan</h2>
<h3><a href="/vi/dia-diem-tham-quan/lang-chai-cua-van">Làng chài Cửa Vạn</a></h3>
<p>Làng chài nổi truyền thống của vịnh Hạ Long, nơi nhiều thế hệ ngư dân từng sinh sống trên những ngôi nhà bè. Ngày nay, du khách có thể tham quan khu vực làng và tìm hiểu lịch sử cộng đồng vạn chài.</p>
<h3><a href="/vi/dia-diem-tham-quan/hang-sung-sot">Hang Sửng Sốt</a></h3>
<p>Hang động nổi tiếng trên đảo Bồ Hòn, có những khoang hang lớn và hệ thống thạch nhũ phong phú. Đây là điểm tham quan phù hợp để so sánh với Hang Tiên Ông về cảnh quan và trải nghiệm.</p>
<h3><a href="/vi/dia-diem-tham-quan/dao-ti-top">Đảo Ti Tốp</a></h3>
<p>Hòn đảo có bãi tắm nhỏ hình lưỡi liềm và lối leo lên đỉnh núi để ngắm toàn cảnh vịnh Hạ Long.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Hòn Cái Tai, khu vực đảo Hang Trai, vịnh Hạ Long'],
                ['label' => 'Khoảng cách', 'value' => 'Khoảng 18 km từ cảng Tuần Châu'],
                ['label' => 'Tuyến tham quan', 'value' => 'Tuyến số 3 vịnh Hạ Long'],
                ['label' => 'Loại hình', 'value' => 'Hang đá vôi, di chỉ khảo cổ'],
                ['label' => 'Diện tích hang', 'value' => 'Khoảng 1.000 m²'],
                ['label' => 'Điểm nổi bật', 'value' => 'Thạch nhũ hình ông tiên, dấu tích cư trú tiền sử'],
                ['label' => 'Niên đại cư trú', 'value' => 'Khoảng 8.000–10.000 năm trước'],
                ['label' => 'Văn hóa khảo cổ', 'value' => 'Văn hóa Soi Nhụ'],
                ['label' => 'Cách đến', 'value' => 'Tàu tham quan hoặc du thuyền, sau đó đi bộ'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 30–45 phút (ước tính)'],
                ['label' => 'Điều kiện tiếp cận', 'value' => 'Có bậc đá và nền không bằng phẳng'],
            ],
            'faq' => [
                ['q' => 'Hang Tiên Ông ở đâu?', 'a' => 'Hang Tiên Ông nằm trên hòn Cái Tai, thuộc khu vực đảo Hang Trai ở vịnh Hạ Long, tỉnh Quảng Ninh. Hang cách cảng Tuần Châu khoảng 18 km đường thủy và nằm gần làng chài Cửa Vạn.'],
                ['q' => 'Vì sao gọi là Hang Tiên Ông?', 'a' => 'Tên hang bắt nguồn từ một khối măng đá có hình dáng giống ông lão với khuôn mặt hiền hậu, râu tóc dài. Người dân liên tưởng đến hình ảnh ông tiên trong chuyện dân gian và gọi nơi này là Hang Tiên Ông.'],
                ['q' => 'Hang Tiên Ông có giá trị khảo cổ gì?', 'a' => 'Hang Tiên Ông là di chỉ khảo cổ gắn với văn hóa Soi Nhụ. Tại đây, các nhà nghiên cứu phát hiện công cụ đá, công cụ xương, xương động vật và những lớp vỏ nhuyễn thể, cho thấy con người từng cư trú trong hang từ khoảng 8.000–10.000 năm trước.'],
                ['q' => 'Hang Tiên Ông có phải hang động tiền sử không?', 'a' => 'Có. Những dấu tích khảo cổ cho thấy đây từng là nơi cư trú của con người thời tiền sử. Tuy nhiên, cần phân biệt tuổi địa chất của hang đá vôi với niên đại cư trú của con người: con số 8.000–10.000 năm nói về dấu tích con người, không phải tuổi hình thành của hang.'],
                ['q' => 'Hang Tiên Ông khác hang Sửng Sốt thế nào?', 'a' => 'Hang Sửng Sốt nổi bật với các khoang hang lớn và hệ thống thạch nhũ. Hang Tiên Ông cũng có nhũ đá tự nhiên nhưng đặc biệt hơn ở những dấu tích cư trú thời tiền sử và giá trị khảo cổ. Hai hang thuộc các tuyến tham quan khác nhau.'],
                ['q' => 'Đi Hang Tiên Ông có vất vả không?', 'a' => 'Quãng đường tham quan tương đối ngắn, nhưng có bậc đá và một số đoạn gồ ghề hoặc ẩm ướt. Người có sức khỏe bình thường thường có thể tham quan nếu đi cẩn thận. Địa điểm không phù hợp với xe lăn do điều kiện lối đi.'],
                ['q' => 'Tham quan Hang Tiên Ông mất bao lâu?', 'a' => 'Thông thường nên dành khoảng 30–45 phút, tùy lịch trình, tốc độ đi bộ và thời gian tìm hiểu khu trưng bày khảo cổ.'],
                ['q' => 'Hành trình Hạ Long 2 ngày 1 đêm có ghé Hang Tiên Ông không?', 'a' => 'Theo lịch trình dự kiến của Green Ruby, hành trình 2 ngày 1 đêm không ghé Hang Tiên Ông. Địa điểm này nằm trong chương trình 3 ngày 2 đêm của Green Ruby 1.'],
                ['q' => 'Green Ruby dự kiến ghé Hang Tiên Ông khi nào?', 'a' => 'Theo chương trình dự kiến, Green Ruby 1 sẽ ghé Hang Tiên Ông vào khoảng 11:30 ngày thứ hai của hành trình 3 ngày 2 đêm – Dấu Ấn Hạ Long. Thời gian và hoạt động thực tế có thể thay đổi theo thời tiết, lịch vận hành và quy định của cơ quan quản lý.'],
            ],
        ],
    ],
    [
        'bay' => 'lan-ha',
        'category' => 'cave',
        'ord' => 6,
        'latitude' => 20.800413,
        'longitude' => 107.0971708,
        'coords_verified' => true,
        'coords_source' => 'Google Maps place "Dark Cave" supplied by the owner 2026-10-24 (maps.app.goo.gl/AEKAWeyVV1GkwW2b9)',
        'image' => ['link' => '/2026/10/21/cheo-kayak-qua-hang-da-voi-vinh-lan-ha.jpg'],
        'image_credit' => null,
        'itineraries' => ['en' => [1, 2], 'vi' => [5, 7]],
        'sources' => [$unesco, $catBaMab],
        'editor_notes' => "EN and VI rewritten 2026-10-24 from the editorial briefs (VI slug kept, H1 'Hang Sáng – Tối' via heading); slug dark-and-bright-cave -> dark-bright-cave (308 in next.config). Verified: GR2 times 15:30 (2D1N) / 15:00 (3D2N) and Gia Luan boarding (itinerary pages); kayak suspension from 1 Feb 2026 per Indochina Sails notice citing 483/TB-UBND (operator source, no official notice found). GR2 itinerary pages still say 'kayak or bamboo boat'. Coordinates from the owner's Google Maps pin (2026-10-24). Needs a photo of this exact cave: the hero is the owner's photo of kayaking through a limestone cave in Lan Ha Bay (media library 2026-10-21), and its alt text says only that. Not published: passage lengths, tide thresholds.",
        'en' => [
            'slug' => 'dark-bright-cave',
            'name' => 'Dark & Bright Cave',
            'image_alt' => 'Kayakers paddling through a limestone cave arch in Lan Ha Bay',
            'seo_title' => 'Dark & Bright Cave, Lan Ha Bay: Bamboo Boat & Visitor Guide',
            'seo_description' => 'Explore Dark & Bright Cave in Lan Ha Bay by bamboo boat. Discover its limestone passages, sheltered lagoon, tide conditions and current kayaking rules.',
            'summary' => 'Dark & Bright Cave, also known as Hang Sang Toi, is a pair of limestone passages in Lan Ha Bay, near Cat Ba Island. The best-known route leads beneath a low rock arch into a sheltered lagoon surrounded by steep, forested cliffs. Visitors explore the area by small rowing boat, getting a close view of the limestone landscape from the water.',
            'sources' => [
                ['title' => 'UNESCO World Heritage Centre – Ha Long Bay–Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'UNESCO – Cat Ba Biosphere Reserve', 'url' => 'https://www.unesco.org/en/mab/cat-ba'],
                ['title' => 'Indochina Sails – Continued Kayak Suspension in Lan Ha Bay', 'url' => 'https://www.indochinasails.com/en/blog/kayak-suspension-enhanced-bamboo-boat-experience-in-lan-ha-bay.html'],
                ['title' => 'Green Ruby Cruises – The Lan Ha Escape, 2 Days 1 Night', 'url' => 'https://greenrubycruises.com/itinerary/the-lan-ha-escape-2-days-1-night-4-2.html'],
                ['title' => 'Green Ruby Cruises – The Lan Ha Immersion, 3 Days 2 Nights', 'url' => 'https://greenrubycruises.com/itinerary/the-lan-ha-immersion-3-days-2-nights-4-1.html'],
            ],
            'content' => <<<'HTML'
<h2>Where is Dark &amp; Bright Cave?</h2>
<p>Dark &amp; Bright Cave lies in Lan Ha Bay, off the eastern side of Cat Ba Island in northern Vietnam.</p>
<p>The cave area is part of the Cat Ba Archipelago, where hundreds of limestone islands rise from the water. It is reached by boat and is a regular sightseeing stop on Lan Ha Bay cruises.</p>
<p>The wider archipelago has two important UNESCO designations. Cat Ba was recognised as a UNESCO Biosphere Reserve in 2004, and in 2023 the archipelago was added to the Ha Long Bay World Heritage property, now officially known as Ha Long Bay–Cat Ba Archipelago.</p>
<p>Unlike caves such as <a href="/destinations/sung-sot-cave">Sung Sot</a>, which visitors explore on foot, Dark &amp; Bright Cave is a water-level attraction. The main experience is travelling beneath the limestone arch and into the lagoon beyond.</p>
<h2>What makes Dark &amp; Bright Cave special?</h2>
<p>The appeal of Dark &amp; Bright Cave is the change in scenery as you pass beneath the rock.</p>
<p>Approaching by boat, you see a low opening at the base of a limestone island. Beyond it lies a lagoon enclosed by high cliffs, with trees and other vegetation growing along the rock faces.</p>
<p>The surrounding cliffs shelter the water, although conditions still depend on the weather and tide. From a small boat, you can look up at the limestone formations and follow the shoreline at a much slower pace than you would on a larger cruise ship.</p>
<p>The name refers to two contrasting passages. The darker passage receives little natural light, while the brighter opening leads into the lagoon.</p>
<p>Visitors should not assume that both passages are open. Access depends on water levels, safety restrictions and the routes currently authorised for sightseeing.</p>
<p>For many guests, the main attraction is the brighter passage and the quiet stretch of water beyond it.</p>
<h2>Dark Cave and Bright Cave: what’s the difference?</h2>
<p>Although the site is commonly called Dark &amp; Bright Cave, the two passages are not identical.</p>
<h3>Bright Cave (Hang Sang)</h3>
<p>Bright Cave is the passage most closely associated with the familiar photographs of this area.</p>
<p>A low limestone arch connects the surrounding bay with an enclosed lagoon. Small rowing boats travel beneath the rock ceiling before emerging into daylight on the other side.</p>
<p>The lagoon is bordered by steep limestone walls, with vegetation clinging to the cliffs above the water.</p>
<p>The experience is relatively gentle, although passengers may need to lower their heads when passing beneath the arch, depending on the tide.</p>
<h3>Dark Cave (Hang Toi)</h3>
<p>Dark Cave is the less illuminated passage, with a more enclosed interior and limited natural light.</p>
<p>Unlike Bright Cave, it is not necessarily accessible during regular sightseeing visits. Water levels and safety restrictions can limit entry, and the route may be closed altogether.</p>
<p>If your main reason for visiting is to explore the darker passage, check with your cruise operator beforehand. Booking a trip to Dark &amp; Bright Cave does not guarantee entry into both caves.</p>
<h2>Things to do at Dark &amp; Bright Cave</h2>
<h3>Take a bamboo boat through Bright Cave</h3>
<p>A locally rowed bamboo boat is the main way to experience the cave area under the current kayaking restrictions.</p>
<p>After putting on a life jacket and receiving boarding instructions, you sit in a small boat while a local rower guides it towards the limestone opening.</p>
<p>As you pass beneath the arch, the rock ceiling comes close overhead. The boat then emerges into the lagoon, where you can look around at the cliffs and vegetation before returning through the passage.</p>
<p>Because someone else handles the rowing, you can concentrate on the scenery and take photographs.</p>
<p>The ride is suitable for many visitors who prefer not to paddle, although getting into and out of the boat still requires some balance.</p>
<h3>Explore the sheltered lagoon</h3>
<p>The lagoon beyond Bright Cave is one of the main reasons people visit.</p>
<p>High limestone cliffs surround the water, leaving relatively little of the open bay visible from inside. Trees and shrubs grow from cracks in the rock, sometimes extending over the water’s edge.</p>
<p>From the boat, you can observe the changing shapes of the limestone walls and the reflections on the water.</p>
<p>There is no need to rush through the lagoon. The experience is about spending time close to the landscape rather than reaching a viewpoint or walking through a series of chambers.</p>
<h3>Kayaking when regulations permit</h3>
<p>Dark &amp; Bright Cave has long been known as a kayaking destination.</p>
<p>However, kayaking services in Lan Ha Bay, including the Dark &amp; Bright Cave area, remain subject to a temporary suspension under local regulations as of October 2026.</p>
<p>Until permission is restored, visitors should not expect to kayak through the cave. Authorised rowing-boat services may be offered instead.</p>
<p>If kayaking is an important part of your holiday, confirm the activity directly with your cruise operator before booking.</p>
<h2>Bamboo boat or kayak: what should visitors know?</h2>
<p>Historically, visitors could choose between paddling a kayak themselves and taking a bamboo boat rowed by a local boat operator.</p>
<p>The two experiences are different.</p>
<p>A kayak gives you more control over the pace and direction of travel, but requires physical effort and confidence handling a small boat.</p>
<p>A bamboo boat is more relaxed. Passengers remain seated while the rower navigates the cave passage and lagoon.</p>
<p>Under the current restrictions, bamboo boat visits are the more relevant option at Dark &amp; Bright Cave.</p>
<p>Activities may change if local authorities revise the rules, so the experience available on your travel date should always be confirmed in advance.</p>
<h2>How to get to Dark &amp; Bright Cave</h2>
<p>Dark &amp; Bright Cave is accessible by boat as part of a Lan Ha Bay sightseeing trip or overnight cruise.</p>
<p>Visitors may travel from Cat Ba Island or join cruises that begin with check-in at Tuan Chau International Marina. See <a href="/guides/hanoi-to-halong-bay">how to get from Hanoi to Halong Bay</a>.</p>
<p>The transfer arrangements depend on the operator and the ship’s boarding location.</p>
<p>Large cruise ships do not enter the cave. Instead, guests reach the designated boarding area and transfer to smaller boats for the water-level visit.</p>
<p>Life jackets are provided, and local staff assist with boarding and explain the permitted route.</p>
<p>Dark &amp; Bright Cave is included in the planned itineraries of <a href="/cruise/green-ruby-2-4.html">Green Ruby 2</a>, which operates its Lan Ha Bay programmes from the Gia Luan side of Cat Ba.</p>
<h2>How long does a visit to Dark &amp; Bright Cave take?</h2>
<p>Allow approximately 60–90 minutes for the full stop, including boat transfers, boarding, the rowing-boat trip and returning to the cruise.</p>
<p>The time spent inside the cave passage itself is much shorter. Most of the experience involves travelling around the lagoon and enjoying the surrounding scenery.</p>
<p>The exact duration depends on the sightseeing route, the number of boats using the area, the tide and the cruise schedule.</p>
<p>Your guide will confirm the available time before the activity begins.</p>
<h2>What to expect when you arrive</h2>
<p>You will usually arrive near the cave area on a sightseeing or transfer boat before boarding a smaller rowing boat.</p>
<p>The crew will explain where to sit, how to wear the life jacket and what to do when passing beneath the limestone arch.</p>
<p>At certain tide levels, the space between the water and the cave ceiling can be limited. Passengers may be asked to lean forward or lower their heads.</p>
<p>Once through the brighter passage, the boat enters the lagoon.</p>
<p>There is no conventional walking route through the cave, and the visit takes place on the water.</p>
<p>Although the lagoon is sheltered, several tour groups may arrive at similar times, particularly during busy travel periods. A quiet visit cannot be guaranteed.</p>
<h2>Accessibility and practical tips</h2>
<p>Visiting Dark &amp; Bright Cave requires transferring into a small boat, which may move slightly as passengers board.</p>
<p>This can be difficult for visitors with limited mobility. Bamboo boats require less physical effort than kayaks, but they are not necessarily wheelchair-accessible.</p>
<p>Before setting off:</p>
<ul>
<li>Wear the provided life jacket throughout the boat trip.</li>
<li>Follow the crew’s instructions when boarding and passing beneath low rock.</li>
<li>Keep phones, cameras and valuables in a waterproof bag.</li>
<li>Wear comfortable clothing and footwear suitable for boat transfers.</li>
<li>Remain seated while the boat is moving.</li>
<li>Do not touch or damage the limestone formations.</li>
<li>Keep the lagoon clean and take all rubbish back with you.</li>
</ul>
<p>Families travelling with young children should confirm age restrictions, life jacket sizes and boarding arrangements with their cruise operator.</p>
<h2>Best time to visit Dark &amp; Bright Cave</h2>
<p>Dark &amp; Bright Cave can be visited throughout the year when weather, tides and local operating conditions allow.</p>
<p>From April to October, temperatures are generally warmer, but this period also brings heavy rain, thunderstorms and the possibility of tropical storms.</p>
<p>From November to March, cooler weather can make the trip comfortable for sightseeing, although sitting in an open rowing boat may feel chilly on windy days.</p>
<p>For this particular attraction, the tide matters as much as the season.</p>
<p>The cave entrance sits close to the water, so high tide can reduce the clearance beneath the limestone arch. At certain water levels, entry may be restricted or temporarily suspended.</p>
<p>Cruise operators and local staff determine the safe route on the day. See <a href="/blog/travel-tips/best-time-to-visit-halong-bay.html">the best time to visit Halong Bay</a> for month-by-month conditions.</p>
<h2>Explore Dark &amp; Bright Cave with Green Ruby Cruises</h2>
<p>Dark &amp; Bright Cave is included in both planned Lan Ha Bay itineraries aboard <a href="/cruise/green-ruby-2-4.html">Green Ruby 2</a>.</p>
<p><strong><a href="/itinerary/the-lan-ha-escape-2-days-1-night-4-2.html">The Lan Ha Escape – 2 Days 1 Night</a></strong><br>A one-night cruise combining the Dark &amp; Bright Cave area with Lan Ha Bay’s limestone scenery and a planned visit to <a href="/destinations/ao-ech-lagoon">Ao Ech Lagoon</a>.</p>
<p><strong><a href="/itinerary/the-lan-ha-immersion-3-days-2-nights-4-1.html">The Lan Ha Immersion – 3 Days 2 Nights</a></strong><br>A longer journey that includes Dark &amp; Bright Cave, with an additional day planned for <a href="/destinations/viet-hai-village">Viet Hai Village</a> and a beach visit.</p>
<p><em>These are planned activities. Final sightseeing arrangements will be confirmed when Green Ruby’s sailings begin. Rowing-boat and kayaking options are subject to the regulations in force on the travel date, as well as weather, tides and operational conditions.</em></p>
<h2>Nearby places and related experiences</h2>
<h3><a href="/destinations/ao-ech-lagoon">Ao Ech Lagoon</a></h3>
<p>A sheltered area of Lan Ha Bay known for its limestone scenery and water-based activities, subject to local regulations.</p>
<h3><a href="/destinations/viet-hai-village">Viet Hai Village</a></h3>
<p>A small village on Cat Ba Island, surrounded by forested hills and reached on tours that combine boat travel with a walk, bicycle ride or electric buggy.</p>
<h3>Ba Trai Dao Beach</h3>
<p>A scenic area of Lan Ha Bay known for its group of limestone islets and sandy beaches. Swimming and access depend on local conditions and authorised routes.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Lan Ha Bay, near Cat Ba Island, Hai Phong, Vietnam'],
                ['label' => 'Also known as', 'value' => 'Hang Sang Toi, Dark and Bright Cave'],
                ['label' => 'Type', 'value' => 'Water-level limestone passages and enclosed lagoon'],
                ['label' => 'Main attraction', 'value' => 'Boat passage beneath a limestone arch and lagoon scenery'],
                ['label' => 'Access', 'value' => 'By sightseeing boat or cruise, followed by a small rowing boat'],
                ['label' => 'Typical visit', 'value' => 'Approximately 60–90 minutes'],
                ['label' => 'Kayaking', 'value' => 'Temporarily suspended under local regulations as of October 2026'],
                ['label' => 'Walking access', 'value' => 'No conventional walking route through the cave'],
                ['label' => 'Conditions', 'value' => 'Dependent on tides, weather and local operating rules'],
                ['label' => 'UNESCO status', 'value' => 'Part of the Ha Long Bay–Cat Ba Archipelago World Heritage Site'],
            ],
            'faq' => [
                ['q' => 'Where is Dark & Bright Cave?', 'a' => 'Dark & Bright Cave is in Lan Ha Bay, near Cat Ba Island in northern Vietnam. It belongs to the Cat Ba Archipelago, which forms part of the UNESCO-listed Ha Long Bay–Cat Ba Archipelago World Heritage Site.'],
                ['q' => 'What is special about Dark & Bright Cave?', 'a' => 'The site is known for its low limestone passages and a sheltered lagoon enclosed by steep, forested cliffs. Visitors can pass beneath a rock arch by small boat and explore the lagoon from water level.'],
                ['q' => 'What is the difference between Dark Cave and Bright Cave?', 'a' => 'Bright Cave has an opening that leads into a lagoon illuminated by daylight. Dark Cave is more enclosed and receives less natural light. Access to the darker passage may be restricted, so visiting the area does not guarantee entry into both caves.'],
                ['q' => 'Can you kayak at Dark & Bright Cave?', 'a' => 'Kayaking has traditionally been offered here, but the activity remains suspended under local regulations as of October 2026. Visitors should confirm whether any restrictions have changed before travelling. Rowed bamboo boat visits may be available as an alternative.'],
                ['q' => 'Do I need kayaking experience to visit Dark & Bright Cave?', 'a' => 'No kayaking experience is needed for a bamboo boat ride because a local boat operator handles the rowing. Kayaking is currently subject to restrictions and should not be assumed to be available.'],
                ['q' => 'Is Dark & Bright Cave suitable for people who dislike enclosed spaces?', 'a' => 'The brighter passage is relatively short, but its ceiling can feel low, particularly at higher tide levels. You may need to duck while passing underneath. Visitors uncomfortable in confined spaces should discuss the route with their guide before boarding.'],
                ['q' => 'Can children visit Dark & Bright Cave?', 'a' => 'Children can generally join permitted rowing-boat activities when suitable life jackets are available and the operator’s age and safety requirements are met. Young children should be accompanied by an adult.'],
                ['q' => 'How long does it take to visit Dark & Bright Cave?', 'a' => 'Allow around 60–90 minutes for the full stop, including transfers and the boat trip. The exact duration depends on local conditions and the cruise itinerary.'],
                ['q' => 'When does Green Ruby plan to visit Dark & Bright Cave?', 'a' => 'Dark & Bright Cave is included in both Green Ruby 2’s planned Lan Ha Bay itineraries. The published 2-day/1-night itinerary schedules the visit for approximately 3:30 PM on the first day, while the 3-day/2-night itinerary currently lists it at approximately 3:00 PM on the first day. Activities and timings remain subject to local regulations, weather, tides and operational requirements.'],
            ],
        ],
        'vi' => [
            'slug' => 'hang-sang-toi',
            'name' => 'Hang Sáng Tối',
            'heading' => 'Hang Sáng – Tối',
            'image_alt' => 'Du khách chèo kayak qua vòm hang đá vôi trên vịnh Lan Hạ',
            'seo_title' => 'Hang Sáng Tối Lan Hạ: Đi Đò Nan & Kinh Nghiệm Tham Quan',
            'seo_description' => 'Hang Sáng Tối ở đâu, có gì đặc biệt? Tìm hiểu trải nghiệm đi đò nan, áng nước giữa núi đá, quy định chèo kayak và cách tham quan vịnh Lan Hạ.',
            'summary' => 'Hang Sáng – Tối nằm trên vịnh Lan Hạ, gần đảo Cát Bà, nổi tiếng với những vòm đá vôi sát mặt nước và áng nước được núi đá bao quanh. Du khách có thể ngồi đò nan do người địa phương chèo, đi qua cửa Hang Sáng để ngắm những vách núi dựng đứng và cảnh vật bên trong. Đây là một trong những điểm tham quan giúp du khách quan sát cảnh quan đá vôi của vịnh Lan Hạ ở khoảng cách gần.',
            'sources' => [
                ['title' => 'UNESCO – Vịnh Hạ Long và Quần đảo Cát Bà', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'UNESCO – Khu dự trữ sinh quyển Cát Bà', 'url' => 'https://www.unesco.org/en/mab/cat-ba'],
                ['title' => 'Indochina Sails – Thông tin tạm dừng kayak tại vịnh Lan Hạ', 'url' => 'https://www.indochinasails.com/en/blog/kayak-suspension-enhanced-bamboo-boat-experience-in-lan-ha-bay.html'],
                ['title' => 'Green Ruby – 2 ngày 1 đêm: Một Thoáng Lan Hạ', 'url' => 'https://greenrubycruises.com/vi/hanh-trinh/2-ngay-1-dem-mot-thoang-lan-ha-2-5.html'],
                ['title' => 'Green Ruby – 3 ngày 2 đêm: Dấu Ấn Lan Hạ', 'url' => 'https://greenrubycruises.com/vi/hanh-trinh/3-ngay-2-dem-dau-an-lan-ha-2-7.html'],
            ],
            'content' => <<<'HTML'
<h2>Hang Sáng – Tối ở đâu?</h2>
<p>Hang Sáng – Tối nằm trên vịnh Lan Hạ, ở phía đông đảo Cát Bà, thuộc thành phố Hải Phòng.</p>
<p>Khu vực này nằm trong quần đảo Cát Bà, nơi có hàng trăm đảo đá vôi lớn nhỏ, nhiều vũng nước kín và các hang xuyên núi nằm sát mặt biển.</p>
<p>Năm 2004, quần đảo Cát Bà được UNESCO công nhận là Khu dự trữ sinh quyển thế giới. Đến năm 2023, quần đảo được bổ sung vào Di sản Thiên nhiên Thế giới vịnh Hạ Long, hình thành Di sản Thiên nhiên Thế giới vịnh Hạ Long – quần đảo Cát Bà.</p>
<p>Khác với những hang động có lối đi bộ như <a href="/vi/dia-diem-tham-quan/hang-sung-sot">hang Sửng Sốt</a>, Hang Sáng – Tối được tham quan chủ yếu bằng thuyền nhỏ. Trải nghiệm nổi bật nhất là đi qua vòm đá tự nhiên để vào áng nước nằm giữa các dãy núi đá vôi.</p>
<h2>Hang Sáng – Tối có gì đặc biệt?</h2>
<p>Điều thú vị nhất khi đến Hang Sáng – Tối là sự thay đổi của khung cảnh sau khi thuyền đi qua cửa hang.</p>
<p>Từ bên ngoài, cửa Hang Sáng là một vòm đá nằm thấp, gần sát mặt nước. Khi đò đi qua, phía trước mở ra một khoảng nước được bao quanh bởi những vách núi đá vôi cao, có cây xanh mọc dọc theo các khe đá.</p>
<p>Áng nước phía trong được các dãy núi che chắn nên thường khá êm. Từ đò nhỏ, du khách có thể quan sát những mảng đá vôi, rễ cây bám vào vách núi và bóng cây phản chiếu trên mặt nước.</p>
<p>Khu vực này có hai lối hang mang đặc điểm khác nhau. Hang Sáng thông ra khoảng nước có ánh sáng tự nhiên, còn Hang Tối kín và ít ánh sáng hơn.</p>
<p>Tuy nhiên, không phải lúc nào cả hai hang cũng được phép tham quan. Việc đi qua từng lối phụ thuộc vào thủy triều, điều kiện an toàn và tuyến hoạt động được cơ quan quản lý cho phép.</p>
<p>Trong các chương trình tham quan thông thường, Hang Sáng và áng nước phía sau là phần trải nghiệm chính.</p>
<h2>Hang Sáng và Hang Tối khác nhau thế nào?</h2>
<p>Mặc dù thường được gọi chung là Hang Sáng – Tối, hai hang có đặc điểm và điều kiện tiếp cận khác nhau.</p>
<h3>Hang Sáng – Lối vào áng nước giữa núi đá</h3>
<p>Hang Sáng là nơi du khách thường đi qua bằng đò nan trong hành trình tham quan.</p>
<p>Cửa hang có dạng vòm đá thấp, nối vùng nước bên ngoài với một áng nước gần như được các vách núi bao quanh.</p>
<p>Khi đi qua vòm hang, người ngồi trên đò có thể phải cúi đầu tùy vào mực nước thủy triều. Ra khỏi cửa hang, không gian phía trước mở rộng, với ánh sáng tự nhiên chiếu xuống mặt nước.</p>
<p>Những vách đá vôi dựng đứng, cây xanh và mặt nước tương đối yên ả là hình ảnh đặc trưng của khu vực này.</p>
<h3>Hang Tối – Lối hang ít ánh sáng</h3>
<p>Hang Tối có không gian kín hơn và nhận được ít ánh sáng tự nhiên.</p>
<p>So với Hang Sáng, việc tiếp cận Hang Tối khó khăn hơn do điều kiện bên trong và sự thay đổi của mực nước. Hang không phải lúc nào cũng mở cho các hoạt động tham quan thông thường.</p>
<p>Vì vậy, khi đặt chuyến đi có điểm dừng Hang Sáng – Tối, bạn không nên mặc định rằng mình sẽ được đi xuyên qua cả hai hang.</p>
<p>Nếu đặc biệt muốn tìm hiểu Hang Tối, hãy hỏi trước đơn vị tổ chức về tình trạng hoạt động và tuyến tham quan được phép.</p>
<h2>Những hoạt động nên trải nghiệm tại Hang Sáng – Tối</h2>
<h3>Ngồi đò nan đi qua Hang Sáng</h3>
<p>Đi đò nan là hình thức tham quan phù hợp nhất tại Hang Sáng – Tối trong thời gian hoạt động kayak đang bị tạm dừng.</p>
<p>Sau khi mặc áo phao và nghe hướng dẫn, du khách lên đò nhỏ do người lái đò địa phương chèo.</p>
<p>Đò di chuyển chậm đến cửa hang, đi qua bên dưới vòm đá rồi tiếp tục vào áng nước phía trong.</p>
<p>Khi đi qua cửa hang, bạn có thể phải cúi người vì khoảng cách từ mặt nước đến trần đá không quá cao.</p>
<p>Qua khỏi vòm đá, đò đưa khách tham quan khu vực áng nước trước khi quay trở ra.</p>
<p>Vì có người chèo đò, du khách không cần tự điều khiển thuyền. Bạn có thể dành thời gian ngắm cảnh, chụp ảnh và quan sát những vách đá vôi xung quanh.</p>
<h3>Ngắm cảnh trong áng nước</h3>
<p>Áng nước phía sau Hang Sáng là điểm hấp dẫn nhất của chuyến tham quan.</p>
<p>Các vách núi đá vôi bao quanh tạo nên một khoảng nước tương đối tách biệt với vùng vịnh bên ngoài. Từ đò nhỏ, bạn có thể nhìn lên những sườn núi phủ cây và quan sát các khối đá ở khoảng cách gần.</p>
<p>Không giống những điểm tham quan cần leo núi hoặc đi bộ qua hang, trải nghiệm tại đây khá nhẹ nhàng. Phần lớn thời gian du khách ngồi trên đò, đi chậm qua các khu vực được phép.</p>
<p>Đây cũng là nơi phù hợp để chụp ảnh cảnh quan vịnh Lan Hạ từ mặt nước, đặc biệt khi trời quang và mặt nước ít gợn sóng.</p>
<h3>Chèo kayak khi được phép hoạt động trở lại</h3>
<p>Hang Sáng – Tối từng là một trong những điểm chèo kayak quen thuộc trên vịnh Lan Hạ.</p>
<p>Trước đây, du khách có thể tự chèo kayak qua cửa hang và khám phá áng nước phía trong theo tuyến được hướng dẫn.</p>
<p>Tuy nhiên, hoạt động chèo kayak trên vịnh Lan Hạ đang bị tạm dừng theo quy định của địa phương, bao gồm khu vực Hang Sáng – Tối.</p>
<p>Tính đến ngày rà soát bài viết, chưa có thông tin xác nhận việc cho phép hoạt động này trở lại.</p>
<p>Vì vậy, du khách không nên mặc định rằng hành trình tham quan Hang Sáng – Tối sẽ có kayak. Các hoạt động thực tế cần được xác nhận với đơn vị tổ chức trước chuyến đi.</p>
<h2>Nên đi đò nan hay chèo kayak tại Hang Sáng – Tối?</h2>
<p>Trước đây, du khách có thể lựa chọn giữa tự chèo kayak và ngồi đò nan do người địa phương chèo.</p>
<p>Hai hình thức mang đến trải nghiệm khác nhau.</p>
<p>Với kayak, bạn tự điều khiển thuyền và cần dùng sức để chèo. Hoạt động phù hợp với người thích vận động và muốn chủ động khám phá mặt nước.</p>
<p>Với đò nan, người lái đò phụ trách di chuyển. Du khách chỉ cần ngồi trên thuyền để ngắm cảnh, chụp ảnh và nghe hướng dẫn.</p>
<p>Trong thời gian kayak bị tạm dừng, đò nan là phương án tham quan phù hợp với quy định hiện hành.</p>
<p>Nếu các quy định được điều chỉnh trong tương lai, bạn nên kiểm tra lại hoạt động được phép trước khi đặt tour.</p>
<h2>Cách đi đến Hang Sáng – Tối</h2>
<p>Hang Sáng – Tối chỉ có thể tiếp cận bằng đường thủy.</p>
<p>Du khách thường đến đây trong các chuyến tham quan vịnh Lan Hạ hoặc hành trình du thuyền nghỉ đêm.</p>
<p>Tùy chương trình, khách có thể xuất phát từ khu vực Cát Bà hoặc làm thủ tục tại cảng Tuần Châu rồi di chuyển sang vịnh Lan Hạ. Xem thêm <a href="/vi/cam-nang/ha-noi-di-ha-long">cách đi từ Hà Nội đến Hạ Long</a>.</p>
<p>Tàu du lịch không trực tiếp đi xuyên qua hang. Khi đến điểm tham quan, du khách được hướng dẫn chuyển sang đò nan tại vị trí đón khách được phép.</p>
<p>Nhân viên hỗ trợ mặc áo phao, lên xuống đò và phổ biến những lưu ý an toàn trước khi khởi hành.</p>
<p>Hang Sáng – Tối nằm trong lịch trình dự kiến của du thuyền <a href="/vi/du-thuyen/green-ruby-2-2.html">Green Ruby 2</a>, phục vụ các hành trình nghỉ đêm trên vịnh Lan Hạ.</p>
<h2>Tham quan Hang Sáng – Tối mất bao lâu?</h2>
<p>Thông thường, du khách nên dành khoảng 60–90 phút cho toàn bộ điểm dừng tại Hang Sáng – Tối, bao gồm thời gian di chuyển sang khu vực lên đò, tham quan và quay trở lại tàu.</p>
<p>Thời gian đi qua vòm hang khá ngắn. Phần lớn chuyến tham quan dành cho việc đi đò quanh áng nước, ngắm cảnh và chụp ảnh.</p>
<p>Nếu có nhiều đoàn đến cùng lúc, du khách có thể phải chờ đến lượt lên đò.</p>
<p>Thời gian thực tế còn phụ thuộc vào thủy triều, thời tiết và lịch trình của đơn vị tổ chức.</p>
<h2>Khi đến Hang Sáng – Tối cần lưu ý gì?</h2>
<p>Điểm cần chú ý nhất là vòm Hang Sáng nằm khá thấp so với mặt nước.</p>
<p>Vào những thời điểm thủy triều lên cao, khoảng trống bên dưới vòm đá có thể giảm. Người ngồi trên đò phải cúi thấp đầu khi đi qua, hoặc hoạt động có thể bị tạm dừng nếu không đảm bảo an toàn.</p>
<p>Trong suốt chuyến tham quan, du khách cần tuân thủ hướng dẫn của người chèo đò và nhân viên tại điểm đến.</p>
<p>Một số điều nên chuẩn bị:</p>
<ul>
<li>Mặc áo phao trong suốt thời gian đi đò.</li>
<li>Cho điện thoại, máy ảnh và vật dụng có giá trị vào túi chống nước.</li>
<li>Mặc quần áo thoải mái, phù hợp với hoạt động trên mặt nước.</li>
<li>Giữ thăng bằng khi bước từ bến hoặc tàu sang đò nhỏ.</li>
<li>Không đứng lên hoặc nghiêng người quá nhiều khi đò đang di chuyển.</li>
<li>Không chạm vào những khối đá hoặc làm hư hại cảnh quan trong hang.</li>
<li>Không xả rác xuống nước.</li>
</ul>
<p>Hang Sáng – Tối không có lối đi bộ xuyên hang dành cho du khách. Hoạt động tham quan chủ yếu diễn ra trên mặt nước.</p>
<h2>Trẻ em và người lớn tuổi có tham quan Hang Sáng – Tối được không?</h2>
<p>Đi đò nan thường phù hợp với nhiều nhóm khách vì không đòi hỏi phải tự chèo.</p>
<p>Trẻ em có thể tham gia nếu đáp ứng quy định của đơn vị vận hành, có áo phao đúng kích cỡ và được người lớn đi cùng.</p>
<p>Người lớn tuổi cũng có thể lựa chọn đò nan để hạn chế vận động. Tuy nhiên, việc chuyển từ tàu hoặc bến sang đò nhỏ vẫn đòi hỏi giữ thăng bằng.</p>
<p>Người có vấn đề về vận động hoặc sử dụng xe lăn nên hỏi trước đơn vị tổ chức về khả năng hỗ trợ lên xuống đò. Hình thức tham quan này không mặc nhiên phù hợp với mọi tình trạng sức khỏe.</p>
<h2>Nên tham quan Hang Sáng – Tối vào tháng mấy?</h2>
<p>Hang Sáng – Tối có thể tham quan quanh năm nếu thời tiết, thủy triều và hoạt động tàu du lịch cho phép.</p>
<p>Từ tháng 4 đến tháng 10, thời tiết thường ấm hơn, thuận tiện cho các hoạt động ngoài trời. Tuy nhiên, đây cũng là thời gian có thể xảy ra mưa lớn, dông và bão.</p>
<p>Từ tháng 11 đến tháng 3, thời tiết mát hoặc lạnh hơn. Nếu ngồi đò vào những ngày nhiều gió, bạn nên mang theo áo khoác mỏng.</p>
<p>Đối với Hang Sáng – Tối, thủy triều có ảnh hưởng trực tiếp đến khả năng đi qua cửa hang. Khi nước dâng cao, khoảng cách giữa trần đá và mặt nước có thể không đủ để đò di chuyển an toàn.</p>
<p>Vì vậy, một ngày thời tiết đẹp chưa chắc đã bảo đảm du khách có thể vào hang. Đơn vị vận hành sẽ căn cứ vào cả mực nước, tình trạng thời tiết và các quy định tại điểm tham quan. Xem thêm <a href="/vi/blog/hanh-trinh/di-ha-long-mua-nao-dep.html">đi Hạ Long mùa nào đẹp</a>.</p>
<h2>Tham quan Hang Sáng – Tối cùng Green Ruby Cruises</h2>
<p>Hang Sáng – Tối là một trong những điểm tham quan dự kiến của du thuyền Green Ruby 2 trong các hành trình nghỉ đêm trên vịnh Lan Hạ.</p>
<p><strong><a href="/vi/hanh-trinh/2-ngay-1-dem-mot-thoang-lan-ha-2-5.html">2 ngày 1 đêm – Một Thoáng Lan Hạ</a></strong><br>Hành trình nghỉ đêm kết hợp tham quan Hang Sáng – Tối, ngắm cảnh vịnh Lan Hạ và các hoạt động khác theo chương trình.</p>
<p><strong><a href="/vi/hanh-trinh/3-ngay-2-dem-dau-an-lan-ha-2-7.html">3 ngày 2 đêm – Dấu Ấn Lan Hạ</a></strong><br>Hành trình dài hơn với thêm thời gian khám phá <a href="/vi/dia-diem-tham-quan/lang-viet-hai">làng Việt Hải</a> trên đảo Cát Bà, các khu vực cảnh quan và bãi biển được phép tiếp cận.</p>
<p><em>Các điểm dừng trên thuộc lịch trình dự kiến của Green Ruby 2. Hoạt động tham quan thực tế, bao gồm hình thức đi đò hoặc chèo kayak, sẽ tuân theo quy định có hiệu lực tại thời điểm khởi hành và có thể thay đổi do thời tiết, thủy triều hoặc yêu cầu vận hành.</em></p>
<h2>Những địa điểm gần và liên quan</h2>
<h3><a href="/vi/dia-diem-tham-quan/ao-ech">Ao Ếch</a></h3>
<p>Khu vực mặt nước được bao quanh bởi những dãy núi đá vôi trên vịnh Lan Hạ, từng được biết đến với các hoạt động chèo kayak. Việc tổ chức hoạt động trên mặt nước phụ thuộc vào quy định hiện hành.</p>
<h3><a href="/vi/dia-diem-tham-quan/lang-viet-hai">Làng Việt Hải</a></h3>
<p>Ngôi làng nằm trên đảo Cát Bà, được bao quanh bởi rừng và núi đá. Du khách có thể khám phá làng bằng xe đạp, xe điện hoặc đi bộ tùy chương trình tham quan.</p>
<h3>Bãi Ba Trái Đào</h3>
<p>Khu vực nổi tiếng với những đảo đá vôi có hình dáng đặc biệt và các bãi cát nhỏ. Khả năng tiếp cận bãi biển phụ thuộc vào thời tiết, thủy triều và tuyến tham quan được phép.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Vịnh Lan Hạ, khu vực quần đảo Cát Bà, Hải Phòng'],
                ['label' => 'Tên tiếng Anh', 'value' => 'Dark & Bright Cave'],
                ['label' => 'Loại hình', 'value' => 'Hang đá vôi sát mặt nước, áng nước kín'],
                ['label' => 'Điểm nổi bật', 'value' => 'Vòm Hang Sáng và áng nước giữa các vách núi'],
                ['label' => 'Hoạt động tham quan', 'value' => 'Đi đò nan, ngắm cảnh'],
                ['label' => 'Chèo kayak', 'value' => 'Đang tạm dừng theo quy định được công bố'],
                ['label' => 'Cách đến', 'value' => 'Tàu tham quan hoặc du thuyền, chuyển sang đò nhỏ'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 60–90 phút (ước tính)'],
                ['label' => 'Đi bộ xuyên hang', 'value' => 'Không'],
                ['label' => 'Điều kiện tham quan', 'value' => 'Phụ thuộc thủy triều, thời tiết và quy định an toàn'],
                ['label' => 'Di sản UNESCO', 'value' => 'Thuộc khu vực vịnh Hạ Long – quần đảo Cát Bà'],
            ],
            'faq' => [
                ['q' => 'Hang Sáng – Tối nằm ở đâu?', 'a' => 'Hang Sáng – Tối nằm trên vịnh Lan Hạ, gần đảo Cát Bà, thuộc thành phố Hải Phòng. Khu vực này nằm trong quần đảo Cát Bà, một phần của Di sản Thiên nhiên Thế giới vịnh Hạ Long – quần đảo Cát Bà.'],
                ['q' => 'Hang Sáng – Tối có gì đặc biệt?', 'a' => 'Đây là khu vực có hai lối hang đá vôi nằm sát mặt nước, trong đó Hang Sáng dẫn vào một áng nước được những vách núi cao bao quanh. Du khách thường đi đò nan qua vòm đá để ngắm cảnh bên trong.'],
                ['q' => 'Hang Sáng và Hang Tối khác nhau thế nào?', 'a' => 'Hang Sáng có lối thông ra áng nước nhận được ánh sáng tự nhiên. Hang Tối kín hơn và ít ánh sáng, việc tiếp cận có thể bị hạn chế. Không phải chuyến tham quan nào cũng được đi qua cả hai hang.'],
                ['q' => 'Hang Sáng – Tối có chèo kayak được không?', 'a' => 'Hoạt động chèo kayak tại khu vực này đang bị tạm dừng theo quy định địa phương tính đến tháng 10/2026. Du khách có thể tham quan bằng đò nan khi dịch vụ được phép hoạt động và điều kiện an toàn bảo đảm. Nên kiểm tra quy định mới nhất trước chuyến đi.'],
                ['q' => 'Chưa biết chèo kayak có tham quan Hang Sáng – Tối được không?', 'a' => 'Có. Bạn không cần biết chèo kayak nếu tham quan bằng đò nan. Người lái đò sẽ điều khiển thuyền trong suốt chuyến đi.'],
                ['q' => 'Người sợ không gian hẹp có nên đi Hang Sáng – Tối không?', 'a' => 'Vòm Hang Sáng tương đối thấp, người ngồi trên đò có thể phải cúi đầu khi đi qua. Đoạn qua vòm không dài, nhưng vẫn có thể gây khó chịu cho người sợ không gian kín. Bạn nên trao đổi trước với hướng dẫn viên để cân nhắc có tham gia hay không.'],
                ['q' => 'Trẻ em có đi Hang Sáng – Tối được không?', 'a' => 'Trẻ em có thể tham gia đi đò nan nếu đáp ứng các yêu cầu về độ tuổi, áo phao và người đi kèm của đơn vị vận hành. Phụ huynh nên xác nhận điều kiện cụ thể trước chuyến đi.'],
                ['q' => 'Tham quan Hang Sáng – Tối mất bao lâu?', 'a' => 'Thông thường nên dành khoảng 60–90 phút cho toàn bộ điểm dừng, bao gồm thời gian lên xuống đò và tham quan áng nước. Thời gian thực tế tùy lịch trình và điều kiện trong ngày.'],
                ['q' => 'Green Ruby dự kiến ghé Hang Sáng – Tối khi nào?', 'a' => 'Hang Sáng – Tối nằm trong cả hai hành trình dự kiến của Green Ruby 2 trên vịnh Lan Hạ: 2 ngày 1 đêm – Một Thoáng Lan Hạ và 3 ngày 2 đêm – Dấu Ấn Lan Hạ. Thời gian ghé dự kiến vào buổi chiều ngày đầu tiên. Điểm dừng, giờ tham quan và hình thức trải nghiệm sẽ được xác nhận theo lịch vận hành, thời tiết, thủy triều và quy định địa phương.'],
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
        'editor_notes' => "EN rewritten 2026-10-24 from the editorial brief. Verified: ~5 km pier-to-village road (CatBa.com, Hai Phong News), GR2 3D2N day 2 10:00 village / 12:00 lunch / 14:00 beach (itinerary page). Brief's Hai Phong source used the oauth.congdoanhaiphong.vn mirror (bad SSL); replaced with haiphongnews.gov.vn. Old note: Not published: distance from the pier to the village (sources vary), household numbers, buggy/bike fees. 2025 administrative changes renamed local units; copy avoids commune/district names.",
        'en' => [
            'slug' => 'viet-hai-village',
            'name' => 'Viet Hai Village',
            'image_alt' => 'Paddy fields and forested limestone hills in the valley of Viet Hai, Cat Ba Island',
            'seo_title' => 'Viet Hai Village, Cat Ba: Cycling, Local Life & Visitor Guide',
            'seo_description' => 'Visit Viet Hai Village on Cat Ba Island: ride 5 km from the pier by bike or electric cart to rice fields, village lunch and local life near Lan Ha Bay.',
            'summary' => 'Viet Hai Village is a small rural community on Cat Ba Island, surrounded by forested limestone hills near Lan Ha Bay. Most visitors arrive by boat, then travel about 5 km inland by bicycle, electric cart or on foot. The journey leads through a narrow valley to a working village with rice fields, gardens, family homes and small local businesses.',
            'sources' => [
                ['title' => 'UNESCO – Cat Ba Biosphere Reserve', 'url' => 'https://www.unesco.org/en/mab/cat-ba'],
                ['title' => 'UNESCO World Heritage Centre – Ha Long Bay–Cat Ba Archipelago', 'url' => 'https://whc.unesco.org/en/list/672'],
                ['title' => 'CatBa.com – Viet Hai Village Visitor Guide', 'url' => 'https://catba.com/destinations/viet-hai-village/'],
                ['title' => 'Hai Phong News – Viet Hai, an Island Within an Island', 'url' => 'https://haiphongnews.gov.vn/tourism/viet-hai-an-island-within-an-island-an-ancient-village-forgotten-by-time-in-the-heart-of-the-cat-ba-heritage-site-ct874.html'],
                ['title' => 'Green Ruby Cruises – The Lan Ha Immersion, 3 Days 2 Nights', 'url' => 'https://greenrubycruises.com/itinerary/the-lan-ha-immersion-3-days-2-nights-4-1.html'],
            ],
            'content' => <<<'HTML'
<h2>Where is Viet Hai Village?</h2>
<p>Viet Hai Village lies in a valley on the eastern side of Cat Ba Island, within the Cat Ba National Park area in Hai Phong, northern Vietnam.</p>
<p>The village is surrounded by limestone mountains and forest, with access to the coast through a narrow route leading towards Lan Ha Bay.</p>
<p>Its location kept Viet Hai relatively isolated for much of its history. Before road access and tourism services developed, residents relied heavily on boats and walking trails through the surrounding hills.</p>
<p>Cat Ba National Park was established in 1986. The wider Cat Ba Archipelago was recognised as a UNESCO Biosphere Reserve in 2004 and became part of the Ha Long Bay–Cat Ba Archipelago World Heritage Site in 2023.</p>
<p>Today, Viet Hai is a regular stop on longer Lan Ha Bay cruises, particularly those offering cycling and village visits.</p>
<h2>What makes Viet Hai Village worth visiting?</h2>
<p>Viet Hai offers a different experience from spending the day on a cruise ship.</p>
<p>After travelling between the limestone islands of Lan Ha Bay, you arrive at a small pier and follow a road inland. The route passes through forested hills before opening into a valley with cultivated fields, vegetable gardens and houses along the roadside.</p>
<p>The village is still home to local families. Agriculture remains part of daily life, alongside fishing-related work, homestays, small restaurants and tourism services.</p>
<p>It is not an abandoned village or a place preserved exactly as it was decades ago. Modern buildings and visitor facilities now sit alongside fields and older homes.</p>
<p>That mixture is part of what makes the visit interesting. Viet Hai shows how a rural community on Cat Ba Island has adapted as tourism has become another source of income.</p>
<p>For cruise passengers, the change of scenery is immediate: from open water and limestone islands to a narrow country road, farmland and wooded hills.</p>
<h2>Life in Viet Hai Village</h2>
<p>Viet Hai has long been a small community whose location made everyday travel more difficult than in villages connected to Cat Ba’s main roads.</p>
<p>Residents traditionally depended on a combination of farming, fishing and the natural resources around them.</p>
<p>Rice fields, vegetable gardens and fruit trees still form part of the landscape, although the amount under cultivation and the appearance of the fields change with the season.</p>
<p>As visitor numbers increased, some families began offering homestays, meals, bicycle rentals and other services.</p>
<p>Tourism has brought new sources of income, but it has also changed the village. Not every building is traditional, and not every activity visitors encounter reflects the way people lived in the past.</p>
<p>Viet Hai is best understood as a functioning rural community rather than an outdoor museum.</p>
<h2>Things to do in Viet Hai Village</h2>
<h3>Cycle from Viet Hai Pier to the village</h3>
<p>Cycling is one of the most enjoyable ways to explore Viet Hai.</p>
<p>From the pier, a paved road runs roughly 5 km inland, passing between limestone hills and through forested stretches before reaching the village.</p>
<p>Parts of the route are relatively gentle, although there are bends and some gradients. The ride is manageable for many recreational cyclists, but the heat can make it more tiring during summer.</p>
<p>As you approach the village, the scenery changes from rocky hills and dense vegetation to open fields, gardens and houses.</p>
<p>Cycling lets you experience this transition at a comfortable pace, with opportunities to stop where it is safe and permitted.</p>
<p>You do not need to be an experienced cyclist, but you should be confident riding a bicycle and following your guide’s instructions.</p>
<h3>Explore Viet Hai by electric cart</h3>
<p>If you prefer not to cycle, an electric cart is a practical alternative.</p>
<p>These small vehicles travel along the road between the pier and the village, allowing passengers to see the landscape without the physical effort of cycling.</p>
<p>An electric cart can be particularly useful for families with young children, older visitors or anyone who finds longer walks difficult.</p>
<p>The journey follows much of the same route used by cyclists, so you can still enjoy the views of the forest, limestone hills and farmland.</p>
<p>Availability and seating arrangements depend on the local operator and your cruise programme.</p>
<h3>Walk through the village</h3>
<p>Once in Viet Hai, you can spend time walking along the village lanes.</p>
<p>You may see rice fields, vegetable plots, small gardens, traditional houses and more recent buildings used by local families or tourism businesses.</p>
<p>There is no single monument or landmark that defines the visit. Much of its appeal lies in looking around, observing the countryside and learning about the community from your guide.</p>
<p>Keep to public roads and permitted paths. Agricultural fields and family gardens are working spaces, not open areas for visitors to wander through.</p>
<h3>Enjoy a local Vietnamese lunch</h3>
<p>Some Lan Ha Bay cruise itineraries include lunch in Viet Hai.</p>
<p>Depending on the restaurant and programme, the meal may feature Vietnamese home-style dishes prepared with vegetables, rice, fish or other locally available ingredients.</p>
<p>Menus vary, and not every ingredient will necessarily come from the village itself.</p>
<p>A village lunch offers a welcome change from eating on board, particularly when it follows a morning of cycling or walking.</p>
<p>If you have dietary restrictions or food allergies, inform your cruise operator in advance.</p>
<h3>Learn about community tourism</h3>
<p>Viet Hai has developed homestays, restaurants and visitor activities alongside its traditional livelihoods.</p>
<p>A guided visit can help explain how local families have adapted to tourism and how their income now comes from several different activities.</p>
<p>Some households welcome visitors, while others remain private homes. Access depends on the arrangements made by your guide and the residents themselves.</p>
<p>Rather than expecting a staged display of rural life, treat the visit as an opportunity to understand how people live in this particular part of Cat Ba.</p>
<h2>Cycling or electric cart: which should you choose?</h2>
<p>Both options take visitors along the main route between Viet Hai Pier and the village, but they offer different levels of activity.</p>
<p><strong>Choose a bicycle</strong> if you enjoy cycling, want to experience the landscape at your own pace and are comfortable riding on a road with occasional slopes.</p>
<p><strong>Choose an electric cart</strong> if you would rather avoid physical exertion, are travelling with younger children or prefer a seated journey.</p>
<p>Walking all the way from the pier is also possible, but the distance is substantial for a short cruise stop. At around 5 km each way, it takes more time than most standard itineraries allow.</p>
<p>For visitors who want to take photographs or explore the village on foot, a practical option is to use a bicycle or electric cart for the journey inland and spend the remaining time walking around the village.</p>
<h2>How to get to Viet Hai Village</h2>
<p>Most cruise visitors reach Viet Hai from Lan Ha Bay by boat.</p>
<p>The boat stops at Viet Hai Pier, on the coastal side of Cat Ba Island. From there, a paved road runs approximately 5 km inland to the village.</p>
<p>Visitors usually continue by bicycle, electric cart or, if time and fitness allow, on foot.</p>
<p>Independent travellers can also arrange boat transport from the Cat Ba area. Another option is to hike through Cat Ba National Park, but that is a much longer trip requiring suitable preparation, route information and often a local guide.</p>
<p>For guests on an organised Lan Ha Bay cruise, transport from the pier and time in the village are normally planned as part of the day’s programme. See <a href="/guides/hanoi-to-halong-bay">how to get from Hanoi to Halong Bay</a> for the journey to the port.</p>
<p>Viet Hai is included in <a href="/cruise/green-ruby-2-4.html">Green Ruby 2</a>’s planned 3-day/2-night itinerary.</p>
<h2>How long should you spend in Viet Hai Village?</h2>
<p>Allow around 2–3 hours for a typical visit, including travelling between the pier and village, cycling or walking, and time to look around.</p>
<p>If your programme includes lunch, the visit may take closer to half a day.</p>
<p>The journey from the pier is part of the experience, so it is worth allowing enough time to enjoy the scenery rather than treating it simply as a transfer.</p>
<p>Visitors who want to hike, stay overnight or explore the national park will need considerably more time.</p>
<p>On a cruise, follow the published schedule and your guide’s instructions, as the day may include other stops around Lan Ha Bay.</p>
<h2>What to expect when you arrive</h2>
<p>Viet Hai is small and rural, but it is also an established destination for visitors to Cat Ba.</p>
<p>You will see farmland, houses, local businesses and services catering to tour groups. Depending on the time of day, some roads and village stops may be busy with bicycles and electric carts.</p>
<p>The rice fields will not always be green. At different times of year, you may see newly planted crops, mature rice, harvested fields or waterlogged plots.</p>
<p>Summer can be hot and humid, particularly when cycling. After rain, paths away from the paved road may be muddy.</p>
<p>Facilities are more limited than in Cat Ba’s main tourist areas, so bring anything essential with you from the cruise.</p>
<h2>Visiting Viet Hai respectfully</h2>
<p>Viet Hai is a working village where people live, grow food and run businesses.</p>
<p>A few things worth remembering:</p>
<ul>
<li>Ask permission before photographing residents at close range or entering private property.</li>
<li>Stay on public roads and permitted paths around farmland.</li>
<li>Do not pick fruit or vegetables without permission.</li>
<li>Keep noise to a reasonable level near people’s homes.</li>
<li>Avoid leaving rubbish in the village or surrounding forest.</li>
<li>Support local businesses if you choose to buy food, drinks or souvenirs.</li>
</ul>
<p>If you are interested in village life, ask your guide about the local crops, traditional livelihoods and the growth of tourism in the area.</p>
<h2>Is Viet Hai suitable for children and older visitors?</h2>
<p>Viet Hai can be a good stop for families because the activities can be adjusted to different fitness levels.</p>
<p>Children who can cycle confidently may enjoy the ride, provided that suitable bicycles or seats are available and the operator’s rules allow it.</p>
<p>For younger children, electric carts are often more practical.</p>
<p>Older visitors can also use an electric cart to avoid the longer ride or walk, then explore a shorter section of the village on foot.</p>
<p>However, reaching Viet Hai still involves boat transfers, and some village paths may be uneven. Electric carts should not be assumed to provide full wheelchair accessibility.</p>
<p>Discuss any mobility requirements with your cruise operator before travelling.</p>
<h2>Best time to visit Viet Hai Village</h2>
<p>Viet Hai can be visited throughout the year, subject to weather and boat operations.</p>
<p>From April to October, the weather is generally warmer and the valley can be lush and green. This is also the period when heat, humidity, heavy rain and occasional storms may affect outdoor activities.</p>
<p>From November to March, cooler conditions can make cycling and walking more comfortable, although cloudy or wet days are still possible.</p>
<p>The appearance of the fields varies with the planting and harvesting cycles, so do not expect the same green rice landscape at every time of year.</p>
<p>If cycling is your main interest, a dry day with moderate temperatures is usually preferable to a particularly hot or rainy one.</p>
<p>Visits can be changed or cancelled when strong winds or storm warnings prevent safe boat travel in Lan Ha Bay. See <a href="/blog/travel-tips/best-time-to-visit-halong-bay.html">the best time to visit Halong Bay</a> for month-by-month conditions.</p>
<h2>Visit Viet Hai Village with Green Ruby Cruises</h2>
<p>Viet Hai Village is included in the planned 3-day/2-night Lan Ha Bay programme aboard <a href="/cruise/green-ruby-2-4.html">Green Ruby 2</a>.</p>
<p><strong><a href="/itinerary/the-lan-ha-immersion-3-days-2-nights-4-1.html">The Lan Ha Immersion – 3 Days 2 Nights</a></strong><br>On the second day, guests transfer to a dedicated day boat for a visit to Viet Hai Village. The planned programme includes travelling into the village by bicycle, on foot or by electric cart, followed by a Vietnamese lunch.</p>
<p>The afternoon continues with a planned beach stop in Lan Ha Bay, subject to local conditions.</p>
<p>Viet Hai adds a different side of Cat Ba to the journey: a chance to leave the water for a few hours and see the farmland, homes and forested hills beyond the bay.</p>
<p><em>These are planned activities. Final arrangements will be confirmed when Green Ruby’s sailings begin. Stops and timings may be adjusted due to weather, local regulations or operational requirements.</em></p>
<h2>Nearby places and related experiences</h2>
<h3><a href="/destinations/dark-bright-cave">Dark &amp; Bright Cave</a></h3>
<p>A water-level limestone passage in Lan Ha Bay leading to a lagoon enclosed by steep cliffs. Visitors explore the area by locally rowed boat when conditions permit.</p>
<h3>Cat Ba National Park</h3>
<p>A protected area of limestone forests and walking trails, known for its biodiversity and natural landscape. Longer hiking routes require appropriate planning.</p>
<h3>Lan Ha Bay</h3>
<p>The coastal bay east of Cat Ba Island, with limestone islands, sheltered waterways and sandy beaches. It is the setting for cruises that combine time on the water with visits to Viet Hai.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Eastern Cat Ba Island, Hai Phong, Vietnam'],
                ['label' => 'Type', 'value' => 'Rural village and community tourism destination'],
                ['label' => 'Landscape', 'value' => 'Limestone valley, farmland and surrounding forest'],
                ['label' => 'Protected area', 'value' => 'Cat Ba National Park area'],
                ['label' => 'UNESCO designation', 'value' => 'Cat Ba Biosphere Reserve, recognised in 2004'],
                ['label' => 'Access', 'value' => 'Boat to Viet Hai Pier, then bicycle, electric cart or walking'],
                ['label' => 'Distance from pier', 'value' => 'Approximately 5 km'],
                ['label' => 'Main activities', 'value' => 'Cycling, village walks, electric cart rides and local meals'],
                ['label' => 'Typical visit', 'value' => '2–3 hours; longer with lunch'],
                ['label' => 'Best for', 'value' => 'Rural scenery, light cycling and learning about local life'],
                ['label' => 'Conditions', 'value' => 'Dependent on weather and safe boat access'],
            ],
            'faq' => [
                ['q' => 'Where is Viet Hai Village?', 'a' => 'Viet Hai Village is on the eastern side of Cat Ba Island, within the Cat Ba National Park area in Hai Phong, Vietnam. Most cruise visitors reach it by boat from Lan Ha Bay and then travel inland from Viet Hai Pier.'],
                ['q' => 'What is Viet Hai Village known for?', 'a' => 'Viet Hai is known for its rural valley setting, rice fields, forested limestone hills and village cycling routes. Visitors can learn about local farming, community tourism and everyday life on Cat Ba Island.'],
                ['q' => 'How do you get to Viet Hai Village?', 'a' => 'Most visitors arrive by boat at Viet Hai Pier, then travel about 5 km inland by bicycle, electric cart or on foot. Longer hiking routes through Cat Ba National Park are another option for suitably prepared visitors.'],
                ['q' => 'How far is Viet Hai Village from the pier?', 'a' => 'The road from Viet Hai Pier to the village is approximately 5 km. It passes through limestone hills and forested scenery before entering the agricultural valley.'],
                ['q' => 'Is the bike ride to Viet Hai difficult?', 'a' => 'The route is suitable for many visitors who can ride a bicycle confidently. There are some bends and gradients, and summer heat can make the journey more tiring. Electric carts are an alternative.'],
                ['q' => 'Can you walk from the pier to Viet Hai Village?', 'a' => 'Yes, but the walk is around 5 km each way. It may take too long for a standard cruise stop, especially if lunch or other activities are scheduled. A bicycle or electric cart is usually more practical.'],
                ['q' => 'Is Viet Hai Village suitable for children?', 'a' => 'Yes, depending on the activities and transport available. Families with young children may prefer an electric cart, while older children who are confident cyclists may be able to join a guided bike ride.'],
                ['q' => 'Can you eat lunch in Viet Hai Village?', 'a' => 'Yes. Some local restaurants and cruise programmes offer meals in the village. Menus and dietary options vary, so check with your cruise operator in advance.'],
                ['q' => 'Can you stay overnight in Viet Hai?', 'a' => 'Yes. The village has homestays and small accommodation providers. Overnight stays are a separate experience from the shorter visits usually included in Lan Ha Bay cruises.'],
                ['q' => 'Which Green Ruby cruise visits Viet Hai Village?', 'a' => 'Viet Hai Village is included in Green Ruby 2’s planned 3-day/2-night Lan Ha Bay itinerary, The Lan Ha Immersion. It is not listed as a stop on the standard 2-day/1-night Lan Ha Escape programme.'],
                ['q' => 'When does Green Ruby plan to visit Viet Hai Village?', 'a' => 'The published 3-day/2-night itinerary schedules Viet Hai Village for approximately 10:00 AM on the second day, followed by lunch in the village at around 12:00 PM. Activities and timings remain provisional until Green Ruby’s sailings begin and may change due to weather, local regulations or operational conditions.'],
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
