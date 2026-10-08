<?php
/**
 * المخدومين والملاحظات: التحقق، والإضافة، والتعديل، والقراية.
 * كل الدوال هنا من غير فحص صلاحيات، والفحص في مسارات REST (rest.php).
 * كل استعلام فيه قيمة من بره بيعدّي على $wpdb->prepare.
 */

defined( 'ABSPATH' ) || exit;

/**
 * موبايل مصري بالشكل الموحّد 01xxxxxxxxx، أو '' لو مش صحيح.
 * بيظبط لوحده: الأرقام العربي (٠١٢)، والمسافات والشُرط، و+20 أو 0020 أو 20 في الأول،
 * والصفر اللي Excel بيشيله (1012345678 ← 01012345678).
 */
function stmina_att_phone( $raw ) {
	$p = stmina_att_digits( $raw );
	$p = preg_replace( '/^(0020|20)(?=1\d{9}$)/', '', $p );
	if ( preg_match( '/^1\d{9}$/', $p ) ) {
		$p = '0' . $p;
	}
	return preg_match( '/^01[0125]\d{8}$/', $p ) ? $p : '';
}

/**
 * الأرقام بس من النص، والأرقام العربي (٠١٢) بتتحوّل لإنجليزي.
 */
function stmina_att_digits( $raw ) {
	$p = strtr( (string) $raw, array( '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9' ) );
	return preg_replace( '/\D/', '', $p );
}

/**
 * الاسم بعد تنضيف المسافات الزيادة.
 */
function stmina_att_name( $raw ) {
	return trim( preg_replace( '/\s+/u', ' ', sanitize_text_field( (string) $raw ) ) );
}

/**
 * الخدمة بالرابط الداخلي (slug)، أو null.
 */
function stmina_att_service( $slug ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . stmina_att_table( 'services' ) . ' WHERE slug = %s', $slug ) );
}

/**
 * كود QR عشوائي: 32 حرف من حروف آمنة في الروابط (أكتر من 190 bit عشوائية).
 */
function stmina_att_new_token() {
	return substr( rtrim( strtr( base64_encode( random_bytes( 24 ) ), '+/', '-_' ), '=' ), 0, 32 );
}

/**
 * التحقق من الاسم والموبايل. بيرجّع القيم المظبوطة، أو WP_Error فيها كل الأخطاء بالخانة.
 *
 * @param array $data      full_name و/أو phone.
 * @param bool  $required  الإضافة لازم الاتنين، والتعديل اللي اتبعت بس.
 * @param int   $except_id رقم المخدوم نفسه، علشان موبايله مايتحسبش متكرر.
 */
function stmina_att_validate( $data, $required, $except_id = 0 ) {
	global $wpdb;
	$errors = array();
	$clean  = array();

	if ( $required || isset( $data['full_name'] ) ) {
		$name = stmina_att_name( isset( $data['full_name'] ) ? $data['full_name'] : '' );
		if ( '' === $name ) {
			$errors['full_name'] = 'اكتب الاسم.';
		} elseif ( count( explode( ' ', $name ) ) < 2 ) {
			$errors['full_name'] = 'اكتب الاسم بالكامل، اسمين على الأقل.';
		} else {
			$clean['full_name'] = $name;
		}
	}

	if ( $required || isset( $data['phone'] ) ) {
		$raw   = isset( $data['phone'] ) ? $data['phone'] : '';
		$phone = stmina_att_phone( $raw );
		if ( '' === trim( (string) $raw ) ) {
			$errors['phone'] = 'اكتب رقم الموبايل.';
		} elseif ( '' === $phone ) {
			$errors['phone'] = 'رقم الموبايل لازم يكون مصري 11 رقم، زي 01012345678.';
		} else {
			$other = $wpdb->get_row( $wpdb->prepare( 'SELECT id, full_name FROM ' . stmina_att_table( 'members' ) . ' WHERE phone = %s AND id != %d', $phone, $except_id ) );
			if ( $other ) {
				$errors['phone'] = 'الرقم ده متسجّل قبل كده لـ ' . $other->full_name . '.';
			} else {
				$clean['phone'] = $phone;
			}
		}
	}

	if ( isset( $data['status'] ) ) {
		if ( in_array( $data['status'], array( 'active', 'stopped' ), true ) ) {
			$clean['status'] = $data['status'];
		} else {
			$errors['status'] = 'الحالة لازم تكون active أو stopped.';
		}
	}

	if ( $errors ) {
		return new WP_Error( 'stmina_invalid', 'فيه بيانات محتاجة تتصلّح.', array( 'status' => 400, 'fields' => $errors ) );
	}
	return $clean;
}

