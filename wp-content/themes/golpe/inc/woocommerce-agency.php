<?php
/**
 * WooCommerce 해외구매대행 기능.
 *
 * 1) 상품 편집 화면: 구매 국가 · 예상 배송기간 · 가격 구성(선택 입력)
 * 2) 상품 상세: 가격 구성표 + 구매대행 유의사항 박스
 * 3) 장바구니: 핵심 조건 요약
 * 4) 결제: 개인통관고유부호 입력(필수) + 구매대행 조건 확인 체크박스(필수)
 * 5) 관리자 주문 화면: 개인통관고유부호 · 조건 확인 시각 표시
 * 6) 결제 페이지가 "블록 결제"이면 관리자에게 경고 (이 기능들은 클래식 결제에서 동작)
 *
 * 결제 금액 계산(가격 · 배송비 · 세금)은 변경하지 않습니다.
 * 확인 체크박스는 "조건을 확인했다"는 의미일 뿐, 법정 청약철회 · 환불 권리를 포기하게 하지 않습니다.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* --------------------------------------------------------------------------
 * 공통 도우미
 * ----------------------------------------------------------------------- */

/**
 * 개인통관고유부호 정리 (공백 · 하이픈 제거, 대문자).
 *
 * @param string $code 입력값.
 * @return string
 */
function golpe_normalize_customs_code( $code ) {
	$code = strtoupper( (string) $code );
	return preg_replace( '/[\s\-]+/', '', $code );
}

/**
 * 개인통관고유부호 형식 확인: P + 숫자 12자리 (총 13자리).
 *
 * @param string $code 정리된 값.
 * @return bool
 */
function golpe_is_valid_customs_code( $code ) {
	return (bool) preg_match( '/^P\d{12}$/', (string) $code );
}

/**
 * 화면 표시용 마스킹 (예: P1234*****789).
 *
 * @param string $code 개인통관고유부호.
 * @return string
 */
function golpe_mask_customs_code( $code ) {
	$code = (string) $code;
	if ( strlen( $code ) < 8 ) {
		return $code;
	}
	return substr( $code, 0, 5 ) . str_repeat( '*', strlen( $code ) - 8 ) . substr( $code, -3 );
}

/**
 * 상품별 구매대행 정보.
 *
 * @param WC_Product $product 상품.
 * @return array
 */
function golpe_product_agency_info( $product ) {
	$country = trim( (string) $product->get_meta( '_golpe_origin_country' ) );
	$period  = trim( (string) $product->get_meta( '_golpe_shipping_period' ) );

	return array(
		'country'  => '' !== $country ? $country : golpe_agency_option( 'agency_country' ),
		'period'   => '' !== $period ? $period : golpe_agency_option( 'agency_shipping_period' ),
		'item'     => $product->get_meta( '_golpe_price_item' ),
		'shipping' => $product->get_meta( '_golpe_price_shipping' ),
		'fee'      => $product->get_meta( '_golpe_price_fee' ),
	);
}

/* --------------------------------------------------------------------------
 * 1) 상품 편집 화면 입력 칸
 * ----------------------------------------------------------------------- */

/**
 * 상품 데이터 > 일반 탭에 구매대행 입력 칸 추가.
 */
function golpe_product_agency_fields() {
	echo '<div class="options_group golpe-agency-fields">';
	echo '<p class="form-field"><strong>' . esc_html__( '해외구매대행 정보 (선택 입력)', 'golpe' ) . '</strong><br><span class="description">' . esc_html__( '비워 두면 사용자 정의하기 > 구매대행 안내의 기본값이 표시됩니다. 가격 구성 3개 항목을 모두 입력하면 상품 상세에 구성표가 표시됩니다.', 'golpe' ) . '</span></p>';

	woocommerce_wp_text_input(
		array(
			'id'          => '_golpe_origin_country',
			'label'       => __( '구매 국가', 'golpe' ),
			'placeholder' => golpe_agency_option( 'agency_country' ),
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'          => '_golpe_shipping_period',
			'label'       => __( '예상 배송기간', 'golpe' ),
			'placeholder' => golpe_agency_option( 'agency_shipping_period' ),
		)
	);

	$price_fields = array(
		'_golpe_price_item'     => __( '현지 상품가 + 현지 배송비 (원)', 'golpe' ),
		'_golpe_price_shipping' => __( '국제배송비 (원)', 'golpe' ),
		'_golpe_price_fee'      => __( '구매대행 수수료 (원)', 'golpe' ),
	);
	foreach ( $price_fields as $id => $label ) {
		woocommerce_wp_text_input(
			array(
				'id'                => $id,
				'label'             => $label,
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '1',
				),
			)
		);
	}
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'golpe_product_agency_fields' );

