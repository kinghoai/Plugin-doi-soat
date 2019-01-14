<?php
/**
 * Plugin Name: Doi soat Vinaquick
 * Plugin URI: https://vinaquick.com
 * Description: Plugin xem đối soát cho web mevabe.vinaquick.com
 * Version: 1.0 
 * Author: Lâm Thanh Hoài
 * Author URI: https://mevabe.vinaquick.com
 * License: GPLv2
 */
?>
<?php 
function doi_soat_enqueue_admin_script() {
	wp_enqueue_script('admin_doi_soat_script', plugins_url( '/js/doi-soat.js', __FILE__ ));
}
add_action( 'doi_soat_admin_script', 'doi_soat_enqueue_admin_script' );
/*
Tạo trang đối soát
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Start Class
if ( ! class_exists( 'DOI_SOAT' ) ) {

	class DOI_SOAT {
		public function __construct() {
			if ( is_admin() ) {
				add_action( 'admin_menu', array( 'DOI_SOAT', 'add_admin_menu' ) );
				add_action( 'admin_init', array( 'DOI_SOAT', 'register_settings' ) );
			}

		}
		public static function get_doi_soat() {
			return get_option( 'doi_soat' );
		}
		/**
		 * Add sub menu page
		 *
		 * @since 1.0.0
		 */
		public static function add_admin_menu() {
			add_menu_page(
				esc_html__( 'ĐỐI SOÁT', 'vinaquick' ),
				esc_html__( 'ĐỐI SOÁT', 'vinaquick' ),
				'manage_options',
				'doi_soat',
				array( 'DOI_SOAT', 'create_admin_page_doi_soat' )
			);
		}
		public static function register_settings() {
			register_setting( 'doi_soat', 'doi_soat', array( 'DOI_SOAT', 'sanitize' ) );
		}
		public static function create_admin_page_doi_soat() {
			do_action( 'admin_thongke_scripts' );
			do_action( 'doi_soat_admin_script' );

				$tk_year = date( 'Y' );
				$tk_month = date( 'm' );
			
            $argsDoiSoat = array(
				'query_label' => 'our_cat_massage_query',
				'posts_per_page'   => -1,
				'post_status'      => array('wc-nhan-vien-xac-nha','wc-thanh-toan-du','wc-da-order','wc-hang-ve-vn','wc-hang-ve-kho-my'),
				'post_type'        => 'shop_order',
				'orderby'          => 'date',
				'order'	           => 'DESC',
				'date_query' => array(
					array(
					'after' => '-30 days',
					'column' => 'post_date',
					),
					),
			);
			if(isset($_GET['order_id']) && !empty( $_GET['order_id'] )){
				$argsDoiSoat["post__in"] = array($_GET['order_id']);
				$argsDoiSoat['post_status'] = 'any';
			}
			if( isset($_GET['page']) 
				&& $_GET['page'] == 'doi_soat'
				&& isset($_GET['start_date']) 
				&& !empty( $_GET['start_date'])
				&& isset($_GET['end_date']) 
				&& !empty( $_GET['end_date'])
				){
					$from = explode( '/', sanitize_text_field( $_GET['start_date'] ) );
					$to   = explode( '/', sanitize_text_field( $_GET['end_date'] ) );
					$from = array_map( 'intval', $from );
					$to   = array_map( 'intval', $to );
				}
				if (
					3 === count( $to )
					&& 3 === count( $from )
				) {
					list( $day_from, $month_from, $year_from ) = $from;
					list( $day_to, $month_to, $year_to )       = $to;
					$argsDoiSoat["date_query"] = array(
						'after' => array(
							'year'  => $year_from,
							'month' => $month_from,
							'day'   => $day_from,
							'hour'  => '0',
							'minute'=> '0',
							'second'=> '0',
						),
						'before' => array(
							'year'  => $year_to,
							'month' => $month_to,
							'day'   => $day_to,
							'hour'  => '23',
							'minute'=> '59',
							'second'=> '59',
						),
					);
				}
				

			add_filter('posts_where', function ($where, $query) {
				$label = $query->query['query_label'] ?? '';
				if($label === 'our_cat_massage_query') {
					global $wpdb;
					if(isset($_GET['order_status']) && !empty( $_GET['order_status'] )){
						$where .= " AND $wpdb->posts.ID IN (SELECT ".$wpdb->posts .".ID FROM ".$wpdb->posts ." WHERE post_status = '".$_GET['order_status']."' )";
					}
					if(isset($_GET['hang_co_san']) && !empty( $_GET['hang_co_san'] )){
						if($_GET['hang_co_san'] == "hang-co-san"){
							$where .= " AND ($wpdb->posts.ID IN(
								SELECT $wpdb->posts.ID FROM $wpdb->posts
								INNER JOIN " . $wpdb->prefix . "woocommerce_order_items ON $wpdb->posts.ID = " . $wpdb->prefix . "woocommerce_order_items.order_id
								INNER JOIN " . $wpdb->prefix . "woocommerce_order_itemmeta ON " . $wpdb->prefix . "woocommerce_order_items.order_item_id = " . $wpdb->prefix . "woocommerce_order_itemmeta.order_item_id
								INNER JOIN $wpdb->postmeta ON " . $wpdb->prefix . "woocommerce_order_itemmeta.meta_value = $wpdb->postmeta.post_id
								WHERE $wpdb->posts.post_type = 'shop_order'
								AND " . $wpdb->prefix . "woocommerce_order_items.order_item_type = 'line_item'
								AND " . $wpdb->prefix . "woocommerce_order_itemmeta.meta_key = '_product_id'
								AND $wpdb->postmeta.meta_key = 'wpcf-best-seller'
								AND $wpdb->postmeta.meta_value = 3) )";
						}
						if($_GET['hang_co_san'] == "hang-order"){
							$where .= " AND ($wpdb->posts.ID IN(
								SELECT $wpdb->posts.ID FROM $wpdb->posts
								INNER JOIN " . $wpdb->prefix . "woocommerce_order_items ON $wpdb->posts.ID = " . $wpdb->prefix . "woocommerce_order_items.order_id
								INNER JOIN " . $wpdb->prefix . "woocommerce_order_itemmeta ON " . $wpdb->prefix . "woocommerce_order_items.order_item_id = " . $wpdb->prefix . "woocommerce_order_itemmeta.order_item_id
								INNER JOIN $wpdb->postmeta ON " . $wpdb->prefix . "woocommerce_order_itemmeta.meta_value = $wpdb->postmeta.post_id
								WHERE $wpdb->posts.post_type = 'shop_order'
								AND " . $wpdb->prefix . "woocommerce_order_items.order_item_type = 'line_item'
								AND " . $wpdb->prefix . "woocommerce_order_itemmeta.meta_key = '_product_id'
								AND $wpdb->postmeta.meta_key = 'wpcf-best-seller'
								AND $wpdb->postmeta.meta_value != 3) )";
						}
					}
				}
				return $where;
			}, 10, 2);
			$allOrderDoiSoat = new WP_Query($argsDoiSoat);
			//echo $allOrderDoiSoat->request;
				$arrNv = array(
					"0" => "Chưa có",
					"1" => "Tuấn Anh",
					"2" => "Khánh Hân",
					"3" => "Thái Trinh",
					"4" => "Thuỳ Linh",
					"5" => "Thy Trần",
				);
				$arrThanhToan = array(
					"0" => "Chưa có",
					"1" => "ĐỦ",
					"2" => "CỌC",
					"3" => "Refund",
					"4" => "Tiền chưa vào",
				);

				$orders_statuses = wc_get_order_statuses();
				?>

					<div id="filter_doi_soat" style="padding-top:50px" class="container-fluid">
						<div class="row">
							<div class="col-6 col-md-3">
								<select id="order_statuses_doi_soat" class="form-control">
									<option value="" name="post_status">--Trạng thái order--</option>
								<?php 
									foreach ($orders_statuses as $k => $v) {
										echo '<option value="'.$k.'" name="post_status">'.$v.'</option>';
									}
								?>
								</select>
							</div>

							<div class="col-6 col-md-3">
								<select id="hang_co_san_doi_soat" class="form-control">
										<option value="" name="post_status">--Select có sẵn--</option>
										<option value="hang-co-san" name="post_status">Hàng có sẵn</option>
										<option value="hang-order" name="post_status">Hàng order</option>
									</select>
							</div>

							<div class="col-6 col-md-3">
								<div class="row">
									<div class="col-6">
										Start Date:
									</div>
									<div class="col-6">
										<input type="text" class="form-control" id="start_date" placeholder="Ex: 30/1/2018">
									</div>
								</div>

								<div class="row">
									<div class="col-6">
										End Date:
									</div>
									<div class="col-6">
										<input type="text" class="form-control" id="end_date" placeholder="Ex: 30/2/2018">	
									</div>
								</div>
							</div>

							<div class="col-6 col-md-3">
								<input type="text" class="form-control" id="search_id_order" placeholder="Search ID Order">
							</div>

						</div>
						<div>
							<button id="doi-soat-filter" type="button" class="btn btn-primary">Tìm đơn hàng</button>
							<button id="reset-filter" type="button" class="btn btn-warning">RESET</button>
						</div>
					</div>


					<div class="table-responsive">
						<table class="table table-bordered table-responsive table-doi-soat" style="background:#fff">
							<thead>
								<tr>
									<th>Date</th>
									<th>Mã Order</th>
									<th>NV</th>
									<th>Status</th>
									<th>Thanh toán</th>
									<th width="60">TT</th>
									<th width="120">Qty</th>
									<th width="150">Trạng thái SP</th>
									<th width="120">Hình ảnh</th>
									<th width="300">Title</th>
									<th width="300">Link web Mỹ</th>
									<th width="100">Có sẵn</th>
									<th width="120">Giá Item</th>
									<th>Tổng đơn(vnđ)</th>
									<th>Đã thanh toán</th>
									<th>Phải thu</th>
									<th>Phải trả KH</th>
									<th>Khách hàng</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if($allOrderDoiSoat->have_posts()):
									while($allOrderDoiSoat->have_posts()):$allOrderDoiSoat->the_post();
									?>
									<?php 
										$order = wc_get_order( get_the_ID() );
										$dataOrder = $order->data;
										$orderStatus = $dataOrder["status"];
										$orderStatusName = wc_get_order_status_name($orderStatus);
										$orderId = $dataOrder["id"];

										$metaData = $dataOrder["meta_data"];
										$metaNv = $metaData[5]->value;
										$metaThanhToan = $metaData[3]->value;

										$metaDate = $dataOrder["date_created"];
										$metaDate = strtotime($metaDate);
										$orderDate = date('d-m-Y',$metaDate);
										$tenNv = $arrNv[$metaNv];
										$ttThanhToan = $arrThanhToan[$metaThanhToan];
										$billing = $dataOrder["billing"];
										$firstName = $billing["first_name"];
										$lastName = $billing["last_name"];
										$phone = $billing["phone"];
										$total = $dataOrder["total"];
										$metaTienCoc = $metaData[4]->value;
										if($metaThanhToan == 1){
											$tienThanhToanCoc = $total;
										} else {
											$tienThanhToanCoc = $metaTienCoc;
										}
										if($total >= $tienThanhToanCoc){
											$phaiThu = $total - $tienThanhToanCoc;
											$phaiTra = 0;
										} else{
											$phaiThu = 0;
											$phaiTra = $tienThanhToanCoc - $total;
										}
										$lineItems = $dataOrder["line_items"];
										$soLuong = 0;
										
									?>
									<tr>
										<td><?php echo $orderDate ?></td>
										<td><a class="adminOrderId" style="font-size: 1.4rem;font-weight: bolder;" href="/wp-admin/post.php?post=<?php echo $orderId?>&action=edit" target="_blank"><?php echo $orderId?></a></td>
										<td><?php echo $tenNv?></td>
										<td><?php echo $orderStatusName ?></td>
										<td style="background-color:<?php
											if ($metaThanhToan == "2") {
												echo "#FF39D4";
											} elseif ($metaThanhToan == "1") {
												echo "#2668BB";
											} else {
												echo "#FFFFFF";
											}
										?>"><?php echo $ttThanhToan?></td>
										<td colspan="8" style="padding:0">
											<table class="table-bordered-sub" style="width: 100%">
												<tbody>
												<?php 
													foreach($lineItems as $lineItem){
														$soLuong += 1;
														$jsonItem = json_decode($lineItem);
														$itemName = $jsonItem->name;
														$itemQty = $jsonItem->quantity;
														$itemPrice = $jsonItem ->total;
														$product_id = $jsonItem->product_id;
														$labelSetting = get_post_meta($product_id, 'wpcf-best-seller', true);
														$linkWebMy = get_post_meta($product_id, 'wpcf-link-product-web-my', true);
														$variation_id = $jsonItem->variation_id;
														$product = wc_get_product($variation_id);
														$imgId = get_post_thumbnail_id($product_id);
														$img = wp_get_attachment_image_src($imgId, "large");
														$metaDatas = $jsonItem->meta_data;
														$trangThaiSp = "Chưa chọn";
														foreach($metaDatas as $metaData){
															if($metaData->key == "trang_thai_sp"){
																$trangThaiSp = $metaData->value;
															}
														}
													?>
														<tr>
															<td width="60"><?php echo $soLuong?></td>
															<td width="120"><?php echo $itemQty?></td>
															<td width="150"><?php echo $trangThaiSp?></td>
															<td width="120"><img src="<?php echo $img[0]?>" class="img-doi-soat"></td>
															<td width="300"><a href="<?php echo get_permalink($product_id)?>" target="_blank"><?php echo $itemName?></a></td>
															<td width="300" style="overflow: hidden">
															<?php if($linkWebMy) {
																?>
																	<a href="<?php echo $linkWebMy?>" target="_blank" style ="max-width: 200px; display: block;text-overflow: ellipsis;white-space: nowrap;">
																		<?php echo $linkWebMy?>
																	</a>
															<?php }
															?>
															</td>
															<td width="100" style="background:<?php if($labelSetting == 3)
																								{echo "#42D42B";} 
																								else {echo "#ECF0F5";} ?>">
																<?php if($labelSetting == 3)
																	{echo "CÓ SẴN";} 
																	else {echo "ORDER";} ?>
															</td>
															<td width="120" style="background:#F4D03F; font-size: 18px; font-weight: bold;"><?php echo number_format($itemPrice)?></td>
														</tr>
													<?php														
													}
												?>
												</tbody>
											</table>
										</td>
										<td style="font-size: 18px; font-weight: bold;"><?php echo number_format($total)?></td>
										<td style="font-size: 18px; font-weight: bold;"><?php echo number_format($tienThanhToanCoc)?></td>
										<td style="font-size: 18px; font-weight: bold;"><?php echo number_format($phaiThu)?></td>
										<td style="font-size: 18px; font-weight: bold;"><?php echo number_format($phaiTra)?></td>
										<td><?php echo $firstName ." ". $lastName . " - " . $phone?></td>
									</tr>
									<?php
									endwhile; endif; wp_reset_query();
								?>
							</tbody>
						</table>
					</div>
				</div>
				<?php
			
		}
	}
}
new DOI_SOAT();