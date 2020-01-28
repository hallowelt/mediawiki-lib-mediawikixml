<?php

namespace HalloWelt\MediaWiki\Lib\MediaWikiXML;

use DOMDocument;
use DOMElement;

class Builder {

	/**
	 *
	 * @var DOMDocument
	 */
	private $dom = null;

	/**
	 *
	 * @var array
	 */
	private $pages = [];

	/**
	 *
	 * @param string $destFilepath
	 * @return boolean
	 */
	public function buildAndSave( $destFilepath ) {
		$this->build();
		$writtenBytes = $this->dom->save( $destFilepath );
		return $writtenBytes !== false;
	}

	/**
	 * @return DOMDocument
	 */
	public function build() {
		$this->initDOM();
	}

	private $mediaWikiXMLStub = <<<HERE
<mediawiki xmlns="http://www.mediawiki.org/xml/export-0.10/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.10/ http://www.mediawiki.org/xml/export-0.10.xsd" version="0.10" xml:lang="en">
</mediawiki>
HERE;

	private function initDOM() {
		$this->dom = new DOMDocument();
		$this->dom->loadXML( $this->mediaWikiXMLStub );
	}

	/**
	 *
	 * @param string $pagetitle
	 * @param string $wikitext
	 * @param string $timestamp
	 * @param string $username
	 * @param string $model
	 * @param strgin $format
	 * @return Builder
	 */
	public function addRevision( $pagetitle, $wikitext, $timestamp = '', $username = '', $model = 'wikitext' , $format = 'text/x-wiki' ) {
		$this->pages[$pagetitle];
		return $this;
	}

	/**
	 *
	 * @param string $pagetitle
	 * @param DOMElement $customEl
	 * @return Builder
	 */
	public function addCustomPageElement( $pagetitle, DOMElement $customEl ) {
		return $this;
	}

	/**
	 *
	 * @param string $elementName
	 * @return DOMElement
	 */
	public function createCustomPageElement( $elementName ) {
		return $this->dom->createElement( $elementName );
	}

}