/**
 * 입력값 저장.
 *
 * @param WC_Product $product 상품.
 */
function golpe_save_product_agency_fields( $product ) {
	// WooCommerce 가 상품 저장 시 nonce 를 이미 확인합니다.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	foreach ( array( '_golpe_origin_country', '_golpe_shipping_period' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$product->update_meta_data( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	foreach ( array( '_golpe_price_item', '_golpe_price_shipping', '_golpe_price_fee' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$value = trim( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			$product->update_meta_data( $key, '' === $value ? '' : (string) absint( $value ) );
		}
	}
	// phpcs:enable
}
add_action( 'woocommerce_admin_process_product_object', 'golpe_save_product_agency_fields' );

/* --------------------------------------------------------------------------
 * 2) 상품 상세: 가격 구성표 + 유의사항
 * ----------------------------------------------------------------------- */

/**
 * 상품 상세 유의사항 박스 (장바구니 버튼 아래).
 */
function golpe_single_product_agency_notice() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$info      = golpe_product_agency_info( $product );
	$has_split = '' !== (string) $info['item'] && '' !== (string) $info['shipping'] && '' !== (string) $info['fee'];
	?>
	<aside class="agency-notice" aria-label="<?php esc_attr_e( '해외구매대행 상품 유의사항', 'golpe' ); ?>">
		<p class="agency-notice__badge"><?php esc_html_e( '해외구매대행 상품', 'golpe' ); ?></p>

		<dl class="agency-notice__meta">
			<div><dt><?php esc_html_e( '구매 국가', 'golpe' ); ?></dt><dd><?php echo esc_html( $info['country'] ); ?></dd></div>
			<div><dt><?php esc_html_e( '예상 배송', 'golpe' ); ?></dt><dd><?php echo esc_html( $info['period'] ); ?></dd></div>
			<div><dt><?php esc_html_e( '관부가세', 'golpe' ); ?></dt><dd>
				<?php
				/* translators: %s: 면세 한도 */
				printf( esc_html__( '%s 초과 시 고객 부담', 'golpe' ), esc_html( golpe_agency_duty_free_limit() ) );
				?>
			</dd></div>
		</dl>

		<?php if ( $has_split ) : ?>
			<table class="agency-table agency-table--compact">
				<caption><?php esc_html_e( '판매가 구성', 'golpe' ); ?></caption>
				<tbody>
					<tr><th scope="row"><?php esc_html_e( '현지 상품가 + 현지 배송비', 'golpe' ); ?></th><td><?php echo wp_kses_post( wc_price( (float) $info['item'] ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( '국제배송비', 'golpe' ); ?></th><td><?php echo wp_kses_post( wc_price( (float) $info['shipping'] ) ); ?></td></tr>
					<tr><th scope="row"><?php esc_html_e( '구매대행 수수료', 'golpe' ); ?></th><td><?php echo wp_kses_post( wc_price( (float) $info['fee'] ) ); ?></td></tr>
					<tr class="agency-table__total"><th scope="row"><?php esc_html_e( '합계 (판매가)', 'golpe' ); ?></th><td><?php echo wp_kses_post( wc_price( (float) $info['item'] + (float) $info['shipping'] + (float) $info['fee'] ) ); ?></td></tr>
				</tbody>
			</table>
			<?php
			$sum   = (float) $info['item'] + (float) $info['shipping'] + (float) $info['fee'];
			$price = (float) $product->get_price();
			if ( current_user_can( 'edit_products' ) && $price > 0 && abs( $sum - $price ) >= 1 ) :
				?>
				<p class="admin-note"><?php esc_html_e( '[관리자에게만 보임] 가격 구성 합계가 현재 판매가와 다릅니다. 상품 편집 화면에서 금액을 확인해 주세요.', 'golpe' ); ?></p>
			<?php endif; ?>
		<?php else : ?>
			<p class="agency-notice__price"><?php echo esc_html( golpe_agency_key_terms()['price'] ); ?></p>
		<?php endif; ?>

		<details class="agency-notice__details">
			<summary><?php esc_html_e( '구매대행 유의사항 · 반품 안내', 'golpe' ); ?></summary>
			<?php golpe_agency_terms_list( array( 'agency', 'shipping', 'duty', 'customs', 'refund', 'defect' ) ); ?>
			<?php golpe_agency_guide_link(); ?>
		</details>
	</aside>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'golpe_single_product_agency_notice', 35 );

/* --------------------------------------------------------------------------
 * 3) 장바구니: 핵심 조건 요약
 * ----------------------------------------------------------------------- */

/**
 * 장바구니 합계 위 안내.
 */
function golpe_cart_agency_notice() {
	?>
	<div class="agency-notice agency-notice--cart">
		<p class="agency-notice__badge"><?php esc_html_e( '해외구매대행 주문 안내', 'golpe' ); ?></p>
		<?php golpe_agency_terms_list( array( 'shipping', 'duty', 'customs', 'refund' ) ); ?>
		<?php golpe_agency_guide_link(); ?>
	</div>
	<?php
}
add_action( 'woocommerce_before_cart_collaterals', 'golpe_cart_agency_notice' );

/* --------------------------------------------------------------------------
 * 4) 결제: 개인통관고유부호 + 조건 확인
 * ----------------------------------------------------------------------- */

/**
 * 개인통관고유부호 입력 칸 (배송 정보 아래).
 *
 * @param WC_Checkout $checkout 결제 객체.
 */
function golpe_checkout_customs_field( $checkout ) {
	echo '<div class="golpe-customs-field">';
	echo '<h3>' . esc_html__( '개인통관고유부호', 'golpe' ) . '</h3>';
	woocommerce_form_field(
		'golpe_customs_code',
		array(
			'type'              => 'text',
			'required'          => true,
			'label'             => __( '개인통관고유부호 (수취인 본인)', 'golpe' ),
			'placeholder'       => 'P000000000000',
			'maxlength'         => 15,
			'autocomplete'      => 'off',
			'description'       => __( 'P로 시작하는 13자리입니다. 관세청 유니패스에서 발급받을 수 있으며, 수입 통관 목적으로만 사용됩니다. 수취인 이름 · 전화번호가 등록 정보와 같아야 통관이 지연되지 않습니다.', 'golpe' ),
			'custom_attributes' => array(
				'inputmode' => 'text',
				'pattern'   => '[Pp][0-9 \-]{12,14}',
			),
		),
		$checkout->get_value( 'golpe_customs_code' )
	);
	echo '</div>';
}
add_action( 'woocommerce_after_order_notes', 'golpe_checkout_customs_field' );

/**
 * 결제 버튼 바로 위: 핵심 조건 요약 + 확인 체크박스.
 */
function golpe_checkout_terms_box() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- 화면 재표시용 값.
	$checked = ! empty( $_POST['golpe_agency_confirm'] );
	?>
	<div class="agency-notice agency-notice--checkout">
		<p class="agency-notice__badge"><?php esc_html_e( '해외구매대행 핵심 조건', 'golpe' ); ?></p>
		<?php golpe_agency_terms_list(); ?>
		<?php golpe_agency_guide_link(); ?>
		<p class="form-row validate-required agency-confirm">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="golpe_agency_confirm" id="golpe_agency_confirm" value="1" <?php checked( $checked ); ?> />
				<span><?php esc_html_e( '위 해외구매대행 이용 조건을 확인했습니다.', 'golpe' ); ?></span>&nbsp;<abbr class="required" title="<?php esc_attr_e( '필수', 'golpe' ); ?>">*</abbr>
			</label>
			<span class="agency-confirm__help"><?php esc_html_e( '이 확인은 조건 안내를 위한 것이며, 법에서 보장하는 청약철회 · 환불 권리에는 영향을 주지 않습니다.', 'golpe' ); ?></span>
		</p>
	</div>
	<?php
}
add_action( 'woocommerce_review_order_before_submit', 'golpe_checkout_terms_box' );

