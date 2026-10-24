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
        'editor_notes' => "Not published: exact number of steps, cave area/size figures, entrance fee and opening hours (no official source found). The 1901 French naming story is on vi.wikipedia only; left out until confirmed by the Ha Long Bay Management Board.",
        'en' => [
            'slug' => 'sung-sot-cave',
            'name' => 'Sung Sot Cave',
            'image_alt' => 'Lit stalactites and the high ceiling of the main chamber in Sung Sot Cave, Ha Long Bay',
            'seo_title' => 'Sung Sot Cave (Surprise Cave), Ha Long Bay: Visitor Guide',
            'seo_description' => 'What to expect at Sung Sot Cave in Ha Long Bay: the climb to the entrance, the two chambers, how long a visit takes, accessibility and when Green Ruby plans to stop.',
            'summary' => "Sung Sot, the 'Surprise Cave', is a large limestone cave on Bo Hon Island in central Ha Long Bay. A modest entrance opens into two high chambers, reached by a stone stairway from the boat pier.",
            'content' => <<<'HTML'
<h2>Where it is</h2>
<p>Sung Sot is set high in the limestone of Bo Hon Island, in the central part of Ha Long Bay. Luon Cave is on the same island and Ti Top Island lies close by, so the three are usually visited on the same route.</p>
<h2>Why it is worth the climb</h2>
<p>The Vietnamese name means "surprise", and it describes the first impression well: a small opening in the rock leads into a much larger space than you expect. The cave has two main chambers. The first is lower and shaped like a hall; the second is far larger, with stalactites and stalagmites along the walls and ceiling. The formations are lit with coloured lights, and guides point out shapes that local stories connect with legends of the bay.</p>
<h2>How visitors reach it</h2>
<p>Cruise and day boats moor at a pier at the foot of the island. From there a stone stairway climbs through trees to the entrance. Inside, a paved path with railings leads through both chambers, and the route ends near a lookout over the moorings before steps take you back down.</p>
<h2>What to expect on arrival</h2>
<p>Sung Sot is one of the most visited caves in Ha Long Bay, so you will share the path with other groups, especially in the middle of the morning and at weekends. The air inside is warm and humid, the light is low, and some surfaces can be damp.</p>
<h2>Accessibility and practical tips</h2>
<ul>
<li>The visit involves a long flight of steps outside and more steps inside. It is not suitable for wheelchairs or for anyone who finds stairs difficult.</li>
<li>Wear shoes with good grip and bring water. A light layer is rarely needed: the cave is usually warm.</li>
<li>Young children will need to be carried on parts of the stairway.</li>
<li>If you would rather not climb, tell the crew in advance; staying on board is always an option.</li>
</ul>
<h2>Weather and seasonal limits</h2>
<p>Rain does not affect the inside of the cave, but every visit depends on boats being allowed to sail. During storm warnings, mostly between June and October, the authorities can suspend sailings in the bay and planned visits may be cancelled or rescheduled.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Bo Hon Island, central Ha Long Bay'],
                ['label' => 'Type', 'value' => 'Limestone cave with two main chambers'],
                ['label' => 'Access', 'value' => 'Boat to the island pier, then a stone stairway'],
                ['label' => 'Typical visit', 'value' => 'About 1 hour including the climb (estimate)'],
                ['label' => 'Accessibility', 'value' => 'Many steps; not wheelchair accessible'],
            ],
            'faq' => [
                ['q' => 'How long does a visit to Sung Sot Cave take?', 'a' => 'Allow about an hour from the pier and back, including the climb and the walk through both chambers. Busy mornings can make it a little longer.'],
                ['q' => 'Is Sung Sot Cave suitable for older travellers or young children?', 'a' => 'It depends on how comfortable you are with stairs. There is a long stone stairway to the entrance and further steps inside. Children can visit, but toddlers will need to be carried on parts of the route.'],
                ['q' => 'Is Sung Sot Cave crowded?', 'a' => 'It is one of the busiest sights in Ha Long Bay. Early visits are usually calmer, which is why Green Ruby plans its stop for the morning.'],
                ['q' => 'Do all Green Ruby cruises visit Sung Sot Cave?', 'a' => 'Sung Sot is a planned stop on both Ha Long Bay itineraries of Green Ruby 1, on the final morning. It is not part of the Lan Ha Bay itineraries of Green Ruby 2. Itineraries are provisional until sailings begin, expected in February 2027.'],
            ],
        ],
        'vi' => [
            'slug' => 'hang-sung-sot',
            'name' => 'Hang Sửng Sốt',
            'image_alt' => 'Thạch nhũ được chiếu sáng dưới trần cao của khoang chính hang Sửng Sốt, vịnh Hạ Long',
            'seo_title' => 'Hang Sửng Sốt, vịnh Hạ Long: kinh nghiệm tham quan',
            'seo_description' => 'Hang Sửng Sốt có gì, đường lên hang ra sao, tham quan mất bao lâu, ai nên cân nhắc khi leo bậc và lịch ghé dự kiến của du thuyền Green Ruby.',
            'summary' => 'Hang Sửng Sốt nằm trên đảo Bồ Hòn, giữa vịnh Hạ Long. Cửa hang nhỏ nhưng bên trong mở ra hai khoang rộng, du khách lên hang theo lối bậc đá từ bến thuyền.',
            'content' => <<<'HTML'
<h2>Vị trí</h2>
<p>Hang Sửng Sốt nằm lưng chừng núi đá vôi trên đảo Bồ Hòn, khu vực trung tâm vịnh Hạ Long. Cùng đảo có hang Luồn, gần đó là đảo Ti Tốp, nên ba điểm này thường nằm chung một tuyến tham quan.</p>
<h2>Vì sao nên lên hang</h2>
<p>Tên hang nói đúng cảm giác của người mới đến: cửa vào khá hẹp, nhưng bước qua là một không gian rộng hơn nhiều so với tưởng tượng. Hang gồm hai khoang chính. Khoang đầu thấp, giống một sảnh lớn; khoang sau rộng và cao hơn hẳn, thạch nhũ và măng đá phủ kín vách và trần. Hệ thống đèn màu làm nổi các khối đá, hướng dẫn viên thường kể những tích truyện dân gian gắn với từng hình thù.</p>
<h2>Đường lên hang</h2>
<p>Tàu neo ở bến dưới chân đảo. Từ bến, lối bậc đá dẫn lên cửa hang qua những tán cây. Bên trong có lối đi lát đá và lan can chạy qua cả hai khoang; cuối hang là điểm nhìn xuống khu neo đậu của tàu trước khi xuống bậc trở lại bến.</p>
<h2>Khi đến nơi</h2>
<p>Đây là một trong những hang được ghé thăm nhiều nhất vịnh Hạ Long, nên bạn sẽ đi chung lối với các đoàn khác, nhất là giữa buổi sáng và cuối tuần. Trong hang khá ấm và ẩm, ánh sáng dịu, mặt đá có chỗ trơn.</p>
<h2>Lưu ý khi tham quan</h2>
<ul>
<li>Phải leo khá nhiều bậc ở bên ngoài và đi tiếp một số bậc trong hang. Hang không phù hợp với xe lăn hay người khó leo cầu thang.</li>
<li>Nên đi giày đế bám và mang theo nước. Trong hang thường ấm nên không cần áo khoác.</li>
<li>Trẻ nhỏ cần được bế ở một số đoạn bậc.</li>
<li>Nếu không muốn leo, bạn có thể báo trước với đội tàu để ở lại trên du thuyền.</li>
</ul>
<h2>Thời tiết và mùa</h2>
<p>Mưa không ảnh hưởng đến bên trong hang, nhưng chuyến tham quan phụ thuộc vào việc tàu có được phép ra vịnh hay không. Khi có cảnh báo bão, thường từ tháng 6 đến tháng 10, cơ quan chức năng có thể tạm dừng tàu du lịch và lịch ghé hang sẽ bị hủy hoặc đổi.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Đảo Bồ Hòn, trung tâm vịnh Hạ Long'],
                ['label' => 'Loại hình', 'value' => 'Hang đá vôi, hai khoang chính'],
                ['label' => 'Cách đến', 'value' => 'Tàu cập bến chân đảo, đi bộ lên lối bậc đá'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 1 giờ kể cả leo bậc (ước tính)'],
                ['label' => 'Khả năng tiếp cận', 'value' => 'Nhiều bậc, không phù hợp xe lăn'],
            ],
            'faq' => [
                ['q' => 'Tham quan hang Sửng Sốt mất bao lâu?', 'a' => 'Bạn nên dành khoảng một giờ tính từ lúc lên bến đến khi quay lại, gồm cả leo bậc và đi hết hai khoang. Những buổi sáng đông khách có thể lâu hơn một chút.'],
                ['q' => 'Người lớn tuổi và trẻ nhỏ có đi hang Sửng Sốt được không?', 'a' => 'Tùy vào khả năng leo bậc. Đường lên cửa hang là một lối bậc đá dài, trong hang còn thêm một số bậc. Trẻ em vẫn đi được, nhưng bé nhỏ cần người lớn bế ở vài đoạn.'],
                ['q' => 'Hang Sửng Sốt có đông không?', 'a' => 'Đây là một trong những điểm đông khách nhất vịnh Hạ Long. Đi sớm thường vắng hơn, vì vậy Green Ruby dự kiến ghé hang vào buổi sáng.'],
                ['q' => 'Du thuyền Green Ruby nào ghé hang Sửng Sốt?', 'a' => 'Hang Sửng Sốt nằm trong cả hai hành trình vịnh Hạ Long của Green Ruby 1, vào sáng ngày cuối. Các hành trình vịnh Lan Hạ của Green Ruby 2 không ghé hang này. Lịch trình có thể điều chỉnh trước khi tàu chính thức hoạt động, dự kiến từ tháng 2/2027.'],
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
        'editor_notes' => "English Wikipedia lists 20.862601, 107.067218, about 1.5 km west of the summit photo GPS used here; the photo position matches 'opposite Sung Sot' and the bay geography. Not published: number of steps (sources vary), beach opening hours, lifeguard arrangements. Hero photo is the owner's upload (media library 2026-10-21).",
        'en' => [
            'slug' => 'titop-island',
            'name' => 'Ti Top Island',
            'image_alt' => 'Ha Long Bay seen from the summit of Ti Top Island',
            'seo_title' => 'Ti Top Island (Titop), Ha Long Bay: Beach & Viewpoint Guide',
            'seo_description' => 'Ti Top Island in Ha Long Bay: why it carries a cosmonaut\'s name, the climb to the summit viewpoint, swimming at the beach, timing and practical tips.',
            'summary' => 'A small island in central Ha Long Bay with a crescent of sand on one side and a viewpoint at the top, reached by a steep stairway. It is named after Soviet cosmonaut Gherman Titov.',
            'content' => <<<'HTML'
<h2>Where it is</h2>
<p>Ti Top lies about 7–8 km south-east of Bai Chay, in the central part of Ha Long Bay and opposite Bo Hon Island, home of Sung Sot and Luon caves. Most boats sailing this part of the bay stop here.</p>
<h2>Why it is worth visiting</h2>
<p>The island combines the two things most visitors want from a stop: a swim and a view. The beach is a narrow crescent of pale sand at the foot of the island. From the summit, the view takes in the karst islands around Bo Hon and the boats anchored between them.</p>
<p>The name has an unusual origin. On 22 January 1962, President Ho Chi Minh visited the island with Gherman Titov, the second person to orbit the Earth, and named it after him. A monument to Titov stands on the island.</p>
<h2>Things to do</h2>
<ul>
<li>Climb to the summit viewpoint for the panorama, ideally in the softer light of late afternoon.</li>
<li>Swim from the beach in the marked area when conditions allow.</li>
<li>Stay on the sand if you prefer: the beach and the pier area are at sea level.</li>
</ul>
<h2>How visitors reach it</h2>
<p>Tenders and day boats land at the island's pier, next to the beach. The path to the top is a steep stairway with railings and a few resting points on the way.</p>
<h2>What to expect on arrival</h2>
<p>Ti Top is popular, and the beach and stairway can be busy in the afternoon, when many cruises arrive. The climb is short but steep and can be hot in summer.</p>
<h2>Accessibility and practical tips</h2>
<ul>
<li>The summit is reached only by stairs. The beach is accessible from the pier without climbing.</li>
<li>Bring swimwear, a towel, water and sun protection.</li>
<li>Swim only where and when the crew says it is safe.</li>
</ul>
<h2>Weather and seasonal limits</h2>
<p>Swimming is most comfortable from about May to October, when the water is warm, but this is also the season when storms can stop sailings. In winter the climb is pleasant, though fewer people swim.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Central Ha Long Bay, about 7–8 km south-east of Bai Chay'],
                ['label' => 'Highlights', 'value' => 'Summit viewpoint, small sand beach'],
                ['label' => 'Access', 'value' => 'Boat to the island pier; steep stairway to the summit'],
                ['label' => 'Typical visit', 'value' => 'About 1 hour (estimate)'],
                ['label' => 'Name', 'value' => 'After cosmonaut Gherman Titov, who visited in January 1962'],
            ],
            'faq' => [
                ['q' => 'Why is it called Ti Top Island?', 'a' => 'President Ho Chi Minh named the island after Soviet cosmonaut Gherman Titov, who visited with him on 22 January 1962. "Ti Top" is the Vietnamese rendering of Titov.'],
                ['q' => 'Can you swim at Ti Top beach?', 'a' => 'Yes, in the designated area when sea conditions allow. Water is warmest from late spring to autumn. The crew decides on the day whether swimming is safe.'],
                ['q' => 'How hard is the climb to the top?', 'a' => 'It is a short but steep stairway with railings. Most reasonably fit visitors manage it at a steady pace; in summer, go slowly and carry water.'],
                ['q' => 'When does Green Ruby plan to stop at Ti Top?', 'a' => 'On both Ha Long Bay itineraries of Green Ruby 1, in the late afternoon of the first day, after Luon Cave. Times are provisional until the ship enters service, expected in February 2027.'],
            ],
        ],
        'vi' => [
            'slug' => 'dao-ti-top',
            'name' => 'Đảo Ti Tốp',
            'image_alt' => 'Vịnh Hạ Long nhìn từ đỉnh đảo Ti Tốp',
            'seo_title' => 'Đảo Ti Tốp, vịnh Hạ Long: bãi tắm, đỉnh núi và kinh nghiệm',
            'seo_description' => 'Đảo Ti Tốp có gì: nguồn gốc tên đảo, đường lên đỉnh ngắm toàn cảnh vịnh Hạ Long, tắm biển, thời điểm nên đến và những lưu ý thực tế.',
            'summary' => 'Hòn đảo nhỏ giữa vịnh Hạ Long, một bên là bãi cát hình lưỡi liềm, trên đỉnh là điểm ngắm cảnh, lên bằng lối bậc dốc. Đảo mang tên nhà du hành vũ trụ Liên Xô Gherman Titov.',
            'content' => <<<'HTML'
<h2>Vị trí</h2>
<p>Đảo Ti Tốp cách Bãi Cháy khoảng 7–8 km về phía đông nam, nằm ở khu trung tâm vịnh Hạ Long, đối diện đảo Bồ Hòn, nơi có hang Sửng Sốt và hang Luồn. Phần lớn tàu đi tuyến này đều ghé đảo.</p>
<h2>Vì sao nên ghé</h2>
<p>Ti Tốp có đủ hai thứ mà du khách mong đợi ở một điểm dừng: tắm biển và ngắm cảnh. Bãi cát nhỏ, cong như vầng trăng khuyết dưới chân đảo. Từ đỉnh đảo nhìn ra là những đảo đá quanh Bồ Hòn và các con tàu neo giữa vịnh.</p>
<p>Tên đảo có một câu chuyện riêng. Ngày 22/1/1962, Chủ tịch Hồ Chí Minh cùng nhà du hành vũ trụ Gherman Titov, người thứ hai bay vòng quanh Trái Đất, lên thăm đảo, và Bác đã đặt tên đảo theo tên ông. Trên đảo có tượng đài Titov.</p>
<h2>Có thể làm gì</h2>
<ul>
<li>Leo lên đỉnh ngắm toàn cảnh, đẹp nhất vào cuối chiều khi nắng dịu.</li>
<li>Tắm biển trong khu vực quy định khi điều kiện cho phép.</li>
<li>Hoặc chỉ nghỉ ở bãi cát: bãi tắm và khu cầu tàu nằm sát mặt nước.</li>
</ul>
<h2>Cách đến</h2>
<p>Xuồng và tàu ngày cập cầu tàu ngay cạnh bãi tắm. Lối lên đỉnh là các bậc dốc có tay vịn, dọc đường có chỗ dừng nghỉ.</p>
<h2>Khi đến nơi</h2>
<p>Ti Tốp khá đông, nhất là buổi chiều khi nhiều tàu cùng ghé. Đường lên đỉnh không dài nhưng dốc, mùa hè khá nóng.</p>
<h2>Lưu ý khi tham quan</h2>
<ul>
<li>Muốn lên đỉnh chỉ có cách leo bậc. Từ cầu tàu ra bãi tắm thì không phải leo.</li>
<li>Mang đồ bơi, khăn, nước uống và đồ chống nắng.</li>
<li>Chỉ xuống nước khi đội tàu xác nhận an toàn.</li>
</ul>
<h2>Thời tiết và mùa</h2>
<p>Từ khoảng tháng 5 đến tháng 10 nước ấm, tắm biển dễ chịu nhất, nhưng đây cũng là mùa có thể có bão khiến tàu tạm dừng. Mùa đông leo núi mát mẻ hơn, nhưng ít người xuống tắm.</p>
HTML,
            'facts' => [
                ['label' => 'Vị trí', 'value' => 'Trung tâm vịnh Hạ Long, cách Bãi Cháy khoảng 7–8 km về phía đông nam'],
                ['label' => 'Điểm nổi bật', 'value' => 'Điểm ngắm cảnh trên đỉnh, bãi cát nhỏ'],
                ['label' => 'Cách đến', 'value' => 'Tàu cập cầu tàu; lên đỉnh bằng lối bậc dốc'],
                ['label' => 'Thời gian tham quan', 'value' => 'Khoảng 1 giờ (ước tính)'],
                ['label' => 'Tên gọi', 'value' => 'Theo tên nhà du hành vũ trụ Gherman Titov, người thăm đảo tháng 1/1962'],
            ],
            'faq' => [
                ['q' => 'Vì sao đảo có tên Ti Tốp?', 'a' => 'Chủ tịch Hồ Chí Minh đặt tên đảo theo tên nhà du hành vũ trụ Liên Xô Gherman Titov, người cùng Bác lên thăm đảo ngày 22/1/1962. "Ti Tốp" là cách đọc phiên âm tên Titov.'],
                ['q' => 'Có tắm biển ở đảo Ti Tốp được không?', 'a' => 'Được, trong khu vực quy định và khi biển êm. Nước ấm nhất từ cuối xuân đến mùa thu. Đội tàu sẽ quyết định trong ngày có cho xuống nước hay không.'],
                ['q' => 'Leo lên đỉnh Ti Tốp có mệt không?', 'a' => 'Đường lên không dài nhưng dốc, có tay vịn. Người có sức khỏe bình thường đều đi được nếu đi đều chân; mùa hè nên đi chậm và mang theo nước.'],
                ['q' => 'Green Ruby dự kiến ghé đảo Ti Tốp khi nào?', 'a' => 'Trong cả hai hành trình vịnh Hạ Long của Green Ruby 1, vào cuối chiều ngày đầu, sau khi thăm hang Luồn. Giờ giấc có thể thay đổi trước khi tàu chính thức hoạt động, dự kiến từ tháng 2/2027.'],
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
        'editor_notes' => "Monkeys: often reported by visitors, kept as 'sometimes seen'. Not published: tunnel length/height, tide thresholds, boat fees.",
        'en' => [
            'slug' => 'luon-cave',
            'name' => 'Luon Cave',
            'image_alt' => 'Rowing boats passing under the rock arch of Luon Cave into the enclosed lagoon, Ha Long Bay',
            'seo_title' => 'Luon Cave, Ha Long Bay: Kayak or Bamboo Boat to the Lagoon',
            'seo_description' => 'Luon Cave in Ha Long Bay is a low sea tunnel into an enclosed lagoon. How the visit works by kayak or bamboo boat, tides, children and what to bring.',
            'summary' => 'A low, water-level tunnel through Bo Hon Island that opens into a lagoon enclosed by cliffs. You pass through by kayak or in a bamboo boat rowed by local boatmen.',
            'content' => <<<'HTML'
<h2>Where it is</h2>
<p>Luon Cave cuts through Bo Hon Island in central Ha Long Bay, the same island as Sung Sot Cave. "Luon" means to pass through, and that is exactly what you do: the cave is a short tunnel at sea level rather than a chamber to walk in.</p>
<h2>Why it is worth visiting</h2>
<p>On the other side of the arch is a lagoon closed in on all sides by steep, wooded cliffs. The water is calm, the noise of the main bay drops away, and it is one of the easiest places in Ha Long Bay to get close to the karst at water level. Macaques are sometimes seen on the cliffs.</p>
<h2>Activities</h2>
<ul>
<li><strong>Kayaking</strong>: paddle yourself through the tunnel and around the lagoon. No previous experience is needed for this sheltered water.</li>
<li><strong>Bamboo boat</strong>: sit back while a local boatman rows. This suits children, older travellers and anyone who prefers not to paddle.</li>
</ul>
<h2>How visitors reach it</h2>
<p>Boats moor at a floating pier near the cave mouth, where kayaks and bamboo boats wait. Life jackets are handed out before you set off.</p>
<h2>What to expect on arrival</h2>
<p>The arch is low, so you duck as you pass under it. Whether boats can go through, and how much headroom there is, depends on the tide. In the afternoon there can be a short wait as groups take turns.</p>
<h2>Accessibility and practical tips</h2>
<ul>
<li>Stepping from the pier into a kayak or bamboo boat needs some balance; the crew helps with boarding.</li>
<li>Keep phones in a dry bag and wear clothes that can get splashed.</li>
<li>Do not feed or approach the monkeys.</li>
</ul>
<h2>Weather and seasonal limits</h2>
<p>Strong wind, heavy rain or a very high tide can make the passage unsafe, and the visit may then be shortened or replaced. In storm season, sailings in the bay can be suspended altogether.</p>
HTML,
            'facts' => [
                ['label' => 'Location', 'value' => 'Bo Hon Island, central Ha Long Bay'],
                ['label' => 'Type', 'value' => 'Sea-level tunnel leading to an enclosed lagoon'],
                ['label' => 'Access', 'value' => 'Kayak or bamboo rowing boat from a floating pier'],
                ['label' => 'Typical visit', 'value' => 'About 1 hour on the water (estimate)'],
                ['label' => 'Depends on', 'value' => 'Tide and weather on the day'],
            ],
            'faq' => [
                ['q' => 'Should I choose a kayak or a bamboo boat at Luon Cave?', 'a' => 'Kayak if you want to explore at your own pace and are comfortable getting a little wet. Choose the bamboo boat if you would rather be rowed, are travelling with young children, or want to take photos.'],
                ['q' => 'Do I need kayaking experience?', 'a' => 'No. The lagoon is sheltered and the route is short. The crew gives a briefing, and life jackets are compulsory.'],
                ['q' => 'Can children go through Luon Cave?', 'a' => 'Yes, usually in a bamboo boat with an adult. Children wear life jackets throughout.'],
                ['q' => 'When does Green Ruby plan to visit Luon Cave?', 'a' => 'On the first afternoon of both Ha Long Bay itineraries of Green Ruby 1, before Ti Top Island. The schedule is provisional until sailings begin, expected in February 2027.'],
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
