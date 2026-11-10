<?php
/**
 * 개인통관고유부호 보호 (암호화 · 접근 제한 · 마스킹 · 삭제 정책).
 *
 * - 저장: libsodium(secretbox)으로 암호화해 주문 메타에 저장합니다. 평문은 저장하지 않습니다.
 * - 키: wp-config.php 의 GOLPE_CUSTOMS_KEY 상수를 권장합니다. (없으면 워드프레스 보안 키로 대체)
 *       ⚠️ 키 또는 보안 키(salt)를 바꾸면 기존에 암호화된 번호는 복호화할 수 없습니다.
 * - 열람: 기본은 마스킹(P*********123). 'manage_woocommerce' 권한자만 "보기" 버튼으로 열람하며,
 *         열람할 때마다 주문 메모에 기록이 남습니다.
 * - 삭제: 주문이 완료 · 취소 · 환불 · 실패 상태가 되고 보관 기간(기본 90일)이 지나면 매일 자동 삭제합니다.
 *         워드프레스 개인정보 삭제 요청(도구 > 개인 데이터 지우기)에도 연동됩니다.
 *
 * 이 파일은 WooCommerce 가 활성화된 경우에만 불러옵니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GOLPE_CUSTOMS_META_ENC', '_golpe_customs_code_enc' );
define( 'GOLPE_CUSTOMS_META_MASKED', '_golpe_customs_code_masked' );
define( 'GOLPE_CUSTOMS_META_LEGACY', '_golpe_customs_code' );
define( 'GOLPE_CUSTOMS_META_DELETED', '_golpe_customs_code_deleted_at' );
define( 'GOLPE_CUSTOMS_CRON_HOOK', 'golpe_customs_cleanup' );

/* --------------------------------------------------------------------------
 * 마스킹 · 권한
 * ----------------------------------------------------------------------- */

/**
 * 마스킹: 첫 글자(P)와 마지막 3자리만 표시 (예: P*********123).
 *
 * @param string $code 개인통관고유부호.
 * @return string
 */
function golpe_mask_customs_code( $code ) {
	$code = (string) $code;
	$len  = strlen( $code );
	if ( $len <= 4 ) {
		return str_repeat( '*', $len );
	}
	return substr( $code, 0, 1 ) . str_repeat( '*', $len - 4 ) . substr( $code, -3 );
}

/**
 * 전체 번호 열람 권한.
 *
 * @return bool
 */
function golpe_can_view_customs_code() {
	$capability = apply_filters( 'golpe_customs_code_capability', 'manage_woocommerce' );
	return current_user_can( $capability );
}

/**
 * 보관 기간(일).
 *
 * @return int
 */
function golpe_customs_retention_days() {
	$days = absint( golpe_agency_option( 'agency_customs_retention_days' ) );
	return $days > 0 ? $days : 90;
}

/* --------------------------------------------------------------------------
 * 암호화
 * ----------------------------------------------------------------------- */

/**
 * 전용 암호화 키 설정 여부.
 *
 * @return bool
 */
function golpe_has_dedicated_customs_key() {
	return defined( 'GOLPE_CUSTOMS_KEY' ) && '' !== (string) GOLPE_CUSTOMS_KEY;
}

/**
 * 32바이트 암호화 키.
 *
 * @return string
 */
function golpe_customs_key() {
	$raw = golpe_has_dedicated_customs_key() ? (string) GOLPE_CUSTOMS_KEY : wp_salt( 'secure_auth' );
	return hash( 'sha256', 'golpe-customs|' . $raw, true );
}

/**
 * 암호화 사용 가능 여부. (워드프레스에 sodium_compat 가 포함되어 있어 보통 항상 가능)
 *
 * @return bool
 */
function golpe_customs_crypto_available() {
	return function_exists( 'sodium_crypto_secretbox' ) && function_exists( 'sodium_crypto_secretbox_open' );
}

/**
 * 암호화.
 *
 * @param string $plain 평문.
 * @return string|false 'v1:' 형식 문자열, 실패 시 false.
 */
