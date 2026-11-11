<?php
/**
 * 개인통관고유부호 보호 (암호화 · 키 관리 · 접근 제한 · 마스킹 · 삭제 정책).
 *
 * - 저장: libsodium(secretbox)으로 암호화해 주문 메타에 저장합니다. 평문은 저장하지 않습니다.
 * - 키: wp-config.php 의 GOLPE_CUSTOMS_KEY 상수(32자 이상)를 사용합니다.
 *       · 키를 바꿀 때는 이전 키를 GOLPE_CUSTOMS_KEY_PREVIOUS 에 남겨 두면 기존 번호도 열 수 있고,
 *         매일 예약 작업과 열람 시점에 새 키로 자동 재암호화됩니다.
 *       · 전용 키가 없을 때는 워드프레스 보안 키로 암호화하며, 나중에 전용 키를 추가해도 자동으로 옮겨집니다.
 * - 열람: 기본은 마스킹(P*********123). 'golpe_view_customs_code' 권한자(기본: 관리자)만 "보기" 버튼으로
 *         열람하며, 열람할 때마다 주문 메모에 기록됩니다. 1시간 열람 횟수 제한, 60초 후 자동 가림.
 * - 삭제: 주문이 완료 · 취소 · 환불 · 실패 상태로 "바뀐 날"로부터 보관 기간(기본 90일)이 지나면 매일 자동 삭제합니다.
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
define( 'GOLPE_CUSTOMS_META_FINAL_AT', '_golpe_final_status_at' );
define( 'GOLPE_CUSTOMS_CRON_HOOK', 'golpe_customs_cleanup' );
define( 'GOLPE_CUSTOMS_CAP', 'golpe_view_customs_code' );
define( 'GOLPE_CUSTOMS_MIN_KEY_LENGTH', 32 );

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
 * 열람 권한을 자동으로 주는 역할.
 * 기본은 관리자(administrator)만. 사용자 정의하기에서 상점 관리자(shop_manager)를 추가할 수 있습니다.
 * 그 밖의 사용자에게는 역할 편집 플러그인 등으로 'golpe_view_customs_code' 권한을 직접 부여하세요.
 *
 * @return string[]
 */
function golpe_customs_viewer_roles() {
	$roles = array( 'administrator' );
	if ( get_theme_mod( 'golpe_agency_customs_allow_shop_manager', false ) ) {
		$roles[] = 'shop_manager';
	}
	return apply_filters( 'golpe_customs_viewer_roles', $roles );
}

/**
 * 지정된 역할의 사용자에게 열람 권한을 부여 (DB 의 역할 정보는 바꾸지 않음).
 *
 * @param array   $allcaps 사용자 권한.
 * @param array   $caps    요청 권한.
 * @param array   $args    인자.
 * @param WP_User $user    사용자.
 * @return array
 */
function golpe_grant_customs_cap( $allcaps, $caps, $args, $user ) {
	if ( ! in_array( GOLPE_CUSTOMS_CAP, (array) $caps, true ) || ! $user instanceof WP_User ) {
		return $allcaps;
	}
	if ( array_intersect( golpe_customs_viewer_roles(), (array) $user->roles ) && ! empty( $allcaps['manage_woocommerce'] ) ) {
		$allcaps[ GOLPE_CUSTOMS_CAP ] = true;
	}
	return $allcaps;
}
add_filter( 'user_has_cap', 'golpe_grant_customs_cap', 10, 4 );

/**
 * 전체 번호 열람 권한.
 *
 * @return bool
 */
function golpe_can_view_customs_code() {
	$capability = apply_filters( 'golpe_customs_code_capability', GOLPE_CUSTOMS_CAP );
	return current_user_can( $capability );
}

/**
 * 1시간 열람 한도 (사용자별).
 *
 * @return int
 */
