<?php
declare(strict_types=1);

use HFB_Companion\Public_Posts;
use HFB_Companion\Reading_Time;
use PHPUnit\Framework\TestCase;

final class HfbCompanionContractTest extends TestCase
{
	public function test_public_post_helper_only_allows_public_published_blog_posts(): void
	{
		$this->assertTrue(Public_Posts::is_public_post($this->post()));
		$this->assertFalse(Public_Posts::is_public_post($this->post(['post_type' => 'page'])));
		$this->assertFalse(Public_Posts::is_public_post($this->post(['post_status' => 'private'])));
		$this->assertFalse(Public_Posts::is_public_post($this->post(['post_password' => 'secret'])));
		$this->assertFalse(Public_Posts::is_public_post(null));
	}

	public function test_reading_time_is_bounded_to_at_least_one_minute(): void
	{
		$this->assertSame(1, Reading_Time::from_content(''));
		$this->assertSame(1, Reading_Time::from_content(str_repeat('word ', 225)));
		$this->assertSame(2, Reading_Time::from_content(str_repeat('word ', 226)));
	}

	public function test_dynamic_blocks_have_metadata_and_editor_handles(): void
	{
		foreach (['post-stack', 'related-posts', 'reader-cta'] as $slug) {
			$metadata_path = dirname(__DIR__, 2) . "/blocks/{$slug}/block.json";
			$this->assertFileExists($metadata_path);

			$metadata = json_decode((string) file_get_contents($metadata_path), true);
			$this->assertIsArray($metadata);
			$this->assertSame('hfb/' . $slug, $metadata['name']);
			$this->assertSame(3, $metadata['apiVersion']);
			$this->assertSame('hfb-companion-blocks-editor', $metadata['editorScript']);
			$this->assertSame('hfb-companion-continuous-reading', $metadata['style']);
		}
	}

	public function test_visible_text_defaults_stay_out_of_block_metadata(): void
	{
		foreach (['related-posts', 'reader-cta'] as $slug) {
			$metadata_path = dirname(__DIR__, 2) . "/blocks/{$slug}/block.json";
			$metadata = json_decode((string) file_get_contents($metadata_path), true);
			$this->assertIsArray($metadata);
			$this->assertIsArray($metadata['attributes']);

			foreach ($metadata['attributes'] as $attribute) {
				if (($attribute['type'] ?? '') === 'string' && ($attribute['default'] ?? '') !== '') {
					$this->fail("Visible string default should be localized in PHP/editor code, not {$slug}/block.json.");
				}
			}
		}
	}

	/**
	 * @param array<string,mixed> $overrides
	 */
	private function post(array $overrides = []): WP_Post
	{
		$defaults = [
			'ID' => 123,
			'post_type' => 'post',
			'post_status' => 'publish',
			'post_password' => '',
			'post_content' => 'A public post.',
			'post_author' => 1,
		];

		return new WP_Post((object) array_merge($defaults, $overrides));
	}
}
