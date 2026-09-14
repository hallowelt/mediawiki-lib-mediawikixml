<?php

namespace HalloWelt\MediaWiki\Lib\MediaWikiXML\Tests;

use HalloWelt\MediaWiki\Lib\MediaWikiXML\Builder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass( Builder::class )]
class BuilderTest extends TestCase {

	#[DataProvider( 'provideTestBuildAndSaveData' )]
	/**
	 * @param array $addRevisionCalls
	 * @param string $expectedFile
	 * @return void
	 * @covers HalloWelt\MediaWiki\Lib\MediaWikiXML\Builder::buildAndSave
	 */
	public function testBuildAndSave( $addRevisionCalls, $expectedFile ) {
		$actualFile = sys_get_temp_dir() . '/output.xml';
		$builder = new Builder();
		foreach ( $addRevisionCalls as $addRevisionCallArgs ) {
			call_user_func_array( [ $builder, 'addRevision' ], $addRevisionCallArgs );
		}
		$builder->buildAndSave( $actualFile );

		$this->assertXmlFileEqualsXmlFile( $expectedFile, $actualFile );
	}

	/**
	 * @return array
	 */
	public static function provideTestBuildAndSaveData() {
		return [
			'standard' => [
				[
					[ "Coffee & Tea", "raffiné" ]
				],
				__DIR__ . '/data/expected1.xml'
			],
			'implicit-model-and-format' => [
				[
					[ "Some.css", "* { display:none; }" ],
					[ "Some.js", "alert( 'Hello World' );" ],
					[ "Some.json", "{ \"some\": \"value\" }" ],
					[ "Coffee & Tea", "raffiné" ]
				],
				__DIR__ . '/data/expected2.xml'
			],
			'explicit-model-and-format' => [
				[
					[ "Some.form", "...", '', '', 'form', 'text/x-form' ]
				],
				__DIR__ . '/data/expected3.xml'
			],
			'username-wrapped-in-contributor' => [
				[
					[ "Coffee & Tea", "raffiné", '', 'Foobar' ]
				],
				__DIR__ . '/data/expected4.xml'
			]
		];
	}
}