function golpe_encrypt_customs_code( $plain ) {
	if ( ! golpe_customs_crypto_available() ) {
		return false;
	}
	try {
		$nonce  = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$cipher = sodium_crypto_secretbox( (string) $plain, $nonce, golpe_customs_key() );
		return 'v1:' . base64_encode( $nonce . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- 암호문 저장용.
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * 복호화.
 *
 * @param string $stored 저장된 값.
 * @return string 실패 시 빈 문자열.
 */
function golpe_decrypt_customs_code( $stored ) {
	$stored = (string) $stored;
	if ( 0 !== strpos( $stored, 'v1:' ) || ! golpe_customs_crypto_available() ) {
		return '';
	}
	$bin = base64_decode( substr( $stored, 3 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	if ( false === $bin || strlen( $bin ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
		return '';
	}
	try {
		$plain = sodium_crypto_secretbox_open(
			substr( $bin, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ),
			substr( $bin, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ),
			golpe_customs_key()
		);
	} catch ( Exception $e ) {
		return '';
	}
	return false === $plain ? '' : $plain;
}

/* --------------------------------------------------------------------------
 * 주문 저장 · 조회 · 삭제
 * ----------------------------------------------------------------------- */

/**
 * 주문에 암호화 저장 (평문 저장 안 함). 저장은 호출한 쪽에서 $order->save().
 *
 * @param WC_Order $order 주문.
 * @param string   $code  검증된 번호.
 * @return bool
 */
function golpe_store_customs_code( $order, $code ) {
	$encrypted = golpe_encrypt_customs_code( $code );
	if ( false === $encrypted ) {
		return false;
	}
	$order->update_meta_data( GOLPE_CUSTOMS_META_ENC, $encrypted );
	$order->update_meta_data( GOLPE_CUSTOMS_META_MASKED, golpe_mask_customs_code( $code ) );
	$order->delete_meta_data( GOLPE_CUSTOMS_META_LEGACY );
	return true;
}

/**
 * 주문의 번호 복호화. (예전 평문 저장분도 읽음)
 *
 * @param WC_Order $order 주문.
 * @return string
 */
function golpe_get_customs_code( $order ) {
	$enc = $order->get_meta( GOLPE_CUSTOMS_META_ENC );
	if ( $enc ) {
		return golpe_decrypt_customs_code( $enc );
	}
	return (string) $order->get_meta( GOLPE_CUSTOMS_META_LEGACY );
}

/**
 * 주문의 마스킹 번호.
 *
 * @param WC_Order $order 주문.
 * @return string
 */
function golpe_get_masked_customs_code( $order ) {
	$masked = (string) $order->get_meta( GOLPE_CUSTOMS_META_MASKED );
	if ( '' !== $masked ) {
		return $masked;
	}
	$legacy = (string) $order->get_meta( GOLPE_CUSTOMS_META_LEGACY );
	return '' !== $legacy ? golpe_mask_customs_code( $legacy ) : '';
}

/**
 * 주문에 번호가 남아 있는지.
 *
 * @param WC_Order $order 주문.
 * @return bool
 */
function golpe_order_has_customs_code( $order ) {
	return (bool) ( $order->get_meta( GOLPE_CUSTOMS_META_ENC ) || $order->get_meta( GOLPE_CUSTOMS_META_LEGACY ) );
}

/**
 * 주문에서 번호 삭제 (+ 기록). 저장까지 수행.
 *
 * @param WC_Order $order  주문.
 * @param string   $reason 사유 (주문 메모에 기록).
 */
function golpe_delete_customs_code( $order, $reason ) {
	$order->delete_meta_data( GOLPE_CUSTOMS_META_ENC );
	$order->delete_meta_data( GOLPE_CUSTOMS_META_MASKED );
	$order->delete_meta_data( GOLPE_CUSTOMS_META_LEGACY );
	$order->update_meta_data( GOLPE_CUSTOMS_META_DELETED, gmdate( 'Y-m-d H:i:s' ) );
	$order->save();
	/* translators: %s: 삭제 사유 */
	$order->add_order_note( sprintf( __( '개인통관고유부호 삭제: %s', 'golpe' ), $reason ) );
}

/* --------------------------------------------------------------------------
 * 관리자 열람 (AJAX + 기록)
 * ----------------------------------------------------------------------- */

/**
 * "보기" 버튼 요청 처리.
 */
function golpe_ajax_reveal_customs_code() {
	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	check_ajax_referer( 'golpe_reveal_customs_' . $order_id, 'nonce' );

	if ( ! golpe_can_view_customs_code() ) {
		wp_send_json_error( array( 'message' => __( '열람 권한이 없습니다.', 'golpe' ) ), 403 );
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		wp_send_json_error( array( 'message' => __( '주문을 찾을 수 없습니다.', 'golpe' ) ), 404 );
	}

	$code = golpe_get_customs_code( $order );
	if ( '' === $code ) {
		wp_send_json_error( array( 'message' => __( '번호를 복호화할 수 없습니다. 암호화 키가 바뀌었을 수 있습니다.', 'golpe' ) ), 500 );
	}

	$user = wp_get_current_user();
	/* translators: 1: 사용자 아이디, 2: 사용자 번호 */
	$order->add_order_note( sprintf( __( '개인통관고유부호 열람: %1$s (ID %2$d)', 'golpe' ), $user->user_login, $user->ID ) );

	nocache_headers();
	wp_send_json_success( array( 'code' => $code ) );
}
add_action( 'wp_ajax_golpe_reveal_customs_code', 'golpe_ajax_reveal_customs_code' );

/**
 * 관리자 주문 화면 표시 (마스킹 + 권한자에게 "보기" 버튼).
 *
 * @param WC_Order $order 주문.
 */
function golpe_admin_order_customs_markup( $order ) {
	$deleted = $order->get_meta( GOLPE_CUSTOMS_META_DELETED );

	if ( ! golpe_order_has_customs_code( $order ) ) {
		if ( $deleted ) {
			/* translators: %s: 삭제 일시 */
			echo '<p><strong>' . esc_html__( '개인통관고유부호', 'golpe' ) . ':</strong> ' . esc_html( sprintf( __( '삭제됨 (%s UTC)', 'golpe' ), $deleted ) ) . '</p>';
		}
		return;
	}

	$order_id = $order->get_id();
	echo '<p class="golpe-customs-admin"><strong>' . esc_html__( '개인통관고유부호', 'golpe' ) . ':</strong> <span class="golpe-customs-admin__value">' . esc_html( golpe_get_masked_customs_code( $order ) ) . '</span> ';

	if ( golpe_can_view_customs_code() ) {
		printf(
			'<button type="button" class="button button-small golpe-customs-reveal" data-order="%1$d" data-nonce="%2$s">%3$s</button>',
			absint( $order_id ),
			esc_attr( wp_create_nonce( 'golpe_reveal_customs_' . $order_id ) ),
			esc_html__( '보기 (열람 기록됨)', 'golpe' )
		);
		?>
		<script>
		( function () {
			var btn = document.querySelector( '.golpe-customs-reveal[data-order="<?php echo absint( $order_id ); ?>"]' );
			if ( ! btn || btn.dataset.bound ) { return; }
			btn.dataset.bound = '1';
			btn.addEventListener( 'click', function () {
				var body = new URLSearchParams( { action: 'golpe_reveal_customs_code', order_id: btn.dataset.order, nonce: btn.dataset.nonce } );
				btn.disabled = true;
				fetch( <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, { method: 'POST', credentials: 'same-origin', body: body } )
					.then( function ( r ) { return r.json(); } )
					.then( function ( res ) {
						var out = btn.parentNode.querySelector( '.golpe-customs-admin__value' );
						if ( res && res.success ) {
							out.textContent = res.data.code;
							btn.remove();
						} else {
							btn.disabled = false;
							window.alert( ( res && res.data && res.data.message ) || 'Error' );
						}
					} )
					.catch( function () { btn.disabled = false; } );
			} );
		} )();
		</script>
		<?php
	}
	echo '</p>';
}

/* --------------------------------------------------------------------------
 * 보관 기간 경과 자동 삭제 (매일)
 * ----------------------------------------------------------------------- */

/**
 * 매일 실행 예약.
 */
function golpe_schedule_customs_cleanup() {
	if ( ! wp_next_scheduled( GOLPE_CUSTOMS_CRON_HOOK ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', GOLPE_CUSTOMS_CRON_HOOK );
	}
}
add_action( 'init', 'golpe_schedule_customs_cleanup' );

/**
 * 테마를 바꾸면 예약 해제.
 */
function golpe_unschedule_customs_cleanup() {
	wp_clear_scheduled_hook( GOLPE_CUSTOMS_CRON_HOOK );
}
add_action( 'switch_theme', 'golpe_unschedule_customs_cleanup' );

/**
 * 보관 기간이 지난 주문의 번호 삭제.
 * 기준: 주문 상태가 완료 · 취소 · 환불 · 실패이고, 마지막 수정일로부터 보관 기간 경과.
 *
 * @param int $batch 한 번에 처리할 주문 수.
 * @return int 삭제한 주문 수.
 */
function golpe_run_customs_cleanup( $batch = 50 ) {
	$cutoff = time() - golpe_customs_retention_days() * DAY_IN_SECONDS;
	$orders = wc_get_orders(
		array(
			'limit'         => absint( $batch ),
			'status'        => array( 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ),
			'date_modified' => '<' . $cutoff,
			'meta_query'    => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => GOLPE_CUSTOMS_META_ENC,
					'compare' => 'EXISTS',
				),
				array(
					'key'     => GOLPE_CUSTOMS_META_LEGACY,
					'compare' => 'EXISTS',
				),
			),
		)
	);

	$count = 0;
	foreach ( $orders as $order ) {
		if ( ! golpe_order_has_customs_code( $order ) ) {
			continue;
		}
		/* translators: %d: 보관 일수 */
		golpe_delete_customs_code( $order, sprintf( __( '보관 기간(%d일) 경과', 'golpe' ), golpe_customs_retention_days() ) );
		$count++;
	}
	return $count;
}
add_action( GOLPE_CUSTOMS_CRON_HOOK, 'golpe_run_customs_cleanup' );

/* --------------------------------------------------------------------------
 * 워드프레스 개인정보 도구 연동 (내보내기 · 삭제 요청)
 * ----------------------------------------------------------------------- */

/**
 * 삭제 도구 등록.
 *
 * @param array $erasers 등록된 삭제 도구.
 * @return array
 */
function golpe_register_customs_eraser( $erasers ) {
	$erasers['golpe-customs-code'] = array(
		'eraser_friendly_name' => __( 'GOLPE 개인통관고유부호', 'golpe' ),
		'callback'             => 'golpe_privacy_erase_customs_code',
	);
	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'golpe_register_customs_eraser' );

/**
 * 이메일 주소 기준 주문의 번호 삭제.
 *
 * @param string $email 이메일.
 * @param int    $page  페이지.
 * @return array
 */
function golpe_privacy_erase_customs_code( $email, $page = 1 ) {
	$per_page = 10;
	$orders   = wc_get_orders(
		array(
			'billing_email' => $email,
			'limit'         => $per_page,
			'page'          => absint( $page ),
		)
	);
	$removed = false;
	foreach ( $orders as $order ) {
		if ( golpe_order_has_customs_code( $order ) ) {
			golpe_delete_customs_code( $order, __( '개인정보 삭제 요청', 'golpe' ) );
			$removed = true;
		}
	}
	return array(
		'items_removed'  => $removed,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => count( $orders ) < $per_page,
	);
}

/**
 * 내보내기 도구 등록. (마스킹된 번호만 내보냄)
 *
 * @param array $exporters 등록된 내보내기 도구.
 * @return array
 */
function golpe_register_customs_exporter( $exporters ) {
	$exporters['golpe-customs-code'] = array(
		'exporter_friendly_name' => __( 'GOLPE 개인통관고유부호', 'golpe' ),
		'callback'               => 'golpe_privacy_export_customs_code',
	);
	return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'golpe_register_customs_exporter' );

/**
 * 이메일 주소 기준 주문의 번호(마스킹) 내보내기.
 *
 * @param string $email 이메일.
 * @param int    $page  페이지.
 * @return array
 */
function golpe_privacy_export_customs_code( $email, $page = 1 ) {
	$per_page = 10;
	$orders   = wc_get_orders(
		array(
			'billing_email' => $email,
			'limit'         => $per_page,
			'page'          => absint( $page ),
		)
	);
	$data = array();
	foreach ( $orders as $order ) {
		$masked = golpe_get_masked_customs_code( $order );
		if ( '' === $masked ) {
			continue;
		}
		$data[] = array(
			'group_id'    => 'golpe-customs-code',
			'group_label' => __( '개인통관고유부호', 'golpe' ),
			'item_id'     => 'golpe-customs-' . $order->get_id(),
			'data'        => array(
				array(
					'name'  => __( '주문번호', 'golpe' ),
					'value' => $order->get_order_number(),
				),
				array(
					'name'  => __( '개인통관고유부호(일부 가림)', 'golpe' ),
					'value' => $masked,
				),
			),
		);
	}
	return array(
		'data' => $data,
		'done' => count( $orders ) < $per_page,
	);
}

/* --------------------------------------------------------------------------
 * 관리자 경고: 전용 암호화 키 미설정
 * ----------------------------------------------------------------------- */

/**
 * 전용 키가 없으면 관리자에게 안내.
 */
function golpe_customs_key_admin_notice() {
	if ( golpe_has_dedicated_customs_key() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>GOLPE:</strong> ' . esc_html__( '개인통관고유부호 암호화용 전용 키(GOLPE_CUSTOMS_KEY)가 wp-config.php 에 없어 워드프레스 보안 키로 대신 암호화하고 있습니다. 운영 전 README "6-5" 안내에 따라 전용 키를 추가하세요. (키를 추가 · 변경하면 그 전에 저장된 번호는 열람할 수 없습니다)', 'golpe' ) . '</p></div>';
}
add_action( 'admin_notices', 'golpe_customs_key_admin_notice' );
