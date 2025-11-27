<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WordPressTest extends TestCase
{
	private $dataStubs;
	
	protected function setUp(): void
	{
		$this->dataStubs = array(
			'bool_0'  => false,
			'bool_1'  => true,
			'empty'   => '',
			'html'    => '<p>Lorem ipsum</p>',
			'integer' => 1,
			'string'  => 'Lorem ipsum',
			'post'    => new \WP_Post( (object) array(
				'ID' => 123,
				'post_title' => 'Test Post',
				'post_content' => 'Lorem ipsum dolor sit amet',
				'post_date' => '2022-01-01 00:00:00',
				'post_author' => 1,
				'post_type' => 'post',
				'post_status' => 'publish',
			) ),
			'null'    => null,
		);
	}
}