/**
 * إضافة مخدوم لخدمة. تاريخ التسجيل والكود بيتعملوا لوحدهم.
 *
 * @return int|WP_Error رقم المخدوم.
 */
function stmina_att_create_member( $data, $service ) {
	global $wpdb;
	$clean = stmina_att_validate( $data, true );
	if ( is_wp_error( $clean ) ) {
		return $clean;
	}
	$now = current_time( 'mysql' );
	$ok  = $wpdb->insert( stmina_att_table( 'members' ), array(
		'full_name'     => $clean['full_name'],
		'phone'         => $clean['phone'],
		'qr_token'      => stmina_att_new_token(),
		'status'        => 'active',
		'registered_at' => $now,
		'created_by'    => get_current_user_id(),
		'updated_at'    => $now,
	) );
	if ( ! $ok ) {
		// غالبًا طلبين في نفس اللحظة بنفس الرقم، والقيد الفريد في الجدول رفض التاني
		return new WP_Error( 'stmina_db', 'ماقدرناش نحفظ المخدوم. جرّب تاني.', array( 'status' => 409 ) );
	}
	$id = (int) $wpdb->insert_id;
	$wpdb->insert( stmina_att_table( 'member_service' ), array( 'member_id' => $id, 'service_id' => $service->id, 'joined_at' => $now ) );
	return $id;
}

/**
 * تعديل الاسم أو الموبايل أو الحالة. مفيش حذف: الإيقاف هو status = stopped.
 *
 * @return true|WP_Error
 */
function stmina_att_update_member( $id, $data ) {
	global $wpdb;
	$clean = stmina_att_validate( $data, false, $id );
	if ( is_wp_error( $clean ) ) {
		return $clean;
	}
	if ( ! $clean ) {
		return true;
	}
	$clean['updated_at'] = current_time( 'mysql' );
	$wpdb->update( stmina_att_table( 'members' ), $clean, array( 'id' => $id ) );
	return true;
}

/**
 * مخدوم واحد في خدمة، أو null لو مش موجود أو مش في الخدمة دي.
 */
function stmina_att_get_member( $id, $service ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare(
		'SELECT m.*, ms.joined_at FROM ' . stmina_att_table( 'members' ) . ' m
		 JOIN ' . stmina_att_table( 'member_service' ) . ' ms ON ms.member_id = m.id AND ms.service_id = %d
		 WHERE m.id = %d',
		$service->id,
		$id
	) );
}

/**
 * مخدومين خدمة، بالاسم. بفلتر الحالة والبحث (في الاسم أو الموبايل).
 *
 * @return object[]
 */
function stmina_att_list_members( $service, $status = '', $search = '' ) {
	global $wpdb;
	$sql  = 'SELECT m.* FROM ' . stmina_att_table( 'members' ) . ' m
		JOIN ' . stmina_att_table( 'member_service' ) . ' ms ON ms.member_id = m.id AND ms.service_id = %d';
	$args = array( $service->id );
	$where = array();
	if ( in_array( $status, array( 'active', 'stopped' ), true ) ) {
		$where[] = 'm.status = %s';
		$args[]  = $status;
	}
	if ( '' !== $search ) {
		// الاسم بالنص زي ما هو، والموبايل بالأرقام بس (علشان "٠١٠ ١٢" تلاقي 01012...)
		$digits  = stmina_att_digits( $search );
		$where[] = '(m.full_name LIKE %s OR m.phone LIKE %s)';
		$args[]  = '%' . $wpdb->esc_like( $search ) . '%';
		$args[]  = '' === $digits ? '-' : '%' . $wpdb->esc_like( $digits ) . '%';
	}
	if ( $where ) {
		$sql .= ' WHERE ' . implode( ' AND ', $where );
	}
	$sql .= ' ORDER BY m.full_name ASC';
	return $wpdb->get_results( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL -- الجملة مبنية من أجزاء ثابتة
}

/**
 * ملاحظات مخدوم، الأحدث الأول، باسم الكاتب.
 *
 * @return array[]
 */
function stmina_att_notes( $member_id ) {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . stmina_att_table( 'notes' ) . ' WHERE member_id = %d ORDER BY created_at DESC, id DESC', $member_id ) );
	return array_map( function ( $n ) {
		$author = get_userdata( $n->author_id );
		return array(
			'id'         => (int) $n->id,
			'body'       => $n->body,
			'author'     => $author ? $author->display_name : '',
			'created_at' => $n->created_at,
		);
	}, $rows );
}

