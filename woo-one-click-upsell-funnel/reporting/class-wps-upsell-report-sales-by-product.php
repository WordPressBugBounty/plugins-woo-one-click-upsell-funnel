<?php
/**
 * Upsell Sales by Product Report.
 *
 * @link       https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-org-backend&utm_campaign=official
 * @since      3.0.0
 *
 * @package    woo_one_click_upsell_funnel
 * @subpackage woo_one_click_upsell_funnel/reporting
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly.
}

if ( class_exists( 'WPS_Upsell_Report_Sales_By_Product' ) ) {
	return;
}

/**
 * WPS_Upsell_Report_Sales_By_Product.
 */
class WPS_Upsell_Report_Sales_By_Product extends WC_Admin_Report {

	/**
	 * Chart colors.
	 *
	 * @var array
	 */
	public $chart_colours = array();

	/**
	 * Product ids.
	 *
	 * @var array
	 */
	public $product_ids = array();

	/**
	 * Product ids with titles.
	 *
	 * @var array
	 */
	public $product_ids_titles = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		// @codingStandardsIgnoreStart
		if ( isset( $_GET['product_ids'] ) && is_array( $_GET['product_ids'] ) ) {
			$data = array_map( 'absint', $_GET['product_ids'] );
			$this->product_ids = array_filter( $data );
		} elseif ( isset( $_GET['product_ids'] ) ) {
			$this->product_ids = array_filter( array( absint( $_GET['product_ids'] ) ) );
		}
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Get the legend for the main chart sidebar.
	 *
	 * @return array
	 */
	public function get_chart_legend() {

		if ( empty( $this->product_ids ) ) {
			return array();
		}

		$legend = array();

		$total_sales = $this->get_order_report_data(
			array(
				'data'         => array(
					'_line_total'            => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_amount',
					),
					'wps_wocuf_upsell_order' => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'wps_wocuf_pro_upsell_meta',
					),
					'is_upsell_purchase'     => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => '',
						'name'            => 'wps_wocuf_pro_upsell_item_meta',
					),
				),
				'where_meta'   => array(
					'relation' => 'AND',
					array(
						'type'       => 'order_item_meta',
						'meta_key'   => array( '_product_id', '_variation_id' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => $this->product_ids, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'operator'   => 'IN',
					),
				),
				'query_type'   => 'get_var',
				'filter_range' => true,
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'nocache'      => true, // Using these as it was not updating latest orders data.
			)
		);

		$total_items = absint(
			$this->get_order_report_data(
				array(
					'data'         => array(
						'_qty'                   => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => 'SUM',
							'name'            => 'order_item_count',
						),
						'wps_wocuf_upsell_order' => array(
							'type'     => 'meta',
							'function' => '',
							'name'     => 'wps_wocuf_pro_upsell_meta',
						),
						'is_upsell_purchase'     => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => '',
							'name'            => 'wps_wocuf_pro_upsell_item_meta',
						),
					),
					'where_meta'   => array(
						'relation' => 'AND',
						array(
							'type'       => 'order_item_meta',
							'meta_key'   => array( '_product_id', '_variation_id' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
							'meta_value' => $this->product_ids, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
							'operator'   => 'IN',
						),
					),
					'query_type'   => 'get_var',
					'filter_range' => true,
					'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
					'nocache'      => true, // Using these as it was not updating latest orders data.
				)
			)
		);

		$legend[] = array(
			/* translators: %s: total items sold */
			'title'            => sprintf( __( '%s sales for the selected items', 'woo-one-click-upsell-funnel' ), '<strong>' . wc_price( $total_sales ) . '</strong>' ),
			'color'            => $this->chart_colours['sales_amount'],
			'highlight_series' => 1,
		);

		$legend[] = array(
			/* translators: %s: total items purchased */
			'title'            => sprintf( __( '%s purchases for the selected items', 'woo-one-click-upsell-funnel' ), '<strong>' . ( $total_items ) . '</strong>' ),
			'color'            => $this->chart_colours['item_count'],
			'highlight_series' => 0,
		);

		return $legend;
	}

	/**
	 * Output the report.
	 */
	public function output_report() {

		$ranges = array(
			'year'       => __( 'Year', 'woo-one-click-upsell-funnel' ),
			'last_month' => __( 'Last month', 'woo-one-click-upsell-funnel' ),
			'month'      => __( 'This month', 'woo-one-click-upsell-funnel' ),
			'7day'       => __( 'Last 7 days', 'woo-one-click-upsell-funnel' ),
		);

		$this->chart_colours = array(
			'sales_amount' => '#8eba36',
			'item_count'   => '#dbe1e3',
		);
		$secure_nonce        = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified   = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'woo-one-click-upsell-funnel' ) );
		}
		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '7day'; //phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ), true ) ) {
			$current_range = '7day';
		}

		$this->check_current_range_nonce( $current_range );
		$this->calculate_current_range( $current_range );

		include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
	}

	/**
	 * Get chart widgets.
	 *
	 * @return array
	 */
	public function get_chart_widgets() {

		$widgets = array();

		if ( ! empty( $this->product_ids ) ) {
			$widgets[] = array(
				'title'    => __( 'Showing reports for:', 'woo-one-click-upsell-funnel' ),
				'callback' => array( $this, 'current_filters' ),
			);
		}

		$widgets[] = array(
			'title'    => '',
			'callback' => array( $this, 'products_widget' ),
		);

		return $widgets;
	}

	/**
	 * Output current filters.
	 */
	public function current_filters() {

		$this->product_ids_titles = array();

		foreach ( $this->product_ids as $product_id ) {

			$product = wc_get_product( $product_id );

			if ( $product ) {
				$this->product_ids_titles[] = $product->get_formatted_name();
			} else {
				$this->product_ids_titles[] = '#' . $product_id;
			}
		}

		echo '<p><strong>' . wp_kses_post( implode( ', ', $this->product_ids_titles ) ) . '</strong></p>';
		echo '<p><a class="button" href="' . esc_url( remove_query_arg( 'product_ids' ) ) . '">' . esc_html__( 'Reset', 'woo-one-click-upsell-funnel' ) . '</a></p>';
	}

	/**
	 * Output products widget.
	 */
	public function products_widget() {
		?>
	<h4 class="section_title"><span><?php esc_html_e( 'Product search', 'woo-one-click-upsell-funnel' ); ?></span></h4>
	<div class="section">
		<form method="GET">
		<div>
		  <?php // @codingStandardsIgnoreStart ?>
			<select class="wc-product-search" style="width:203px;" multiple="multiple" id="product_ids" name="product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woo-one-click-upsell-funnel' ); ?>" data-action="woocommerce_json_search_products_and_variations"></select>
			<button type="submit" class="submit button" value="<?php esc_attr_e( 'Show', 'woo-one-click-upsell-funnel' ); ?>"><?php esc_html_e( 'Show', 'woo-one-click-upsell-funnel' ); ?></button>
			<input type="hidden" name="range" value="<?php echo ( ! empty( $_GET['range'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['range'] ) ) ) : ''; ?>" />
			<input type="hidden" name="start_date" value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) ) : ''; ?>" />
			<input type="hidden" name="end_date" value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) ) : ''; ?>" />
			<input type="hidden" name="page" value="<?php echo ( ! empty( $_GET['page'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : ''; ?>" />
			<input type="hidden" name="tab" value="<?php echo ( ! empty( $_GET['tab'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : ''; ?>" />
			<input type="hidden" name="report" value="<?php echo ( ! empty( $_GET['report'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['report'] ) ) ) : ''; ?>" />
		  <?php wp_nonce_field( 'custom_range', 'wc_reports_nonce', false ); ?>
		  <?php // @codingStandardsIgnoreEnd ?>
		</div>
		</form>
	</div>
	<h4 class="section_title"><span><?php esc_html_e( 'Top sellers', 'woo-one-click-upsell-funnel' ); ?></span></h4>
	<div class="section">
	<table cellspacing="0">
		<?php
		$top_sellers = $this->get_order_report_data(
			array(
				'data'         => array(
					'_product_id' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => '',
						'name'            => 'product_id',
					),
					'_qty'        => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_qty',
					),
				),
				'where_meta'   => array(
					'relation' => 'OR',
					array(
						'type'       => 'order_item_meta',
						'meta_key'   => 'is_upsell_purchase', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => 'true', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'operator'   => 'IN',
					),
				),
				'order_by'     => 'order_item_qty DESC',
				'group_by'     => 'product_id',
				'limit'        => 12,
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'nocache'      => true, // Using these as it was not updating latest orders data.
			)
		);

		if ( $top_sellers ) :
			// @codingStandardsIgnoreStart
			foreach ( $top_sellers as $product ) :
				?>
			<tr class="<?php echo esc_html( in_array( $product->product_id, $this->product_ids ) ? 'active' : '' ); ?>">
				<td class="count"><?php echo esc_html( $product->order_item_qty ); ?></td>
				<td class="name"><a href="<?php echo esc_url( add_query_arg( 'product_ids', $product->product_id ) ); ?>"><?php echo esc_html( get_the_title( $product->product_id ) ); ?></a></td>
				<td class="sparkline"><?php $this->sales_sparkline( $product->product_id, 7, 'count' ); ?></td>
			</tr>
				<?php
			endforeach;
			// @codingStandardsIgnoreEnd
		else :
			?>
			<tr><td colspan="3"><?php echo esc_html__( 'No products found in range', 'woo-one-click-upsell-funnel' ); ?></td></tr>
			<?php
		endif;
		?>
	</table>
	</div>
	<h4 class="section_title"><span><?php esc_html_e( 'Top freebies', 'woo-one-click-upsell-funnel' ); ?></span></h4>
	<div class="section">
		<table cellspacing="0">
		<?php
		$top_freebies = $this->get_order_report_data(
			array(
				'data'         => array(
					'_product_id' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => '',
						'name'            => 'product_id',
					),
					'_qty'        => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_qty',
					),
				),
				'where_meta'   => array(
					array(
						'type'       => 'order_item_meta',
						'meta_key'   => '_line_subtotal', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => '0', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'operator'   => '=',
					),
					array(
						'type'       => 'order_item_meta',
						'meta_key'   => 'is_upsell_purchase', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => 'true', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'operator'   => 'IN',
					),
				),
				'order_by'     => 'order_item_qty DESC',
				'group_by'     => 'product_id',
				'limit'        => 12,
				'query_type'   => 'get_results',
				'filter_range' => true,
				'nocache'      => true, // Using these as it was not updating latest orders data.
			)
		);

		if ( $top_freebies ) :
			// @codingStandardsIgnoreStart
			foreach ( $top_freebies as $product ) :
				?>
			<tr class="<?php echo esc_html( in_array( $product->product_id, $this->product_ids ) ? 'active' : '' ); ?>">
				<td class="count"><?php echo esc_html( $product->order_item_qty ); ?></td>
				<td class="name"><a href="<?php echo esc_url( add_query_arg( 'product_ids', $product->product_id ) ); ?>"><?php echo esc_html( get_the_title( $product->product_id ) ); ?></a></td>
				<td class="sparkline"><?php $this->sales_sparkline( $product->product_id, 7, 'count' ); ?></td>
			</tr>
				<?php
			endforeach;
			// @codingStandardsIgnoreEnd
		else :
			?>
			<tr><td colspan="3"><?php echo esc_html__( 'No products found in range', 'woo-one-click-upsell-funnel' ); ?></td></tr>
		<?php endif; ?>
	</table>
	</div>
	<h4 class="section_title"><span><?php esc_html_e( 'Top earners', 'woo-one-click-upsell-funnel' ); ?></span></h4>
	<div class="section">
	<table cellspacing="0">
		<?php
		$top_earners = $this->get_order_report_data(
			array(
				'data'         => array(
					'_product_id' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => '',
						'name'            => 'product_id',
					),
					'_line_total' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_total',
					),
				),
				'where_meta'   => array(
					'relation' => 'OR',
					array(
						'type'       => 'order_item_meta',
						'meta_key'   => 'is_upsell_purchase', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => 'true', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'operator'   => 'IN',
					),
				),
				'order_by'     => 'order_item_total DESC',
				'group_by'     => 'product_id',
				'limit'        => 12,
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				'nocache'      => true, // Using these as it was not updating latest orders data.
			)
		);

		if ( $top_earners ) :
			// @codingStandardsIgnoreStart
			foreach ( $top_earners as $product ) :
				?>
			<tr class="<?php echo esc_html( in_array( $product->product_id, $this->product_ids ) ? 'active' : '' ); ?>">
				<td class="count"><?php echo esc_html( ! empty( $product->order_item_qty ) ? $product->order_item_qty : 0 ); ?></td>
				<td class="name"><a href="<?php echo esc_url( add_query_arg( 'product_ids', $product->product_id ) ); ?>"><?php echo esc_html( get_the_title( $product->product_id ) ); ?></a></td>
				<td class="sparkline"><?php $this->sales_sparkline( $product->product_id, 7, 'count' ); ?></td>
			</tr>
				<?php
			endforeach;
			// @codingStandardsIgnoreEnd
		else :
			?>
			<tr><td colspan="3"><?php echo esc_html__( 'No products found in range', 'woo-one-click-upsell-funnel' ); ?></td></tr>
		<?php endif; ?>
	</table>
	</div>
	<script type="text/javascript">
	jQuery('.section_title').click(function(){
		var next_section = jQuery(this).next('.section');

		if ( jQuery(next_section).is(':visible') ) {
			return false;
		}

		jQuery('.section:visible').slideUp();
		jQuery('.section_title').removeClass('open');
		jQuery(this).addClass('open').next('.section').slideDown();

		return false;
	});
	jQuery('.section').slideUp( 100, function() {
		<?php if ( empty( $this->product_ids ) ) : ?>
		jQuery('.section_title:eq(1)').click();
	<?php endif; ?>
	});
	</script>
		<?php
	}

	/**
	 * Output an export link.
	 */
	public function get_export_button() {

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'woo-one-click-upsell-funnel' ) );
		}
		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '7day'; //phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		?>
		<a href="#"
			download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo esc_html( date_i18n( 'Y-m-d', time() ) ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php esc_attr_e( 'Date', 'woo-one-click-upsell-funnel' ); ?>"
			data-groupby="<?php echo esc_html( $this->chart_groupby ); ?>"
		><?php esc_html_e( 'Export CSV', 'woo-one-click-upsell-funnel' ); ?></a>
		<?php
	}

	/**
	 * Get the main chart.
	 */
	public function get_main_chart() {
		global $wp_locale;

		if ( empty( $this->product_ids ) ) {
			?>
		<div class="chart-container">
		<p class="chart-prompt"><?php esc_html_e( 'Choose a product to view stats', 'woo-one-click-upsell-funnel' ); ?></p>
		</div>
			<?php
		} else {
			// Get orders and dates in range - we want the SUM of order totals, COUNT of order items, COUNT of orders, and the date.
			$order_item_counts = $this->get_order_report_data(
				array(
					'data'         => array(
						'_qty'               => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => 'SUM',
							'name'            => 'order_item_count',
						),
						'post_date'          => array(
							'type'     => 'post_data',
							'function' => '',
							'name'     => 'post_date',
						),
						'_product_id'        => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => '',
							'name'            => 'product_id',
						),
						'is_upsell_purchase' => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => '',
							'name'            => 'wps_wocuf_pro_upsell_item_meta',
						),
					),
					'where_meta'   => array(
						'relation' => 'OR',
						array(
							'type'       => 'order_item_meta',
							'meta_key'   => array( '_product_id', '_variation_id' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
							'meta_value' => $this->product_ids, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
							'operator'   => 'IN',
						),
					),
					'group_by'     => 'product_id,' . $this->group_by_query,
					'order_by'     => 'post_date ASC',
					'query_type'   => 'get_results',
					'filter_range' => true,
					'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
					'nocache'      => true, // Using these as it was not updating latest orders data.
				)
			);

			$order_item_amounts = $this->get_order_report_data(
				array(
					'data'         => array(
						'_line_total'        => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => 'SUM',
							'name'            => 'order_item_amount',
						),
						'post_date'          => array(
							'type'     => 'post_data',
							'function' => '',
							'name'     => 'post_date',
						),
						'_product_id'        => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => '',
							'name'            => 'product_id',
						),
						'is_upsell_purchase' => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => '',
							'name'            => 'wps_wocuf_pro_upsell_item_meta',
						),
					),
					'where_meta'   => array(
						'relation' => 'OR',
						array(
							'type'       => 'order_item_meta',
							'meta_key'   => array( '_product_id', '_variation_id' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
							'meta_value' => $this->product_ids, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
							'operator'   => 'IN',
						),
					),
					'group_by'     => 'product_id, ' . $this->group_by_query,
					'order_by'     => 'post_date ASC',
					'query_type'   => 'get_results',
					'filter_range' => true,
					'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
					'nocache'      => true, // Using these as it was not updating latest orders data.
				)
			);

			// Prepare data for report.
			$order_item_counts  = $this->prepare_chart_data( $order_item_counts, 'post_date', 'order_item_count', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$order_item_amounts = $this->prepare_chart_data( $order_item_amounts, 'post_date', 'order_item_amount', $this->chart_interval, $this->start_date, $this->chart_groupby );

			// Encode in json format.
			$chart_data = wp_json_encode(
				array(
					'order_item_counts'  => array_values( $order_item_counts ),
					'order_item_amounts' => array_values( $order_item_amounts ),
				)
			);
			?>
	<div class="chart-container">
		<div class="chart-placeholder main"></div>
	</div>
			<?php // @codingStandardsIgnoreStart ?>
	  <script type="text/javascript">
		var main_chart;

		jQuery(function(){
		  var order_data = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( $chart_data ); ?>' ) );

		  var drawGraph = function( highlight ) {

			var series = [
			  {
				label: "<?php echo esc_js( __( 'Number of items sold', 'woo-one-click-upsell-funnel' ) ); ?>",
				data: order_data.order_item_counts,
				color: '<?php echo esc_html( $this->chart_colours['item_count'] ); //phpcs:ignore ?>',
				bars: { fillColor: '<?php echo esc_html( $this->chart_colours['item_count'] ); //phpcs:ignore ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo esc_html( $this->barwidth ); //phpcs:ignore ?> * 0.5, align: 'center' },
				shadowSize: 0,
				hoverable: false
			  },
			  {
				label: "<?php echo esc_js( __( 'Sales amount', 'woo-one-click-upsell-funnel' ) ); ?>",
				data: order_data.order_item_amounts,
				yaxis: 2,
				color: '<?php echo esc_html( $this->chart_colours['sales_amount'] ); //phpcs:ignore ?>',
				points: { show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true },
				lines: { show: true, lineWidth: 4, fill: false },
				shadowSize: 0,
				prepend_tooltip: "<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>"
			  }
			];


			if ( highlight !== 'undefined' && series[ highlight ] ) {
			  highlight_series = series[ highlight ];

			  highlight_series.color = '#9c5d90';

			  if ( highlight_series.bars )
				highlight_series.bars.fillColor = '#9c5d90';

			  if ( highlight_series.lines ) {
				highlight_series.lines.lineWidth = 5;
			  }
			}

			main_chart = jQuery.plot(
			  jQuery('.chart-placeholder.main'),
			  series,
			  {
				legend: {
				  show: false
				},
				grid: {
				  color: '#aaa',
				  borderColor: 'transparent',
				  borderWidth: 0,
				  hoverable: true
				},
				xaxes: [ {
				  color: '#aaa',
				  position: "bottom",
				  tickColor: 'transparent',
				  mode: "time",
				  timeformat: "<?php echo esc_html( ( 'day' === $this->chart_groupby ) ? '%d %b' : '%b' ); ?>",
				  monthNames: JSON.parse( decodeURIComponent( '<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->month_abbrev ) ) ); ?>' ) ),
				  tickLength: 1,
				  minTickSize: [1, "<?php echo esc_html( $this->chart_groupby ); ?>"],
				  font: {
					color: "#aaa"
				  }
				} ],
				yaxes: [
				  {
					min: 0,
					minTickSize: 1,
					tickDecimals: 0,
					color: '#ecf0f1',
					font: { color: "#aaa" }
				  },
				  {
					position: "right",
					min: 0,
					tickDecimals: 2,
					alignTicksWithAxis: 1,
					color: 'transparent',
					font: { color: "#aaa" }
				  }
				],
			  }
			);

			jQuery('.chart-placeholder').resize();
		  }

		  drawGraph();

		  jQuery('.highlight_series').hover(
			function() {
			  drawGraph( jQuery(this).data('series') );
			},
			function() {
			  drawGraph();
			}
		  );
		});
	  </script>
			<?php
			// @codingStandardsIgnoreEnd
		}
	}
}
