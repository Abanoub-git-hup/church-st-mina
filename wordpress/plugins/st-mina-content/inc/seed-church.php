<?php
/**
 * إدخال محتوى الكنيسة مرة واحدة من التصميم المعتمد: الآباء، والمحطات، والألبومات، والصفحات الأربع.
 * الـ plugin كان متفعّل قبل الجزء ده، فالإدخال مش في التفعيل: بيشتغل مرة واحدة مع أول طلب للموقع.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_loaded', function () {
	if ( get_option( 'stmina_church_seeded' ) ) {
		return;
	}
	// قفل: add_option بترجع false لو الإعداد موجود، فلو طلبين جم مع بعض واحد بس يدخل
	if ( ! add_option( 'stmina_church_seeding', time(), '', false ) ) {
		return;
	}
	stmina_seed_church();
	update_option( 'stmina_church_seeded', STMINA_CONTENT_VERSION );
	delete_option( 'stmina_church_seeding' );
	flush_rewrite_rules(); // علشان روابط الصفحات الجديدة
} );

function stmina_seed_church() {
	// ---------- الآباء الكهنة: الاسم، والنبذة، وصورة الوش وقصّها، والملصقات ----------
	$priests = array(
		array( 'القمص كيرلس روماني', 'رُسم قسًا على الكنيسة في 16 نوفمبر 1998. باقي النبذة يكتبها محرر المحتوى.', 'priests/quote-kyrillos-3.jpg', array( 265, -32, -233 ), array(
			array( 'priests/quote-kyrillos-1.jpg', 'قول للقمص كيرلس روماني عن القيامة' ),
			array( 'priests/quote-kyrillos-2.jpg', 'قول للقمص كيرلس روماني' ),
			array( 'priests/quote-kyrillos-3.jpg', 'صلاة للقمص كيرلس روماني' ),
		) ),
		array( 'القس أرسانيوس عزت', 'نبذة قصيرة عن أبينا: سنة الرسامة، والخدمات التي يرعاها. يكتبها محرر المحتوى.', 'priests/quote-arsanios-1.jpg', array( 252, -49, -84 ), array(
			array( 'priests/quote-arsanios-1.jpg', 'خادم مش مصلي.. غصنه ناشف' ),
			array( 'priests/quote-arsanios-2.jpg', 'قول للقس أرسانيوس عزت' ),
		) ),
		array( 'القس فام عبد المسيح', 'نبذة قصيرة عن أبينا: سنة الرسامة، والخدمات التي يرعاها. يكتبها محرر المحتوى.', 'priests/quote-fam.jpg', array( 301, -5, -115 ), array(
			array( 'priests/quote-fam.jpg', 'قول للقس فام عبد المسيح' ),
		) ),
	);
	foreach ( $priests as $i => $p ) {
		$id = wp_insert_post( array( 'post_type' => 'stmina_priest', 'post_status' => 'publish', 'post_title' => $p[0], 'post_excerpt' => $p[1], 'menu_order' => $i + 1 ) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		stmina_seed_meta( 'post', $id, 'rank', 'كاهن الكنيسة', 'field_stmina_priest_rank', $id );
		stmina_seed_meta( 'post', $id, 'face_image', stmina_seed_image( $p[2] ), 'field_stmina_priest_face', $id );
		stmina_seed_meta( 'post', $id, 'face_w', $p[3][0], 'field_stmina_priest_fw', $id );
		stmina_seed_meta( 'post', $id, 'face_x', $p[3][1], 'field_stmina_priest_fx', $id );
		stmina_seed_meta( 'post', $id, 'face_y', $p[3][2], 'field_stmina_priest_fy', $id );
		foreach ( $p[4] as $n => $poster ) {
			$img = stmina_seed_image( $poster[0], $id, $poster[1] );
			wp_update_post( array( 'ID' => $img, 'menu_order' => $n + 1 ) );
		}
	}

	// ---------- محطات التاريخ (اللي سنتها 0000 لسه مستنية بيانات الكنيسة) ----------
	$todo      = 'تُكتب السنة والتفاصيل بعد التأكد منها.';
	$milestones = array(
		array( '0000', 'بداية الصلاة في المكان', $todo ),
		array( '0000', 'بناء الكنيسة', $todo ),
		array( '0000', 'تدشين المذابح', $todo ),
		array( '1998', 'رسامة القس كيرلس روماني', 'رُسم قسًا على الكنيسة في 16 نوفمبر 1998.' ),
		array( '2007', 'قداس افتتاح الكنيسة', '29 ديسمبر 2007، بصلوات أبونا كيرلس روماني وأبونا مقار ماهر وأبونا بطرس حليم.' ),
		array( '0000', 'الكنيسة اليوم', 'تُكتب التفاصيل بعد التأكد منها.' ),
	);
	foreach ( $milestones as $i => $m ) {
		$id = wp_insert_post( array( 'post_type' => 'stmina_milestone', 'post_status' => 'publish', 'post_title' => $m[1], 'post_excerpt' => $m[2], 'menu_order' => $i + 1 ) );
		if ( $id && ! is_wp_error( $id ) ) {
			stmina_seed_meta( 'post', $id, 'year', $m[0], 'field_stmina_milestone_year', $id );
		}
	}

	// ---------- تصنيفات الصور والألبومات ----------
	$cats = array( 'worship' => 'العبادة', 'sunday' => 'مدارس الأحد', 'youth' => 'الشباب', 'teams' => 'الفرق والأنشطة', 'talks' => 'المحاضرات' );
	$cat_ids = array();
	foreach ( $cats as $slug => $name ) {
		$t = term_exists( $slug, 'stmina_album_cat' ) ?: wp_insert_term( $name, 'stmina_album_cat', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $t ) ) {
			$cat_ids[ $slug ] = (int) $t['term_id'];
		}
	}
	// الألبوم، والتصنيف، والصور (الملف والتعليق)
	$albums = array(
		array( 'قداسات وصلوات', 'worship', array(
			array( 'worship/youth-liturgy.jpeg', 'قداس الشباب' ),
			array( 'worship/deacons.jpeg', 'الآباء مع الشمامسة أمام الهيكل' ),
			array( 'worship/youth-night-liturgy-1.jpeg', 'قداس ليلي للشباب' ),
			array( 'worship/youth-night-liturgy-2.jpeg', 'الشمامسة في الهيكل' ),
			array( 'worship/youth-night-liturgy-3.jpeg', 'سجود أمام الهيكل' ),
			array( 'church/nave-real.jpeg', 'صحن الكنيسة' ),
		) ),
		array( 'اجتماع الشباب 2026', 'youth', array( array( 'services/youth/youth-meeting-2026.jpeg', 'اجتماع الشباب 2026' ) ) ),
		array( 'مؤتمر عنبر 11 للشباب 2026', 'youth', array( array( 'services/youth/youth-conference-anbar11-2026.jpeg', 'مؤتمر "عنبر 11" للشباب 2026' ) ) ),
		array( 'رحلة أديرة المنيا 2026', 'youth', array(
			array( 'services/youth/minya-monasteries-trip-2026-1.jpeg', 'رحلة أديرة المنيا 2026' ),
			array( 'services/youth/minya-monasteries-trip-2026-2.jpeg', 'رحلة أديرة المنيا 2026' ),
		) ),
		array( 'أمسية العبور 2025', 'youth', array(
			array( 'services/youth/obour-evening-2025-1.jpeg', 'أمسية العبور 2025' ),
			array( 'services/youth/obour-evening-2025-2.jpeg', 'أمسية العبور 2025' ),
		) ),
		array( 'مؤتمر ابتدائي 2026', 'sunday', array(
			array( 'services/sunday-school/primary-conference-2026-1.jpg', 'مؤتمر ابتدائي 2026' ),
			array( 'services/sunday-school/primary-conference-2026-2.jpg', 'مؤتمر ابتدائي 2026' ),
			array( 'services/sunday-school/primary-conference-2026-3.jpg', 'مؤتمر ابتدائي 2026' ),
		) ),
		array( 'مسابقة كرة القدم لابتدائي', 'sunday', array(
			array( 'services/sunday-school/primary-football-1.jpg', 'مسابقة كرة القدم لابتدائي' ),
			array( 'services/sunday-school/primary-football-2.jpg', 'تسليم الميداليات' ),
		) ),
		array( 'مرحلة ثانوي', 'sunday', array(
			array( 'services/sunday-school/secondary-meeting.jpg', 'نشاط لمرحلة ثانوي' ),
			array( 'services/sunday-school/secondary-conference-2015.jpeg', 'مؤتمر ثانوي 2015' ),
		) ),
		array( 'فريق المسرح', 'teams', array(
			array( 'services/theater/theater-ismuh-yasou.jpg', 'عرض "اسمه يسوع"' ),
			array( 'services/theater/theater-stage.jpg', 'فريق المسرح على المسرح' ),
			array( 'services/theater/theater-team.jpg', 'فريق مسرح الأنبا موسى الأسود' ),
		) ),
		array( 'كورال ترينتي', 'teams', array( array( 'services/choir/trinity-choir.jpg', 'كورال ترينتي' ) ) ),
		array( 'فريق الكشافة', 'teams', array( array( 'services/scouts/scouts.jpg', 'فريق الكشافة' ) ) ),
		array( 'كورس المشورة 2026', 'talks', array(
			array( 'services/counseling/counseling-course-2026-1.jpeg', 'كورس المشورة 2026' ),
			array( 'services/counseling/counseling-course-2026-2.jpeg', 'محاضرة في الكنيسة الصغيرة' ),
		) ),
	);
	foreach ( $albums as $i => $a ) {
		$id = wp_insert_post( array( 'post_type' => 'stmina_album', 'post_status' => 'publish', 'post_title' => $a[0], 'menu_order' => $i + 1 ) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		if ( isset( $cat_ids[ $a[1] ] ) ) {
			wp_set_object_terms( $id, array( $cat_ids[ $a[1] ] ), 'stmina_album_cat' );
		}
		foreach ( $a[2] as $n => $photo ) {
			$img = stmina_seed_image( $photo[0], $id, $photo[1] );
			if ( $img ) {
				// الصورة ممكن تكون مستخدمة في خدمة، فبنربطها بالألبوم صراحة
				wp_update_post( array( 'ID' => $img, 'post_parent' => $id, 'menu_order' => $n + 1 ) );
			}
		}
	}

	// ---------- الصفحات الأربع ----------
	$p = function ( $text ) {
		return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$fig = stmina_seed_image( 'worship/youth-night-liturgy-1.jpeg' );
	$history = $p( 'هنا يكتب محرر المحتوى قصة نشأة الكنيسة: متى بدأت الصلاة في المكان، ومن الآباء الذين خدموا فيه، وكيف بُنيت الكنيسة الكبيرة والكنيسة الصغيرة.' )
		. $p( 'فقرة ثانية عن مراحل البناء والتجديد، وتدشين المذابح، ومنها مذبح البابا كيرلس السادس بالكنيسة الكبيرة.' )
		. '<!-- wp:image {"id":' . (int) $fig . ',"sizeSlug":"large"} -->' . "\n"
		. '<figure class="wp-block-image size-large"><img src="' . esc_url( wp_get_attachment_image_url( $fig, 'large' ) ) . '" alt="أب كاهن عند المذبح والصحن في الخلفية" class="wp-image-' . (int) $fig . '"/><figcaption class="wp-element-caption">من داخل الكنيسة</figcaption></figure>' . "\n"
		. "<!-- /wp:image -->\n\n"
		. $p( 'فقرة عن الكنيسة اليوم: الخدمات، والاجتماعات، والشعب الذي يجتمع فيها.' )
		. "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>«مَا أَرْهَبَ هذَا ٱلْمَكَانَ! مَا هذَا إِلاَّ بَيْتُ ٱللهِ، وَهذَا بَابُ ٱلسَّمَاءِ»</p>\n<!-- /wp:paragraph --><cite>(تكوين 28: 17)</cite></blockquote>\n<!-- /wp:quote -->";

	$pages = array(
		// الرابط، والاسم، والكلمة الصغيرة، وعنوان الواجهة، والجزء الدهبي، والوصف، وصورة الواجهة، والمحتوى
		array( 'church-history', 'النشأة', 'عن كنيستنا', 'بيت الله وباب السماء', 'وباب السماء', 'كنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس، عرب العيايدة، ناحية الجبل الأصفر، مطرانية شبين القناطر وتوابعها.', 'church/nave-real.jpeg', $history ),
		array( 'church-fathers', 'الآباء', 'الآباء الكهنة', 'آباء الكنيسة', 'الكنيسة', 'القمص كيرلس روماني، والقس أرسانيوس عزت، والقس فام عبد المسيح.', 'worship/deacons.jpeg', '' ),
		array( 'church-gallery', 'الصور', 'من حياة الكنيسة', 'صور الكنيسة', 'الكنيسة', 'صور من العبادة والخدمات والأنشطة. اضغط على أي صورة لتكبيرها.', 'worship/youth-liturgy.jpeg', '' ),
		array( 'church-location', 'الموقع', 'زورونا', 'موقع الكنيسة', 'الكنيسة', 'عرب العيايدة، ناحية الجبل الأصفر، مطرانية شبين القناطر وتوابعها.', 'services/sunday-school/secondary-meeting.jpg', $p( 'هنا يكتب محرر المحتوى وصفًا لطريقة الوصول: من أقرب طريق رئيسي، ووسائل المواصلات، وعلامة مميزة قريبة من الكنيسة.' ) ),
	);
	foreach ( $pages as $i => $pg ) {
		if ( get_page_by_path( $pg[0] ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $pg[0],
			'post_title'   => $pg[1],
			'post_excerpt' => $pg[5],
			'post_content' => $pg[7],
			'menu_order'   => $i + 1,
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		stmina_seed_meta( 'post', $id, 'hero_eyebrow', $pg[2], 'field_stmina_page_eyebrow', $id );
		stmina_seed_meta( 'post', $id, 'hero_title', $pg[3], 'field_stmina_page_title', $id );
		stmina_seed_meta( 'post', $id, 'hero_highlight', $pg[4], 'field_stmina_page_highlight', $id );
		$hero = stmina_seed_image( $pg[6] );
		if ( $hero ) {
			set_post_thumbnail( $id, $hero );
		}
	}
}