/**
 * 결제 입력값 확인.
 */
function golpe_checkout_validate() {
	// WooCommerce 가 결제 처리 전에 nonce 를 확인합니다.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$code = isset( $_POST['golpe_customs_code'] ) ? golpe_normalize_customs_code( sanitize_text_field( wp_unslash( $_POST['golpe_customs_code'] ) ) ) : '';

	if ( '' === $code ) {
		wc_add_notice( __( '개인통관고유부호를 입력해 주세요.', 'golpe' ), 'error', array( 'id' => 'golpe_customs_code' ) );
	} elseif ( ! golpe_is_valid_customs_code( $code ) ) {
		wc_add_notice( __( '개인통관고유부호 형식이 올바르지 않습니다. P로 시작하는 13자리(P + 숫자 12자리)를 입력해 주세요.', 'golpe' ), 'error', array( 'id' => 'golpe_customs_code' ) );
	}

	if ( empty( $_POST['golpe_agency_confirm'] ) ) {
		wc_add_notice( __( '해외구매대행 이용 조건 확인에 체크해 주세요.', 'golpe' ), 'error', array( 'id' => 'golpe_agency_confirm' ) );
	}
	// phpcs:enable
}
add_action( 'woocommerce_checkout_process', 'golpe_checkout_validate' );