function golpe_customs_reveal_limit() {
	return (int) apply_filters( 'golpe_customs_reveal_limit_per_hour', 30 );
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

/**
 * 보관 기간 계산이 시작되는 "최종 상태".
 *
 * @return string[] 'wc-' 접두어 없는 상태 이름.
 */
function golpe_customs_final_statuses() {
	return apply_filters( 'golpe_customs_final_statuses', array( 'completed', 'cancelled', 'refunded', 'failed' ) );
}

/* --------------------------------------------------------------------------
 * 키 관리 · 암호화
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
 * 전용 키가 너무 짧은지.
 *
 * @return bool
 */
function golpe_customs_key_is_weak() {
	return golpe_has_dedicated_customs_key() && strlen( (string) GOLPE_CUSTOMS_KEY ) < GOLPE_CUSTOMS_MIN_KEY_LENGTH;
}

/**
 * 원본 키 문자열 → 32바이트 키.
 *
 * @param string $raw 원본.
 * @return string
 */
function golpe_customs_derive_key( $raw ) {
	return hash( 'sha256', 'golpe-customs|' . $raw, true );
}

/**
 * 키 식별자 (암호문에 함께 저장, 키 자체는 노출되지 않음).
 *
 * @param string $key 32바이트 키.
 * @return string 8자리.
 */
function golpe_customs_key_id( $key ) {
	return substr( hash( 'sha256', 'golpe-kid|' . $key ), 0, 8 );
}

/**
 * 현재(암호화용) 키.
 *
 * @return string
 */
function golpe_customs_key() {
	$raw = golpe_has_dedicated_customs_key() ? (string) GOLPE_CUSTOMS_KEY : wp_salt( 'secure_auth' );
	return golpe_customs_derive_key( $raw );
}

/**
 * 복호화에 쓸 수 있는 모든 키 (현재 키 → 이전 키 → 워드프레스 보안 키 순).
 *
 * @return array 키 식별자 => 32바이트 키.
 */
function golpe_customs_decrypt_keys() {
	$raws = array();
	if ( golpe_has_dedicated_customs_key() ) {
		$raws[] = (string) GOLPE_CUSTOMS_KEY;
	}
	if ( defined( 'GOLPE_CUSTOMS_KEY_PREVIOUS' ) ) {
		foreach ( (array) GOLPE_CUSTOMS_KEY_PREVIOUS as $previous ) {
			if ( '' !== (string) $previous ) {
				$raws[] = (string) $previous;
			}
		}
	}
	// 전용 키를 나중에 추가한 경우에도 기존 번호를 열 수 있도록 보안 키도 후보에 둡니다.
	$raws[] = wp_salt( 'secure_auth' );

	$keys = array();
	foreach ( $raws as $raw ) {
		$key                                = golpe_customs_derive_key( $raw );
		$keys[ golpe_customs_key_id( $key ) ] = $key;
	}
	return $keys;
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
 * @return string|false 'v2:키식별자:암호문' 형식, 실패 시 false.
 */
function golpe_encrypt_customs_code( $plain ) {
	if ( ! golpe_customs_crypto_available() ) {
		return false;
	}
	try {
		$key    = golpe_customs_key();
		$nonce  = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$cipher = sodium_crypto_secretbox( (string) $plain, $nonce, $key );
		return 'v2:' . golpe_customs_key_id( $key ) . ':' . base64_encode( $nonce . $cipher ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- 암호문 저장용.
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * 한 키로 복호화 시도.
 *
 * @param string $b64 base64 (nonce + 암호문).
 * @param string $key 32바이트 키.
 * @return string|false
 */
function golpe_customs_open( $b64, $key ) {
	$bin = base64_decode( $b64, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	if ( false === $bin || strlen( $bin ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
		return false;
	}
	try {
		return sodium_crypto_secretbox_open(
			substr( $bin, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ),
			substr( $bin, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ),
			$key
		);
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * 복호화. (v2: 키 식별자로 키 선택 / v1: 모든 후보 키 시도)
 *
 * @param string $stored 저장된 값.
 * @return string 실패 시 빈 문자열.
 */
function golpe_decrypt_customs_code( $stored ) {
	$stored = (string) $stored;
	if ( ! golpe_customs_crypto_available() ) {
		return '';
	}
	$keys = golpe_customs_decrypt_keys();

	if ( 0 === strpos( $stored, 'v2:' ) ) {
		$parts = explode( ':', $stored, 3 );
		if ( 3 !== count( $parts ) || ! isset( $keys[ $parts[1] ] ) ) {
			return '';
		}
		$plain = golpe_customs_open( $parts[2], $keys[ $parts[1] ] );
		return false === $plain ? '' : $plain;
	}

	if ( 0 === strpos( $stored, 'v1:' ) ) {
		foreach ( $keys as $key ) {
			$plain = golpe_customs_open( substr( $stored, 3 ), $key );
			if ( false !== $plain ) {
				return $plain;
			}
		}
	}
	return '';
}

/**
 * 저장값이 현재 키로 암호화되어 있는지.
 *
 * @param string $stored 저장된 값.
 * @return bool
 */
function golpe_customs_is_current_key( $stored ) {
	return 0 === strpos( (string) $stored, 'v2:' . golpe_customs_key_id( golpe_customs_key() ) . ':' );
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
 * 현재 키가 아니면(또는 예전 평문이면) 현재 키로 다시 암호화. 저장까지 수행.
 *
 * @param WC_Order $order 주문.
 * @return bool 다시 암호화했으면 true.
 */
function golpe_customs_reencrypt_order( $order ) {
	$enc = (string) $order->get_meta( GOLPE_CUSTOMS_META_ENC );
	if ( '' !== $enc && golpe_customs_is_current_key( $enc ) ) {
		return false;
	}
	$code = golpe_get_customs_code( $order );
	if ( '' === $code || ! golpe_store_customs_code( $order, $code ) ) {
		return false;
	}
	$order->save();
	return true;
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
 * 주문 상태 전환일 기록 (보관 기간 기준)
 * ----------------------------------------------------------------------- */

/**
 * 최종 상태로 바뀌면 그 시각을 기록하고, 다시 진행 상태로 돌아가면 기록을 지웁니다.
 *
 * @param int      $order_id 주문 번호.
 * @param string   $from     이전 상태.
 * @param string   $to       새 상태.
 * @param WC_Order $order    주문.
 */
function golpe_customs_track_final_status( $order_id, $from, $to, $order ) {
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order ) {
		return;
	}
	if ( in_array( $to, golpe_customs_final_statuses(), true ) ) {
		$order->update_meta_data( GOLPE_CUSTOMS_META_FINAL_AT, time() );
		$order->save_meta_data();
	} elseif ( $order->get_meta( GOLPE_CUSTOMS_META_FINAL_AT ) ) {
		$order->delete_meta_data( GOLPE_CUSTOMS_META_FINAL_AT );
		$order->save_meta_data();
	}
}
add_action( 'woocommerce_order_status_changed', 'golpe_customs_track_final_status', 10, 4 );

/**
 * 이 기능을 넣기 전에 이미 최종 상태였던 주문: 전환일 기록이 없으면 채워 넣습니다.
 * - 완료 주문: 완료일(date_completed)
 * - 그 외: 기록 시점(지금)부터 보관 기간 시작 (늦게 지우는 쪽으로 안전하게)
 *
 * @param int $batch 한 번에 처리할 주문 수.
 * @return int 채운 주문 수.
 */
function golpe_customs_backfill_final_status( $batch = 50 ) {
	$orders = wc_get_orders(
		array(
			'limit'      => absint( $batch ),
			'status'     => golpe_customs_final_statuses(),
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array(
					'key'     => GOLPE_CUSTOMS_META_FINAL_AT,
					'compare' => 'NOT EXISTS',
				),
				array(
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
			),
		)
	);
	foreach ( $orders as $order ) {
		$completed = $order->get_date_completed();
		$at        = ( 'completed' === $order->get_status() && $completed ) ? $completed->getTimestamp() : time();
		$order->update_meta_data( GOLPE_CUSTOMS_META_FINAL_AT, $at );
		$order->save_meta_data();
	}
	return count( $orders );
}

/* --------------------------------------------------------------------------
 * 관리자 열람 (AJAX + 기록 + 횟수 제한)
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

	$user_id   = get_current_user_id();
	$limit_key = 'golpe_customs_reveals_' . $user_id;
	$count     = (int) get_transient( $limit_key );
	if ( $count >= golpe_customs_reveal_limit() ) {
		wp_send_json_error( array( 'message' => __( '1시간 열람 한도를 넘었습니다. 잠시 후 다시 시도해 주세요.', 'golpe' ) ), 429 );
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		wp_send_json_error( array( 'message' => __( '주문을 찾을 수 없습니다.', 'golpe' ) ), 404 );
	}

	$code = golpe_get_customs_code( $order );
	if ( '' === $code ) {
		wp_send_json_error( array( 'message' => __( '번호를 복호화할 수 없습니다. 암호화 키 설정(README 6-5)을 확인해 주세요.', 'golpe' ) ), 500 );
	}

	set_transient( $limit_key, $count + 1, HOUR_IN_SECONDS );

	$user = wp_get_current_user();
	/* translators: 1: 사용자 아이디, 2: 사용자 번호 */
	$order->add_order_note( sprintf( __( '개인통관고유부호 열람: %1$s (ID %2$d)', 'golpe' ), $user->user_login, $user->ID ) );

	// 이전 키로 암호화된 번호는 현재 키로 옮겨 둡니다.
	golpe_customs_reencrypt_order( $order );

	nocache_headers();
	wp_send_json_success( array( 'code' => $code ) );
}
add_action( 'wp_ajax_golpe_reveal_customs_code', 'golpe_ajax_reveal_customs_code' );

/**
 * 관리자 주문 화면 표시 (마스킹 + 권한자에게 "보기" 버튼, 60초 후 자동 가림).
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
	$masked   = golpe_get_masked_customs_code( $order );
	echo '<p class="golpe-customs-admin"><strong>' . esc_html__( '개인통관고유부호', 'golpe' ) . ':</strong> <span class="golpe-customs-admin__value">' . esc_html( $masked ) . '</span> ';

	if ( golpe_can_view_customs_code() ) {
		printf(
			'<button type="button" class="button button-small golpe-customs-reveal" data-order="%1$d" data-nonce="%2$s" data-masked="%3$s">%4$s</button>',
			absint( $order_id ),
			esc_attr( wp_create_nonce( 'golpe_reveal_customs_' . $order_id ) ),
			esc_attr( $masked ),
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
				var out = btn.parentNode.querySelector( '.golpe-customs-admin__value' );
				btn.disabled = true;
				fetch( <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, { method: 'POST', credentials: 'same-origin', body: body } )
					.then( function ( r ) { return r.json(); } )
					.then( function ( res ) {
						if ( res && res.success ) {
							out.textContent = res.data.code;
							btn.hidden = true;
							// 60초 뒤 다시 가립니다.
							window.setTimeout( function () {
								out.textContent = btn.dataset.masked;
								btn.hidden = false;
								btn.disabled = false;
							}, 60000 );
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
	} else {
		echo '<span class="description">' . esc_html__( '(열람 권한 없음)', 'golpe' ) . '</span>';
	}
	echo '</p>';
}

/* --------------------------------------------------------------------------
 * 매일 예약 작업: 보관 기간 경과 삭제 + 키 교체분 재암호화
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
 * 기준: 주문이 최종 상태(완료 · 취소 · 환불 · 실패)로 "바뀐 시각"(_golpe_final_status_at)으로부터 보관 기간 경과.
 * 주문을 나중에 수정해도(메모 추가 등) 기준일은 바뀌지 않습니다.
 *
 * @param int $batch 한 번에 처리할 주문 수.
 * @return int 삭제한 주문 수.
 */
function golpe_run_customs_cleanup( $batch = 50 ) {
	golpe_customs_backfill_final_status( $batch );

	$cutoff = time() - golpe_customs_retention_days() * DAY_IN_SECONDS;
	$orders = wc_get_orders(
		array(
			'limit'      => absint( $batch ),
			'status'     => golpe_customs_final_statuses(),
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array(
					'key'     => GOLPE_CUSTOMS_META_FINAL_AT,
					'value'   => $cutoff,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				),
				array(
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
			),
		)
	);

	$count = 0;
	foreach ( $orders as $order ) {
		// 한 번 더 확인 (조회 이후 상태가 바뀌었을 수 있음).
		$final_at = (int) $order->get_meta( GOLPE_CUSTOMS_META_FINAL_AT );
		if ( ! golpe_order_has_customs_code( $order ) || ! in_array( $order->get_status(), golpe_customs_final_statuses(), true ) || ! $final_at || $final_at > $cutoff ) {
			continue;
		}
		/* translators: %d: 보관 일수 */
		golpe_delete_customs_code( $order, sprintf( __( '최종 상태 전환 후 보관 기간(%d일) 경과', 'golpe' ), golpe_customs_retention_days() ) );
		$count++;
	}

	golpe_customs_reencrypt_batch( $batch );

	return $count;
}
add_action( GOLPE_CUSTOMS_CRON_HOOK, 'golpe_run_customs_cleanup' );

/**
 * 키 교체 후: 이전 키로 암호화된 번호를 현재 키로 옮깁니다.
 *
 * @param int $batch 한 번에 처리할 주문 수.
 * @return int 옮긴 주문 수.
 */
function golpe_customs_reencrypt_batch( $batch = 50 ) {
	$prefix = 'v2:' . golpe_customs_key_id( golpe_customs_key() ) . ':';
	$orders = wc_get_orders(
		array(
			'limit'      => absint( $batch ),
			'status'     => array_keys( wc_get_order_statuses() ),
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => GOLPE_CUSTOMS_META_ENC,
					'value'   => $prefix,
					'compare' => 'NOT LIKE',
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
		if ( golpe_customs_reencrypt_order( $order ) ) {
			$count++;
		}
	}
	return $count;
}

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
 * 개인정보처리방침 안내 문구 (설정 > 개인정보 > 정책 가이드)
 * ----------------------------------------------------------------------- */

/**
 * 워드프레스 개인정보처리방침 가이드에 GOLPE 안내 문구 초안을 추가합니다.
 * (자동으로 방침 페이지를 수정하지 않습니다. 관리자가 복사해서 사용)
 */
function golpe_add_privacy_policy_content() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}
	$days = golpe_customs_retention_days();
	$html = '<p class="privacy-policy-tutorial">' . esc_html__( '아래는 GOLPE 테마가 처리하는 개인정보에 대한 안내 초안입니다. 실제 운영 내용(위탁 업체, 국외 이전 여부 등)에 맞게 수정하고 전문가 검토를 받으세요.', 'golpe' ) . '</p>'
		. '<h3>' . esc_html__( '개인통관고유부호의 수집 · 이용', 'golpe' ) . '</h3>'
		. '<ul>'
		. '<li><strong>' . esc_html__( '수집 항목', 'golpe' ) . ':</strong> ' . esc_html__( '개인통관고유부호 (주문 시 필수 입력)', 'golpe' ) . '</li>'
		. '<li><strong>' . esc_html__( '수집 목적', 'golpe' ) . ':</strong> ' . esc_html__( '해외구매대행 상품의 수입 통관 (관세법에 따른 수입신고)', 'golpe' ) . '</li>'
		/* translators: %d: 보관 일수 */
		. '<li><strong>' . esc_html__( '보유 기간', 'golpe' ) . ':</strong> ' . esc_html( sprintf( __( '주문이 완료 · 취소 · 환불된 날부터 %d일 후 자동 파기 (관계 법령에 보관 의무가 있으면 그 기간)', 'golpe' ), $days ) ) . '</li>'
		. '<li><strong>' . esc_html__( '제공 · 위탁', 'golpe' ) . ':</strong> ' . esc_html__( '관세청, 통관 대행 업체(관세사), 국제 · 국내 배송 업체 — [실제 업체명 기재]', 'golpe' ) . '</li>'
		. '<li><strong>' . esc_html__( '국외 이전', 'golpe' ) . ':</strong> ' . esc_html__( '주문 정보(수취인 이름 · 주소 · 연락처 등)가 중국 현지 판매처 · 물류 업체로 이전되는 경우 이전 국가, 받는 자, 항목, 목적, 보유 기간을 기재 — [실제 내용 기재]', 'golpe' ) . '</li>'
		. '<li><strong>' . esc_html__( '안전성 확보 조치', 'golpe' ) . ':</strong> ' . esc_html__( '암호화 저장, 열람 권한 제한 및 열람 기록, 화면 표시 시 일부 가림', 'golpe' ) . '</li>'
		. '<li><strong>' . esc_html__( '파기 방법', 'golpe' ) . ':</strong> ' . esc_html__( '보유 기간 경과 시 데이터베이스에서 복구할 수 없도록 삭제', 'golpe' ) . '</li>'
		. '</ul>';

	wp_add_privacy_policy_content( 'GOLPE (해외구매대행)', wp_kses_post( $html ) );
}
add_action( 'admin_init', 'golpe_add_privacy_policy_content' );

/* --------------------------------------------------------------------------
 * 관리자 경고: 키 · 개인정보처리방침
 * ----------------------------------------------------------------------- */

/**
 * 키 · 개인정보처리방침 설정 상태 안내.
 */
function golpe_customs_key_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! golpe_has_dedicated_customs_key() ) {
		echo '<div class="notice notice-warning"><p><strong>GOLPE:</strong> ' . esc_html__( '개인통관고유부호 암호화용 전용 키(GOLPE_CUSTOMS_KEY)가 wp-config.php 에 없어 워드프레스 보안 키로 대신 암호화하고 있습니다. 운영 전 README "6-5" 안내에 따라 전용 키(32자 이상)를 추가하세요. (추가하면 기존 번호는 자동으로 새 키로 옮겨집니다)', 'golpe' ) . '</p></div>';
	} elseif ( golpe_customs_key_is_weak() ) {
		/* translators: %d: 최소 길이 */
		echo '<div class="notice notice-error"><p><strong>GOLPE:</strong> ' . esc_html( sprintf( __( 'GOLPE_CUSTOMS_KEY 가 너무 짧습니다. %d자 이상의 무작위 문자열로 바꾸고, 기존 키는 GOLPE_CUSTOMS_KEY_PREVIOUS 에 남겨 두세요. (README 6-5)', 'golpe' ), GOLPE_CUSTOMS_MIN_KEY_LENGTH ) ) . '</p></div>';
	}

	if ( ! (int) get_option( 'wp_page_for_privacy_policy' ) || ! get_privacy_policy_url() ) {
		echo '<div class="notice notice-warning"><p><strong>GOLPE:</strong> ' . esc_html__( '개인정보처리방침 페이지가 설정되지 않았거나 공개되지 않았습니다. 설정 > 개인정보에서 페이지를 지정하고, 개인통관고유부호 수집 내용을 추가하세요. (정책 가이드에 GOLPE 안내 초안이 있습니다)', 'golpe' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'golpe_customs_key_admin_notice' );