/**
 * إضافة ملاحظة.
 *
 * @return int|WP_Error
 */
function stmina_att_add_note( $member_id, $body ) {
	global $wpdb;
	$body = trim( sanitize_textarea_field( (string) $body ) );
	if ( '' === $body ) {
		return new WP_Error( 'stmina_invalid', 'اكتب الملاحظة.', array( 'status' => 400, 'fields' => array( 'body' => 'اكتب الملاحظة.' ) ) );
	}
	$wpdb->insert( stmina_att_table( 'notes' ), array(
		'member_id'  => $member_id,
		'author_id'  => get_current_user_id(),
		'body'       => $body,
		'created_at' => current_time( 'mysql' ),
	) );
	return (int) $wpdb->insert_id;
}

/**
 * رابط كارت المخدوم (صفحته العامة): /me/<الكود>/
 * نفس الرابط ده هو اللي جوه الـ QR، فشاشة المسح بتاخد الكود من آخره.
 */
function stmina_att_card_url( $token ) {
	return home_url( '/me/' . rawurlencode( $token ) . '/' );
}

/**
 * المخدوم بكود الكارت، أو null. الكود بيتقارن بالظبط، وأي شكل غلط بيرجّع null من غير استعلام.
 */
function stmina_att_member_by_token( $token ) {
	global $wpdb;
	if ( ! is_string( $token ) || ! preg_match( '/^[A-Za-z0-9_-]{32}$/', $token ) ) {
		return null;
	}
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . stmina_att_table( 'members' ) . ' WHERE qr_token = %s', $token ) );
}

/**
 * إعادة إصدار الكارت: كود جديد مكان القديم، فالرابط القديم بيبطل فورًا.
 *
 * @return string الكود الجديد.
 */
function stmina_att_reissue( $member_id ) {
	global $wpdb;
	$old   = $wpdb->get_var( $wpdb->prepare( 'SELECT qr_token FROM ' . stmina_att_table( 'members' ) . ' WHERE id = %d', $member_id ) );
	$token = stmina_att_new_token();
	// الكود القديم بيتسجّل كملغي، علشان لو حد مسحه يظهر "الكارت ده ملغي" مش "كارت مش معروف"
	if ( $old ) {
		$wpdb->replace( stmina_att_table( 'revoked' ), array( 'token' => $old, 'member_id' => $member_id, 'revoked_at' => current_time( 'mysql' ) ) );
	}
	// والكارت الجديد لسه مااتبعتش
	$wpdb->update( stmina_att_table( 'members' ), array( 'qr_token' => $token, 'card_sent_at' => null, 'pin_hash' => null, 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $member_id ) );
	return $token;
}

/**
 * عدد سجلات الحضور للمخدوم في كل الجلسات.
 */
function stmina_att_member_records_count( $member_id ) {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . stmina_att_table( 'records' ) . ' WHERE member_id = %d', $member_id ) );
}

/**
 * حذف نهائي لمخدوم اتضاف بالغلط. للمدير بس، وبشرط إن مالوش أي حضور متسجّل:
 * القاعدة لسه "الإيقاف بدل الحذف"، والاستثناء ده للغلطات بس (زي تسجيل تجربة).
 * بيتمسح معاه كل اللي يخصه: الربط بالخدمات، والملاحظات، وأكواده القديمة.
 *
 * @return true|WP_Error
 */
function stmina_att_delete_member( $member_id ) {
	global $wpdb;
	if ( stmina_att_member_records_count( $member_id ) ) {
		return new WP_Error( 'stmina_has_records', 'المخدوم ده ليه حضور متسجّل، فمينفعش يتمسح. أوقفه بدل كده.', array( 'status' => 409 ) );
	}
	$wpdb->delete( stmina_att_table( 'member_service' ), array( 'member_id' => $member_id ) );
	$wpdb->delete( stmina_att_table( 'notes' ), array( 'member_id' => $member_id ) );
	$wpdb->delete( stmina_att_table( 'revoked' ), array( 'member_id' => $member_id ) );
	$wpdb->delete( stmina_att_table( 'members' ), array( 'id' => $member_id ) );
	return true;
}
