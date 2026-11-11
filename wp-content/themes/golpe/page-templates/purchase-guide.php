<?php
/**
 * Template Name: 구매대행 이용 안내
 *
 * 사용 방법: 관리자 > 페이지 > 새 페이지 추가 > 오른쪽 "템플릿"에서 '구매대행 이용 안내' 선택.
 * 페이지 본문에 입력한 내용은 맨 위에 먼저 표시되고, 그 아래에 아래 안내 섹션이 자동으로 표시됩니다.
 *
 * ⚠️ 법적 안내 문구는 초안입니다. 오픈 전 전문가 또는 공정거래위원회 기준으로 최종 검토하세요.
 *
 * @package GOLPE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$golpe_country  = golpe_agency_option( 'agency_country' );
$golpe_period   = golpe_agency_option( 'agency_shipping_period' );
$golpe_limit    = golpe_agency_duty_free_limit();
$golpe_cs_hours = trim( (string) golpe_agency_option( 'agency_cs_hours' ) );

$golpe_sections = array(
	'about'   => __( '해외구매대행이란', 'golpe' ),
	'process' => __( '이용 절차', 'golpe' ),
	'price'   => __( '가격 구성', 'golpe' ),
	'duty'    => __( '관부가세', 'golpe' ),
	'customs' => __( '개인통관고유부호', 'golpe' ),
	'ship'    => __( '배송 기간', 'golpe' ),
	'refund'  => __( '취소 · 반품 · 환불', 'golpe' ),
	'etc'     => __( '기타 유의사항', 'golpe' ),
);

get_header();
?>
<div class="container page-container agency-guide">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<header class="page-header agency-guide__header">
			<p class="section-eyebrow">PURCHASE AGENCY GUIDE</p>
			<?php the_title( '<h1 class="page-title">', '</h1>' ); ?>
		</header>

		<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
			<p class="admin-note agency-guide__admin">
				<?php esc_html_e( '[관리자에게만 보임] 이 안내는 초안입니다. 배송기간 · 반품 비용 · 운영시간은 "사용자 정의하기 > GOLPE 테마 설정 > 구매대행 안내"에서 입력하고, 오픈 전 법률 검토를 받아 주세요.', 'golpe' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( '' !== trim( get_the_content() ) ) : ?>
			<div class="entry-content agency-guide__intro"><?php the_content(); ?></div>
		<?php endif; ?>
	<?php endwhile; ?>

	<div class="agency-notice agency-guide__summary">
		<p class="agency-notice__badge"><?php esc_html_e( '한눈에 보기', 'golpe' ); ?></p>
		<?php golpe_agency_checkout_terms_list(); ?>
	</div>

	<div class="agency-guide__layout">
		<nav class="agency-guide__toc" aria-label="<?php esc_attr_e( '안내 목차', 'golpe' ); ?>" data-golpe-guide-toc>
			<ol>
				<?php foreach ( $golpe_sections as $golpe_id => $golpe_label ) : ?>
					<li><a href="#guide-<?php echo esc_attr( $golpe_id ); ?>"><?php echo esc_html( $golpe_label ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</nav>

		<div class="agency-guide__body entry-content">
			<section id="guide-about" class="agency-guide__section">
				<h2><?php echo esc_html( $golpe_sections['about'] ); ?></h2>
				<p>
					<?php
					/* translators: %s: 구매 국가 */
					printf( esc_html__( 'GOLPE는 %s 현지 쇼핑몰 · 판매처에서 판매되는 골프용품을 고객님의 주문에 따라 대신 구매하고, 국제배송과 통관을 거쳐 국내로 배송해 드리는 해외구매대행 서비스입니다.', 'golpe' ), esc_html( $golpe_country ) );
					?>
				</p>
				<p><?php esc_html_e( '상품은 고객님 명의로 수입 통관되며, GOLPE는 국내에 재고를 보관하지 않고 주문을 받은 뒤 현지에서 구매합니다.', 'golpe' ); ?></p>
			</section>

			<section id="guide-process" class="agency-guide__section">
				<h2><?php echo esc_html( $golpe_sections['process'] ); ?></h2>
				<ol class="agency-guide__steps">
					<li><strong><?php esc_html_e( '주문 · 결제', 'golpe' ); ?></strong> — <?php esc_html_e( '상품 선택 후 개인통관고유부호를 입력하고 결제합니다.', 'golpe' ); ?></li>
					<li><strong><?php esc_html_e( '현지 구매', 'golpe' ); ?></strong> — <?php esc_html_e( '결제 확인 후 현지 판매처에 구매를 진행합니다. 현지 품절 시 즉시 연락드리고 전액 환불합니다.', 'golpe' ); ?></li>
					<li><strong><?php esc_html_e( '국제배송', 'golpe' ); ?></strong> — <?php esc_html_e( '현지 판매처에서 발송된 상품이 국제배송으로 한국에 도착합니다.', 'golpe' ); ?></li>
					<li><strong><?php esc_html_e( '통관', 'golpe' ); ?></strong> — <?php esc_html_e( '고객님 명의로 수입 통관이 진행됩니다.', 'golpe' ); ?></li>
					<li><strong><?php esc_html_e( '국내 배송', 'golpe' ); ?></strong> — <?php esc_html_e( '통관 완료 후 국내 택배로 배송되며, 운송장 번호를 안내해 드립니다.', 'golpe' ); ?></li>
				</ol>
			</section>

			<section id="guide-price" class="agency-guide__section">
				<h2><?php echo esc_html( $golpe_sections['price'] ); ?></h2>
				<p><?php esc_html_e( '상품 페이지에 표시된 판매가에는 아래 비용이 모두 포함되어 있습니다. 결제 후 추가로 청구되는 배송비나 수수료는 없습니다. (단, 관부가세는 아래 안내에 따릅니다.)', 'golpe' ); ?></p>
				<table class="agency-table agency-table--stack">
					<thead>
						<tr><th scope="col"><?php esc_html_e( '항목', 'golpe' ); ?></th><th scope="col"><?php esc_html_e( '내용', 'golpe' ); ?></th><th scope="col"><?php esc_html_e( '판매가 포함', 'golpe' ); ?></th></tr>
					</thead>
					<tbody>
						<tr><td data-label="<?php esc_attr_e( '항목', 'golpe' ); ?>"><?php esc_html_e( '현지 상품가', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '내용', 'golpe' ); ?>"><?php esc_html_e( '현지 판매처의 상품 가격', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '판매가 포함', 'golpe' ); ?>"><?php esc_html_e( '포함', 'golpe' ); ?></td></tr>
						<tr><td data-label="<?php esc_attr_e( '항목', 'golpe' ); ?>"><?php esc_html_e( '현지 배송비', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '내용', 'golpe' ); ?>"><?php esc_html_e( '현지 판매처 → 국제 발송지', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '판매가 포함', 'golpe' ); ?>"><?php esc_html_e( '포함', 'golpe' ); ?></td></tr>
						<tr><td data-label="<?php esc_attr_e( '항목', 'golpe' ); ?>"><?php esc_html_e( '국제배송비', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '내용', 'golpe' ); ?>"><?php esc_html_e( '해외 → 한국 (국내 택배 포함)', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '판매가 포함', 'golpe' ); ?>"><?php esc_html_e( '포함', 'golpe' ); ?></td></tr>
						<tr><td data-label="<?php esc_attr_e( '항목', 'golpe' ); ?>"><?php esc_html_e( '구매대행 수수료', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '내용', 'golpe' ); ?>"><?php esc_html_e( '구매 · 통관 진행 등 대행 서비스 비용', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '판매가 포함', 'golpe' ); ?>"><?php esc_html_e( '포함', 'golpe' ); ?></td></tr>
						<tr><td data-label="<?php esc_attr_e( '항목', 'golpe' ); ?>"><?php esc_html_e( '관부가세', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '내용', 'golpe' ); ?>"><?php esc_html_e( '면세 한도 초과 시 부과', 'golpe' ); ?></td><td data-label="<?php esc_attr_e( '판매가 포함', 'golpe' ); ?>"><?php esc_html_e( '별도 (고객 부담)', 'golpe' ); ?></td></tr>
					</tbody>
				</table>
				<p class="agency-guide__small"><?php esc_html_e( '상품별 가격 구성은 각 상품 상세 페이지에서 확인하실 수 있습니다.', 'golpe' ); ?></p>
			</section>

			<section id="guide-duty" class="agency-guide__section">
				<h2><?php echo esc_html( $golpe_sections['duty'] ); ?></h2>
				<ul>
					<li>
						<?php
						/* translators: %s: 면세 한도 */
						printf( esc_html__( '개인이 사용할 목적으로 들여오는 물품은 물품가격이 %s 이하(미국 외 국가 발송 기준)이면 일반적으로 관세 · 부가세가 면제됩니다.', 'golpe' ), esc_html( $golpe_limit ) );
						?>
					</li>
					<li><?php esc_html_e( '면세 한도를 넘으면 물품 전체 가격에 대해 관세 · 부가세가 부과될 수 있으며, 이 세금은 고객님 부담입니다. 부과 시 납부 방법을 별도로 안내해 드립니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '같은 날 여러 건을 주문해 함께 통관되는 경우 금액이 합산되어 과세될 수 있습니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '면세 기준과 통관 규정은 관세청 정책에 따라 바뀔 수 있습니다. 정확한 기준은 관세청 안내를 확인해 주세요.', 'golpe' ); ?></li>
				</ul>
			</section>

			<section id="guide-customs" class="agency-guide__section">
				<h2><?php echo esc_html( $golpe_sections['customs'] ); ?></h2>
				<ul>
					<li><?php esc_html_e( '해외구매대행 상품은 고객님 명의로 통관되므로, 주문 시 수취인 본인의 개인통관고유부호(P로 시작하는 13자리)를 입력해 주셔야 합니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '개인통관고유부호는 관세청 유니패스에서 발급받을 수 있으며, 유효기간이 있으므로 만료 여부를 확인해 주세요.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '수취인 이름 · 전화번호 · 주소가 개인통관고유부호 등록 정보와 다르면 통관이 지연될 수 있습니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '입력하신 개인통관고유부호는 수입 통관 목적으로만 사용합니다.', 'golpe' ); ?></li>
				</ul>
			</section>

			<section id="guide-ship" class="agency-guide__section">
				<h2><?php echo esc_html( $golpe_sections['ship'] ); ?></h2>
				<ul>
					<li>
						<?php
						/* translators: %s: 배송 기간 */
						printf( esc_html__( '예상 배송기간: %s', 'golpe' ), esc_html( $golpe_period ) );
						?>
					</li>
					<li><?php esc_html_e( '현지 판매처 발송 지연, 현지 공휴일, 국제 물류 상황, 세관 검사 등에 따라 늦어질 수 있습니다. 지연이 예상되면 먼저 연락드립니다.', 'golpe' ); ?></li>
				</ul>
			</section>

			<section id="guide-refund" class="agency-guide__section agency-guide__section--emphasis">
				<h2><?php echo esc_html( $golpe_sections['refund'] ); ?></h2>
				<p><strong><?php esc_html_e( '해외구매대행 상품도 국내 전자상거래법이 적용되어, 다른 온라인 쇼핑몰과 동일하게 청약철회(반품) · 환불을 받으실 수 있습니다.', 'golpe' ); ?></strong></p>
				<p class="agency-guide__principle"><?php esc_html_e( '상품 하자 · 오배송 · 현지 품절 · GOLPE 사정에 의한 발송 지연 등 GOLPE의 책임 있는 사유로 취소 · 반품하는 경우, 반송비를 포함한 모든 비용은 GOLPE가 부담하며 결제 금액 전액을 환불합니다.', 'golpe' ); ?></p>
				<h3><?php esc_html_e( '① 상품을 받기 전 — 주문 취소', 'golpe' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '결제 후 상품을 받기 전에도 언제든 주문 취소(청약철회)를 신청하실 수 있습니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '현지 구매 전 취소: 결제 금액 전액을 환불합니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '현지 구매 · 발송 후 단순변심 취소: 상품을 되돌려 보내는 데 실제로 드는 비용(주문 전 안내한 금액 또는 산정 기준 범위)만 빼고 환불합니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '현지 품절, GOLPE 사정에 의한 발송 지연 등 GOLPE의 책임 있는 사유로 취소하는 경우에는 어떤 비용도 빼지 않고 전액 환불합니다.', 'golpe' ); ?></li>
				</ul>

				<h3><?php esc_html_e( '② 상품을 받은 후 — 단순변심 반품', 'golpe' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '상품을 받은 날부터 7일 이내에 신청하시면 됩니다. 반품 상품이 7일 이후에 도착하더라도 기간 안에 신청하셨다면 반품이 가능합니다.', 'golpe' ); ?></li>
					<li><?php echo esc_html( golpe_agency_return_cost_text() ); ?></li>
					<li><?php esc_html_e( '반품 비용 외에 위약금 · 창고료 · 인건비 · 물류비 등 다른 비용은 청구하지 않습니다.', 'golpe' ); ?></li>
				</ul>

				<h3><?php esc_html_e( '③ 상품 하자 · 오배송 등', 'golpe' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '상품이 표시 · 광고 내용과 다르거나 계약 내용과 다르게 이행된 경우(하자 · 오배송 등)에는 상품을 받은 날부터 3개월 이내, 그 사실을 안 날 또는 알 수 있었던 날부터 30일 이내에 청약철회하실 수 있습니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '이 경우 반품 비용(국제 반송비 포함)은 전부 GOLPE가 부담합니다.', 'golpe' ); ?></li>
				</ul>

				<h3><?php esc_html_e( '④ 환불 시기', 'golpe' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '상품을 받기 전 취소: 취소를 신청하신 날부터 3영업일 이내에 환불합니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '반품: 반품 상품을 돌려받은 날부터 3영업일 이내에 환불합니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '신용카드 등으로 결제하신 경우 결제업자에게 지체 없이 결제 취소를 요청합니다. 실제 반영 시점은 카드사마다 다를 수 있습니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '환불 금액에서 빼는 비용은 단순변심 취소 · 반품의 반송 실비뿐이며, GOLPE의 책임 있는 사유인 경우에는 빼는 비용 없이 전액 환불합니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '3영업일 기한을 넘겨 환불하는 경우, 늦어진 기간에 대해 전자상거래법에서 정한 지연이자를 함께 지급합니다.', 'golpe' ); ?></li>
				</ul>

				<h3><?php esc_html_e( '⑤ 관부가세 환급', 'golpe' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '관부가세를 이미 납부한 상품을 반품하는 경우, 관세청에 환급을 신청할 수 있습니다. 필요한 서류와 절차를 안내해 드립니다.', 'golpe' ); ?></li>
				</ul>

				<h3><?php esc_html_e( '⑥ 신청 방법', 'golpe' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '주문 취소 · 반품은 고객센터로 주문번호와 함께 신청해 주세요. 반품 주소와 방법을 안내해 드립니다.', 'golpe' ); ?></li>
				</ul>

				<h3><?php esc_html_e( '⑦ 청약철회가 제한될 수 있는 경우', 'golpe' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '고객님의 책임 있는 사유로 상품이 멸실 · 훼손된 경우 (상품 내용을 확인하기 위해 포장을 뜯은 것은 해당하지 않습니다)', 'golpe' ); ?></li>
					<li><?php esc_html_e( '고객님의 사용 또는 일부 소비로 상품 가치가 현저히 줄어든 경우', 'golpe' ); ?></li>
					<li><?php esc_html_e( '시간이 지나 다시 판매하기 어려울 정도로 상품 가치가 현저히 줄어든 경우', 'golpe' ); ?></li>
					<li><?php esc_html_e( '단, 위 두 번째 · 세 번째 경우라도 GOLPE가 그 사실을 상품 페이지 등에 미리 명확하게 표시하는 등 법에서 정한 조치를 하지 않았다면 청약철회가 제한되지 않습니다.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '상품이 표시 · 광고 내용과 다르거나 계약 내용과 다르게 이행된 경우(③)에는 위 사유와 관계없이 청약철회하실 수 있습니다.', 'golpe' ); ?></li>
				</ul>
			</section>

			<section id="guide-etc" class="agency-guide__section">
				<h2><?php echo esc_html( $golpe_sections['etc'] ); ?></h2>
				<ul>
					<li><?php esc_html_e( '해외 판매 상품은 국내 공식 A/S가 제공되지 않을 수 있습니다. 상품 문의는 GOLPE 고객센터로 연락해 주세요.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '의류 · 신발 사이즈 표기는 국가 · 브랜드마다 다를 수 있으니 상품 상세의 실측 사이즈를 확인해 주세요.', 'golpe' ); ?></li>
					<li><?php esc_html_e( '모니터 환경에 따라 실제 색상과 차이가 있을 수 있습니다.', 'golpe' ); ?></li>
					<?php if ( '' !== $golpe_cs_hours ) : ?>
						<li>
							<?php
							/* translators: %s: 운영 시간 */
							printf( esc_html__( '고객센터 운영시간: %s', 'golpe' ), esc_html( $golpe_cs_hours ) );
							?>
						</li>
					<?php endif; ?>
				</ul>
			</section>
		</div>
	</div>
</div>
<?php
get_footer();
