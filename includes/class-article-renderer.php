<?php
/**
 * Shared article markup renderer for stacked posts.
 *
 * @package HFB_Companion
 */

declare( strict_types=1 );

namespace HFB_Companion;

defined( 'ABSPATH' ) || exit;

final class Article_Renderer {

	private const WORDS_PER_MINUTE = 225;

	/**
	 * @param array<string,mixed> $args Rendering context.
	 */
	public function render( \WP_Post $post, array $args = [] ): string {
		if ( post_password_required( $post ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core password form filter.
			return (string) apply_filters( 'the_password_form', '', $post );
		}

		$context = $args['context'] ?? 'stack';
		if ( ! in_array( $context, [ 'main', 'stack' ], true ) ) {
			$context = 'stack';
		}

		$category = $this->primary_category( (int) $post->ID );
		$author   = (int) $post->post_author;

		$title     = get_the_title( $post );
		$permalink = get_permalink( $post );
		$excerpt   = wp_strip_all_tags( get_the_excerpt( $post ) );
		$date_iso  = get_the_date( 'c', $post );
		$date_ui   = get_the_date( '', $post );
		$reading   = $this->reading_time( $post );

		$author_user = get_user_by( 'id', $author );
		if ( $author_user ) {
			$display_name    = $author_user->display_name;
			$author_name     = $display_name;
			$author_bio      = get_the_author_meta( 'description', $author );
			$author_avatar   = get_avatar_url( $author, [ 'size' => 96 ] );
			$author_initials = $this->initials( $display_name );
		} else {
			$author_name     = __( 'Unknown Author', 'hungry-flamingo-blog-companion' );
			$author_bio      = '';
			$author_avatar   = '';
			$author_initials = __( '?', 'hungry-flamingo-blog-companion' );
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core content rendering filter.
		$content = apply_filters( 'the_content', $post->post_content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		$eyebrow = $category
			? sprintf(
				'<a class="tag tag--%1$s" href="%2$s">%3$s</a>',
				esc_attr( sanitize_html_class( $category->slug ) ),
				esc_url( get_category_link( $category->term_id ) ),
				esc_html( $category->name )
			)
			: '';

		$article_class = 'hfb-article hfb-article--' . esc_attr( $context );

		ob_start();
		?>
		<article id="post-<?php echo (int) $post->ID; ?>"
			class="<?php echo esc_attr( $article_class ); ?>"
			data-post-id="<?php echo (int) $post->ID; ?>"
			data-permalink="<?php echo esc_url( get_permalink( $post ) ); ?>"
			data-title="<?php echo esc_attr( get_the_title( $post ) ); ?>">

			<header class="hfb-article__header">
				<div class="article-eyebrow">
					<?php echo $eyebrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="article-eyebrow__note">
						<?php
						printf(
							/* translators: %s: estimated reading time in minutes. */
							esc_html__( 'Long read · %s min', 'hungry-flamingo-blog-companion' ),
							(int) $reading
						);
						?>
					</span>
				</div>
					<h1 class="hfb-article__title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h1>
				<?php if ( $excerpt ) : ?>
					<p class="hfb-article__lede"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>

				<div class="article-meta-bar">
					<div class="avatar" aria-hidden="true">
						<?php if ( $author_avatar ) : ?>
							<img src="<?php echo esc_url( $author_avatar ); ?>" alt="" loading="lazy" width="40" height="40" />
						<?php else : ?>
								<?php echo esc_html( $author_initials ); ?>
						<?php endif; ?>
					</div>
					<div>
							<div class="article-meta-bar__author"><?php echo esc_html( $author_name ); ?></div>
							<time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date_ui ); ?> · <?php
							/* translators: %d: estimated reading time in minutes. */
							printf( esc_html__( '%d min read', 'hungry-flamingo-blog-companion' ), (int) $reading );
						?></time>
					</div>
					<div class="spacer"></div>
						<button type="button" class="share-btn" data-hfb-share="<?php echo esc_url( $permalink ); ?>">
							<?php echo wp_kses( SVG::icon( 'share' ), self::svg_kses() ); ?>
							<?php esc_html_e( 'Share', 'hungry-flamingo-blog-companion' ); ?>
							<span class="share-btn__copied" hidden></span>
						</button>
				</div>
			</header>

			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<figure class="hfb-article__feature">
					<?php echo wp_kses_post( get_the_post_thumbnail( $post, 'full', [ 'loading' => 'lazy' ] ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="hfb-article__body">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<?php if ( $author_bio ) : ?>
				<aside class="hfb-article__author-card">
					<div class="avatar avatar--lg" aria-hidden="true">
						<?php if ( $author_avatar ) : ?>
							<img src="<?php echo esc_url( $author_avatar ); ?>" alt="" loading="lazy" width="64" height="64" />
						<?php else : ?>
								<?php echo esc_html( $author_initials ); ?>
						<?php endif; ?>
					</div>
					<div>
							<h4><?php echo esc_html( $author_name ); ?></h4>
							<p><?php echo esc_html( $author_bio ); ?></p>
						<a class="follow-btn" href="<?php echo esc_url( get_author_posts_url( $author ) ); ?>"><?php esc_html_e( 'More from this author', 'hungry-flamingo-blog-companion' ); ?></a>
					</div>
				</aside>
			<?php endif; ?>
		</article>
		<?php

		return (string) ob_get_clean();
	}

	private function reading_time( \WP_Post $post ): int {
		$word_count = str_word_count( wp_strip_all_tags( (string) $post->post_content ) );
		return max( 1, (int) ceil( $word_count / self::WORDS_PER_MINUTE ) );
	}

	/**
	 * @return array<string,array<string,bool>>
	 */
	private static function svg_kses(): array {
		$shape_attrs = [
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'class'           => true,
			'aria-hidden'     => true,
			'focusable'       => true,
		];

		return [
			'svg'      => array_merge(
				$shape_attrs,
				[
					'xmlns'   => true,
					'viewBox' => true,
					'width'   => true,
					'height'  => true,
				]
			),
			'g'        => $shape_attrs,
			'path'     => array_merge( $shape_attrs, [ 'd' => true ] ),
			'circle'   => array_merge(
				$shape_attrs,
				[
					'cx' => true,
					'cy' => true,
					'r'  => true,
				]
			),
			'rect'     => array_merge(
				$shape_attrs,
				[
					'x'      => true,
					'y'      => true,
					'width'  => true,
					'height' => true,
				]
			),
			'line'     => array_merge(
				$shape_attrs,
				[
					'x1' => true,
					'y1' => true,
					'x2' => true,
					'y2' => true,
				]
			),
			'polyline' => array_merge( $shape_attrs, [ 'points' => true ] ),
			'polygon'  => array_merge( $shape_attrs, [ 'points' => true ] ),
		];
	}

	private function primary_category( int $post_id ): ?\WP_Term {
		$yoast_primary = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_category', true );
		if ( $yoast_primary ) {
			$term = get_term( $yoast_primary, 'category' );
			if ( $term instanceof \WP_Term ) {
				return $term;
			}
		}

		$terms = get_the_category( $post_id );
		return $terms ? $terms[0] : null;
	}

	private function initials( string $name ): string {
		$parts = preg_split( '/\s+/', trim( $name ) ) ?: [];
		$take  = array_slice( $parts, 0, 2 );
		$out   = '';

		foreach ( $take as $part ) {
			$out .= function_exists( 'mb_substr' ) ? mb_substr( $part, 0, 1 ) : substr( $part, 0, 1 );
		}

		return strtoupper( $out ?: '·' );
	}
}
