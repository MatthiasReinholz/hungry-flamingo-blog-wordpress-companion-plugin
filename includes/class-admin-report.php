<?php
/**
 * Local editorial health report.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Admin_Report {

	private ?Related_Posts $related_posts = null;

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	public function register_page(): void {
		add_management_page(
			__( 'Hungry Flamingo Report', 'hungry-flamingo-blog-companion' ),
			__( 'Hungry Flamingo', 'hungry-flamingo-blog-companion' ),
			'manage_options',
			'hfb-editorial-report',
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this report.', 'hungry-flamingo-blog-companion' ) );
		}

		$posts = get_posts(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => 30,
				'ignore_sticky_posts'    => true,
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			)
		);

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Hungry Flamingo Editorial Report', 'hungry-flamingo-blog-companion' ); ?></h1>
			<p><?php esc_html_e( 'This local report reviews recent published posts for reader-retention basics. It does not send data anywhere.', 'hungry-flamingo-blog-companion' ); ?></p>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Post', 'hungry-flamingo-blog-companion' ); ?></th>
						<th><?php esc_html_e( 'Words', 'hungry-flamingo-blog-companion' ); ?></th>
						<th><?php esc_html_e( 'Internal links', 'hungry-flamingo-blog-companion' ); ?></th>
						<th><?php esc_html_e( 'Related candidates', 'hungry-flamingo-blog-companion' ); ?></th>
						<th><?php esc_html_e( 'Status', 'hungry-flamingo-blog-companion' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! $posts ) : ?>
						<tr><td colspan="5"><?php esc_html_e( 'No published posts found.', 'hungry-flamingo-blog-companion' ); ?></td></tr>
					<?php endif; ?>
					<?php foreach ( $posts as $post ) : ?>
						<?php $row = $this->analyze_post( $post ); ?>
						<tr>
							<td>
								<strong><a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></strong>
								<br />
								<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'View', 'hungry-flamingo-blog-companion' ); ?></a>
							</td>
							<td><?php echo (int) $row['words']; ?></td>
							<td><?php echo (int) $row['internal_links']; ?></td>
							<td><?php echo (int) $row['related_candidates']; ?></td>
							<td><?php echo esc_html( $row['status'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * @return array{words:int,internal_links:int,related_candidates:int,status:string}
	 */
	private function analyze_post( \WP_Post $post ): array {
		$words              = str_word_count( wp_strip_all_tags( (string) $post->post_content ) );
		$internal_links     = $this->count_internal_links( (string) $post->post_content );
		$related_candidates = count( $this->related_posts()->ids( (int) $post->ID, 3 ) );
		$status             = __( 'Healthy', 'hungry-flamingo-blog-companion' );

		if ( 0 === $internal_links ) {
			$status = __( 'Add internal links', 'hungry-flamingo-blog-companion' );
		} elseif ( 0 === $related_candidates ) {
			$status = __( 'Needs related posts', 'hungry-flamingo-blog-companion' );
		}

		return array(
			'words'              => $words,
			'internal_links'     => $internal_links,
			'related_candidates' => $related_candidates,
			'status'             => $status,
		);
	}

	private function count_internal_links( string $html ): int {
		if ( '' === trim( $html ) ) {
			return 0;
		}

		preg_match_all( '/<a\s[^>]*href=[\'"]([^\'"]+)[\'"]/i', $html, $matches );
		$home_host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$count     = 0;

		foreach ( $matches[1] as $href ) {
			$href = trim( html_entity_decode( (string) $href, ENT_QUOTES ) );
			if ( '' === $href || '#' === $href[0] || str_starts_with( $href, 'mailto:' ) || str_starts_with( $href, 'tel:' ) ) {
				continue;
			}

			if ( str_starts_with( $href, '/' ) && ! str_starts_with( $href, '//' ) ) {
				++$count;
				continue;
			}

			$host = (string) wp_parse_url( $href, PHP_URL_HOST );
			if ( $host && $home_host && strtolower( $host ) === strtolower( $home_host ) ) {
				++$count;
			}
		}

		return $count;
	}

	private function related_posts(): Related_Posts {
		if ( ! $this->related_posts instanceof Related_Posts ) {
			$this->related_posts = new Related_Posts();
		}

		return $this->related_posts;
	}
}
