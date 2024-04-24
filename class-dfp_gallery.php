<?php

require 'vendor/autoload.php';
use Detection\MobileDetect;
class DFP_Gallery {
	public function __construct() {
		add_action( 'init', [ $this, 'add_shortcode' ] );
		add_action( 'after_setup_theme', [ $this, 'add_image_sizes' ] );

		add_action('wp_ajax_tdf_hidden_project_content', array($this, 'render_item_content'));
		add_action('wp_ajax_nopriv_tdf_hidden_project_content', array($this, 'render_item_content'));


	}

	public function add_image_sizes() {
		add_image_size( '1:1', 480, 480, true );
		add_image_size( '1:1_2x', 960, 960, true );
		add_image_size( '1:1_4x', 1920, 1920, true );

		add_image_size( '9:16', 270, 480, true );
		add_image_size( '9:16_2x', 540, 960, true );
		add_image_size( '9:16_4x', 1080, 1920, true );

		add_image_size( '16:9', 480, 270, true );
		add_image_size( '16:9_2x', 960, 540, true );
		add_image_size( '16:9_4x', 1920, 1080, true );
	}

	public function add_shortcode() {
		add_shortcode( 'df-gallery', [ $this, 'render_shortcode' ] );
	}

	public function render_shortcode( $atts ) {
		$atts = shortcode_atts( [
			'category' => '',
		], $atts, 'df-gallery' );

		ob_start();
		$args = [
			'post_type'      => 'projects',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			// 'no_found_rows' => true,
			'fields' => 'ids',
		];

		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'project_categories',
					'field'    => 'slug',
					'terms'    => explode( ',', $atts['category'] ),
				]
			];
		}

		$q = new WP_Query( $args );

		if ( $q->have_posts() ) {
			?>
			<div class="df-gallery">
				<div class="items">
					<?php
					while ( $q->have_posts() ) {
						$q->the_post();
						$project_logo = get_field( 'thumbnail' );
						$project_title = get_field( 'thumbnail_title' );
						$project_subtitle = get_field( 'thumbnail_subtitle' );
						?>
						<div class="item" data-item-id="<?php the_ID(); ?>">
							<?php
							$overlay = get_field( 'thumbnail_logo_overlay' );
							if ( $overlay ) {
								$html = wp_get_attachment_image( $overlay['ID'], 'full', false, [
									'class' => 'item-overlay',
								] );
								echo wp_kses_post( $html );
							}
							?>
							<?php
							echo wp_get_attachment_image( $project_logo['ID'], '1:1', false, [
								'class' => 'item-background',
							] );

							if ( ! empty( $project_title ) ) {
								?>
								<div class="project_title">
									<?= get_field( 'thumbnail_title' ); ?>
									<!-- echo get_field( 'thumbnail_title' ); -->
								</div>
								<?php
							}

							if ( ! empty( $project_subtitle ) ) {
								?>
								<div class="project_subtitle">
									<?= get_field( 'thumbnail_subtitle' ); ?>
								</div>
								<?php
							}
							
							?>
							
						</div>
						<?php
						// $this->render_item_content();
					}
					?>
				</div>
			</div>
			<?php
			wp_enqueue_style( 'swiper', plugin_dir_url( __FILE__ ) . '/css/swiper-budle.min.css' );
			wp_enqueue_script( 'swiper', plugin_dir_url( __FILE__ ) . '/js/swiper-bundle.min.js', [], false, true );
			wp_enqueue_script( 'vimeo', 'https://player.vimeo.com/api/player.js', [], false, true );

			wp_enqueue_style( 'df_shortcode', plugin_dir_url( __FILE__ ) . '/css/shortcode.css' );
			wp_enqueue_script(
				'df_shortcode-handler',
				plugin_dir_url( __FILE__ ) . 'js/shortcode-handler.js',
				[ 'swiper', 'vimeo' ],
				filemtime( plugin_dir_path( __FILE__ ) . '/js/shortcode-handler.js' ),
				true
			);
			wp_localize_script( 'df_shortcode-handler', 'tdf_hidden_project_content', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		} else {
			echo 'No posts found';
		}

		wp_reset_postdata();

		$markup = ob_get_clean();
		return $markup;
	}

	public function render_item_content() {
		
		if( isset( $_POST['id'] ) ) {

			$q = new WP_Query( [
				'post_type'      => 'projects',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'no_found_rows' => true,
				'fields' => 'ids',
				'p' => $_POST['id']
			] );
		}

		while ( $q->have_posts() ) {

			$q->the_post();

			ob_start(); ?>
	
			<div class="item-content is-hidden" data-item-id="<?php the_ID(); ?>">

			<?php 
				$detect = new MobileDetect();
				$isMobile = $detect->isMobile(); // bool(false)
				// $isTablet = $detect->isTablet(); // bool(false)

				// if ( $isMobile || $isTablet ) {
				// }

				if ( ! $isMobile ) { ?>

					<div class="desktop">
						<?php
						if ( have_rows( 'project_content_desktop' ) ) {
							?>
							<div class="swiper">
								<div class="swiper-wrapper">
									<?php
									while ( have_rows( 'project_content_desktop' ) ) : the_row();
										$layout = get_row_layout();
										?>
										<div class="swiper-slide <?php echo esc_attr( $layout ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>">
											<?php
											switch ( $layout ) {
												case 'full_width_image':
													$image = get_sub_field( 'image' );
													if ( $image ) {
														$html = wp_get_attachment_image( $image['ID'], '16:9_4x' );
														echo wp_kses_post( $html );
													}
												break;

												case 'video':
													?>
														<div class="video-wrapper" data-tdf-vimeo-url="<?= get_sub_field( 'vimeo_url' ); ?>"></div>
													<?php
												break;

												case 'content_image_video':
													?>
													<div class="two-col">
														<div class="col" style="  display: flex;align-items: center;justify-content: center">
															<div>
															<?php
															$title = get_sub_field( 'title' );
															if ( ! empty( $title ) ) {
																?>
																<div class="slide-title">
																	<?php echo wp_kses_post( $title ); ?>
																</div>
																<?php
															}

															$logo = get_sub_field( 'logo' );
															if ( ! empty( $logo ) ) {
																?>
																<img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr( $logo['alt'] ); ?>" class="logo" />
																<?php
															}

															$subtitle = get_sub_field( 'subtitle' );
															if ( ! empty( $subtitle ) ) {
																?>
																<div class="subtitle">
																	<?php echo wp_kses_post( $subtitle ); ?>
																</div>
																<?php
															}

															$content__copy = get_sub_field( 'content__copy' );
															if ( ! empty( $content__copy ) ) {
																?>
																<div class="content__copy">
																	<?php echo wp_kses_post( $content__copy ); ?>
																</div>
																<?php
															}

															$details = get_sub_field( 'details' );
															if ( ! empty( $details ) ) {
																?>
																<div class="details">
																	<?php echo wp_kses_post( $details ); ?>
																</div>
																<?php
															}
															
															$client_logo = get_sub_field( 'client_logo' );
															if ( ! empty( $client_logo ) ) {
																?>
																<img src="<?php echo esc_url( $client_logo['url'] ); ?>" alt="<?php echo esc_attr( $client_logo['alt'] ); ?>" class="client_logo" />
																<?php
															}
															?>
														</div>
														</div>
														<div class="col">
															<?php
															$image = get_sub_field( 'background_image' );
															if ( $image ) {
																$html = wp_get_attachment_image( $image['ID'], '9:16_4x', false, [
																	'class' => 'two-col-image',
																] );
																echo wp_kses_post( $html );
															}
															?>
														</div>
													</div>
													<?php
												break;
											}
											?>
										</div>
										<?php
									endwhile;
									?>
								</div>
								<div class="dfp-button-prev dfp-nav-button"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/prev-arrow.png' ); ?>"/></div>
								<div class="dfp-button-next dfp-nav-button"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/images/next-arrow.png' ); ?>"/></div>
							</div>
							<?php
						}
						?>
					</div>

				<?php } ?>

				<div class="mobile">
					<?php
					if ( have_rows( 'project_content_mobile' ) ) {
						?>
						<div class="swiper">
							<div class="swiper-wrapper">
								<?php
								while ( have_rows( 'project_content_mobile' ) ): the_row();

									$layout = get_row_layout();
									?>
									<div class="swiper-slide <?php echo esc_attr( $layout ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>">
										<?php
										switch ( $layout ) {
											case 'full_width_image':
												$image = get_sub_field( 'image' );
												if ( $image ) {
													$html = wp_get_attachment_image( $image['ID'], '9:16_4x' );
													echo wp_kses_post( $html );
												}
											break;

											case 'video':
												?>
													<div class="video-wrapper" data-tdf-vimeo-url="<?= get_sub_field( 'vimeo_url' ); ?>"></div>
												<?php
											break;

											case 'intro_content': 
												$title = get_sub_field( 'title' );
												if ( ! empty( $title ) ) {
													?>
													<div class="slide-title">
														<?php echo wp_kses_post( $title ); ?>
													</div>
													<?php
												}
												
												$logo = get_sub_field( 'logo' );
												if ( ! empty( $logo ) ) {
													?>
													<img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr( $logo['alt'] ); ?>" class="logo" />
													<?php
												}

												$subtitle = get_sub_field( 'subtitle' );
												if ( ! empty( $subtitle ) ) {
													?>
													<div class="subtitle">
														<?php echo wp_kses_post( $subtitle ); ?>
													</div>
													<?php
												}

												$content__copy = get_sub_field( 'content__copy' );
												if ( ! empty( $content__copy ) ) {
													?>
													<div class="content__copy">
														<?php echo wp_kses_post( $content__copy ); ?>
													</div>
													<?php
												}

												$details = get_sub_field( 'details' );
												if ( ! empty( $details ) ) {
													?>
													<div class="details">
														<?php echo wp_kses_post( $details ); ?>
													</div>
													<?php
												}

												$client_logo = get_sub_field( 'client_logo' );
												if ( ! empty( $client_logo ) ) {
													?>
													<img src="<?php echo esc_url( $client_logo['url'] ); ?>" alt="<?php echo esc_attr( $client_logo['alt'] ); ?>" class="client_logo" />
													<?php
												}
											break;
										}
										?>
									</div>
									<?php
								endwhile;
								?>
							</div>
							<div class="dfp-button-prev dfp-nav-button"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/prev-arrow.png' ); ?>"/></div>
							<div class="dfp-button-next dfp-nav-button"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/next-arrow.png' ); ?>"/></div>
						</div>
						<?php
					}
					?>
				</div>
				<div class="close-button"><img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'images/close-button.png' ); ?>"/></div>
			</div>
			<?php
		}
		$project_content = ob_get_contents();
		ob_end_clean();
		echo $project_content;
		die();
	}
}