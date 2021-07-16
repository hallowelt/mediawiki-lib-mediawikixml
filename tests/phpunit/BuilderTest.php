<?php

namespace HalloWelt\MediaWiki\Lib\MediaWikiXML\Tests;

use HalloWelt\MediaWiki\Lib\MediaWikiXML\Builder;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase {

	/**
	 * @covers HalloWelt\MediaWiki\Lib\MediaWikiXML\Builder::build
	 * @return void
	 */
	public function testBuild() {
		$actualFile = sys_get_temp_dir() . '/output.xml';
		$exprectedFile = __DIR__ . '/data/expected1.xml';

		$builder = new Builder();
		$builder->addRevision( "Coffee & Tea", "raffiné" );
		$builder->buildAndSave( $actualFile );

		$this->assertXmlFileEqualsXmlFile( $exprectedFile, $actualFile );
	}
}