/**
 * 주문에 저장 (HPOS 호환: 주문 객체 메타 사용).
 *
 * @param WC_Order $order 주문.
 */
function golpe_checkout_save( $order ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( isset( $_POST['golpe_customs_code'] ) ) {
		$code = golpe_normalize_customs_code( sanitize_text_field( wp_unslash( $_POST['golpe_customs_code'] ) ) );
		if ( golpe_is_valid_customs_code( $code ) ) {
			$order->update_meta_data( '_golpe_customs_code', $code );
		}
	}
	if ( ! empty( $_POST['golpe_agency_confirm'] ) ) {
		$order->update_meta_data( '_golpe_agency_confirmed_at', gmdate( 'Y-m-d H:i:s' ) );
	}
	// phpcs:enable
}
add_action( 'woocommerce_checkout_create_order', 'golpe_checkout_save' );

/* --------------------------------------------------------------------------
 * 5) 관리자 주문 화면 표시
 * ----------------------------------------------------------------------- */

/**
 * 주문 상세(관리자) 배송지 아래에 표시.
 *
 * @param WC_Order $order 주문.
 */
function golpe_admin_order_agency_info( $order ) {
	$code      = $order->get_meta( '_golpe_customs_code' );
	$confirmed = $order->get_meta( '_golpe_agency_confirmed_at' );
	if ( ! $code && ! $confirmed ) {
		return;
	}
	echo '<div class="golpe-admin-agency"><h3>' . esc_html__( '해외구매대행', 'golpe' ) . '</h3>';
	if ( $code ) {
		echo '<p><strong>' . esc_html__( '개인통관고유부호', 'golpe' ) . ':</strong> ' . esc_html( $code ) . '</p>';
	}
	if ( $confirmed ) {
		echo '<p><strong>' . esc_html__( '조건 확인 (UTC)', 'golpe' ) . ':</strong> ' . esc_html( $confirmed ) . '</p>';
	}
	echo '</div>';
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'golpe_admin_order_agency_info' );

/**
 * 고객 주문 상세 · 주문 완료 화면: 마스킹해서 표시.
 *
 * @param WC_Order $order 주문.
 */
function golpe_customer_order_agency_info( $order ) {
	$code = $order->get_meta( '_golpe_customs_code' );
	if ( ! $code ) {
		return;
	}
	echo '<p class="golpe-order-customs"><strong>' . esc_html__( '개인통관고유부호', 'golpe' ) . ':</strong> ' . esc_html( golpe_mask_customs_code( $code ) ) . '</p>';
}
add_action( 'woocommerce_order_details_after_customer_details', 'golpe_customer_order_agency_info' );

/* --------------------------------------------------------------------------
 * 6) 블록 결제 사용 시 관리자 경고
 * ----------------------------------------------------------------------- */

/**
 * 결제 페이지가 블록 방식이면 경고 표시.
 */
function golpe_block_checkout_admin_notice() {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wc_get_page_id' ) ) {
		return;
	}
	$checkout_id = wc_get_page_id( 'checkout' );
	if ( $checkout_id <= 0 || ! has_block( 'woocommerce/checkout', $checkout_id ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>GOLPE:</strong> ' . esc_html__( '현재 결제 페이지가 "블록 결제" 방식입니다. 개인통관고유부호 입력과 구매대행 조건 확인은 "클래식 결제"에서만 동작합니다. 결제 페이지 내용을 [woocommerce_checkout] 숏코드로 바꿔 주세요. (README 참고)', 'golpe' ) . '</p></div>';
}
add_action( 'admin_notices', 'golpe_block_checkout_admin_notice' );
