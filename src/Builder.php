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
	 * @var DOMElement[];
	 */
	private $customPageElements = [];

	/**
	 *
	 * @var DOMElement
	 */
	private $currentPageEl = null;

		/**
	 *
	 * @var DOMElement
	 */
	private $currentRevisionEl = null;

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
		foreach( $this->pages as $pagename => $revisionDatas ) {
			$this->currentPageEl = $this->dom->createElement( 'page' );
			$this->currentPageEl->appendChild(
				$this->dom->createElement( 'title', $pagename )
			);
			$this->dom->documentElement->appendChild( $this->currentPageEl );
			foreach( $revisionDatas as $revisionData ) {
				$this->appendRevisionElement( $revisionData );
			}
		}
	}

	private $mediaWikiXMLStub = <<<HERE
<mediawiki xmlns="http://www.mediawiki.org/xml/export-0.10/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.10/ http://www.mediawiki.org/xml/export-0.10.xsd" version="0.10" xml:lang="en">
</mediawiki>
HERE;

	private function initDOM() {
		$this->dom = new DOMDocument();
		$this->dom->formatOutput = true;
		#$this->dom->loadXML( $this->mediaWikiXMLStub );
		$this->dom->loadXML( '<mediawiki></mediawiki>' );
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
		if( !isset( $this->pages[$pagetitle] ) ) {
			$this->pages[$pagetitle] = [];
		}
		$this->pages[$pagetitle][] = [
			'text' => $wikitext,
			'timestamp' => $timestamp,
			'username' => $username,
			'model' => $model,
			'format' => $format
		];

		return $this;
	}

	/**
	 *
	 * @param string $pagetitle
	 * @param DOMElement $customEl
	 * @return Builder
	 */
	public function addCustomPageElement( $pagetitle, DOMElement $customEl ) {
		if( !isset( $this->customPageElements[$pagetitle] ) ) {
			$this->customPageElements[$pagetitle] = [];
		}
		$this->pages[$pagetitle][] = $customEl;

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

	private function appendRevisionElement( $data ) {
		$this->currentRevisionEl = $this->dom->createElement( 'revision' );

		$this->appendRevisionEl( 'username', $data );
		$this->appendRevisionEl( 'timestamp', $data );
		$this->appendRevisionEl( 'model', $data );
		$this->appendRevisionEl( 'format', $data );
		$this->appendRevisionEl( 'text', $data );

		$this->currentPageEl->appendChild( $this->currentRevisionEl );
	}

	private function appendRevisionEl( $nodeName, $data ) {
		if( !isset( $data[$nodeName] ) || empty( $data[$nodeName] )  ) {
			return;
		}
		$el = $this->dom->createElement( $nodeName, $data[$nodeName] );
		$this->currentRevisionEl->appendChild( $el );
	}
}
