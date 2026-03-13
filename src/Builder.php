<?php

namespace HalloWelt\MediaWiki\Lib\MediaWikiXML;

use DOMDocument;
use DOMElement;

class Builder {

	/**
	 *
	 * @var DOMDocument
	 */
	protected $dom = null;

	/**
	 *
	 * @var array
	 */
	private $pages = [];

	/**
	 *
	 * @var DOMElement[]
	 */
	private $customPageElements = [];

	/**
	 *
	 * @var DOMElement
	 */
	protected $currentPageEl = null;

		/**
		 *
		 * @var DOMElement
		 */
	private $currentRevisionEl = null;

	/**
	 *
	 * @param string $destFilepath
	 * @return bool
	 */
	public function buildAndSave( $destFilepath ) {
		$this->build();
		$writtenBytes = $this->dom->save( $destFilepath );
		return $writtenBytes !== false;
	}

	/**
	 * @return void
	 */
	public function reset(): void {
		$this->initDOM();
		$this->pages = [];
		$this->customPageElements = [];
		$this->currentPageEl = null;
		$this->currentRevisionEl = null;
	}

	/**
	 * @return DOMDocument
	 */
	public function build() {
		$this->initDOM();
		foreach ( $this->pages as $pagename => $revisionDatas ) {
			$this->currentPageEl = $this->dom->createElement( 'page' );
			$title = $this->dom->createElement( 'title' );
			$content = $this->dom->createTextNode( $pagename );
			$title->appendChild( $content );
			$this->currentPageEl->appendChild(
				$title
			);
			$this->dom->documentElement->appendChild( $this->currentPageEl );
			foreach ( $revisionDatas as $revisionData ) {
				$this->appendRevisionElement( $revisionData );
			}
		}
	}

	protected function initDOM() {
		$this->dom = new DOMDocument();
		$this->dom->formatOutput = true;
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
	public function addRevision( $pagetitle, $wikitext, $timestamp = '', $username = '',
		$model = '', $format = '' ) {
		if ( !isset( $this->pages[$pagetitle] ) ) {
			$this->pages[$pagetitle] = [];
		}
		$modelAndFormat = $this->getDefaultModelAndFormat( $pagetitle );

		$this->pages[$pagetitle][] = [
			'text' => $wikitext,
			'timestamp' => $timestamp,
			'username' => $username,
			'model' => empty( $model ) ? $modelAndFormat['model'] : $model,
			'format' => empty( $format ) ? $modelAndFormat['format'] : $format
		];

		return $this;
	}

	/**
	 * This is not yet supported by MediaWiki
	 *
	 * @param string $filename
	 * @param string $wikitext
	 * @param string $content
	 * @param string $timestamp
	 * @param string $username
	 * @param string $model
	 * @param string $format
	 * @return void
	 */
	public function addFileRevision( $filename, $wikitext, $content, $timestamp = '', $username = '',
		$model = '', $format = ''
	) {
		$pagetitle = 'File:' . $filename;

		if ( !isset( $this->pages[$pagetitle] ) ) {
			$this->pages[$pagetitle] = [];
		}

		$modelAndFormat = $this->getDefaultModelAndFormat( $pagetitle );

		$this->pages[$pagetitle][] = [
			'text' => $wikitext,
			'timestamp' => $timestamp,
			'username' => $username,
			'model' => empty( $model ) ? $modelAndFormat['model'] : $model,
			'format' => empty( $format ) ? $modelAndFormat['format'] : $format,
			'upload' => [
				'timestamp' => $timestamp,
				'username' => $username,
				'filename' => $filename,
				'contents' => base64_encode( $content )
			]
		];

		return $this;
	}

	/**
	 *
	 * @param string $pagetitle
	 * @return array
	 */
	private function getDefaultModelAndFormat( $pagetitle ) {
		$extension = $this->getNormalExtension( $pagetitle );
		switch ( $extension ) {
			case 'css':
				$model = 'sanitized-css';
				$format = 'text/css';
				break;
			case 'js':
				$model = 'javascript';
				$format = 'text/javascript';
				break;
			case 'json':
				$model = 'json';
				$format = 'application/json';
				break;
			default:
				$model = 'wikitext';
				$format = 'text/x-wiki';
		}

		return [
			'model' => $model,
			'format' => $format
		];
	}

	/**
	 *
	 * @param string $pagetitle
	 * @return string
	 */
	private function getNormalExtension( $pagetitle ) {
		$extension = '';
		$parts = explode( '.', $pagetitle );
		if ( count( $parts ) > 1 ) {
			$lastPart = array_pop( $parts );
			$extension = strtolower( $lastPart );
		}

		return $extension;
	}

	/**
	 *
	 * @param string $pagetitle
	 * @param DOMElement $customEl
	 * @return Builder
	 */
	public function addCustomPageElement( $pagetitle, DOMElement $customEl ) {
		if ( !isset( $this->customPageElements[$pagetitle] ) ) {
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

	/**
	 *
	 * @param array $data
	 * @return void
	 */
	private function appendRevisionElement( $data ) {
		$this->currentRevisionEl = $this->dom->createElement( 'revision' );

		$this->appendRevisionEl( 'username', $this->currentRevisionEl, $data );
		$this->appendRevisionEl( 'timestamp', $this->currentRevisionEl, $data );
		$this->appendRevisionEl( 'model', $this->currentRevisionEl, $data );
		$this->appendRevisionEl( 'format', $this->currentRevisionEl, $data );

		if ( !isset( $data['text'] ) ) {
			$data['text'] = '';
		}
		$textNode = $this->dom->createTextNode( $data['text'] );
		$textEl = $this->dom->createElement( 'text' );
		$textEl->appendChild( $textNode );
		$this->currentRevisionEl->appendChild( $textEl );

		if ( isset( $data['upload'] ) ) {
			$this->appendRevisionUploadEl( $this->currentRevisionEl, $data );
		}
		$this->currentPageEl->appendChild( $this->currentRevisionEl );
	}

	/**
	 *
	 * @param string $nodeName
	 * @param DOMElement $revisionElement
	 * @param array $data
	 * @return void
	 */
	private function appendRevisionEl( $nodeName, $revisionElement, $data ) {
		if ( !isset( $data[$nodeName] ) || empty( $data[$nodeName] ) ) {
			return;
		}
		$el = $this->dom->createElement( $nodeName );
		$content = $this->dom->createTextNode( $data[$nodeName] );
		$el->appendChild( $content );
		$revisionElement->appendChild( $el );
	}

	/**
	 * @param DOMElement $revisionElement
	 * @param array $data
	 * @return void
	 */
	private function appendRevisionUploadEl( $revisionElement, $data ) {
		$nodeName = 'upload';
		if ( !isset( $data[$nodeName] ) || empty( $data[$nodeName] ) ) {
			return;
		}
		$data = $data['upload'];

		$el = $this->dom->createElement( $nodeName );

		$this->appendRevisionEl( 'timestamp', $el, $data );
		$this->appendRevisionEl( 'username', $el, $data );
		$this->appendRevisionEl( 'filename', $el, $data );

		$content = $this->dom->createElement( 'contents' );
		$content->setAttribute( 'encoding', 'base64' );
		$content->nodeValue = $data['contents'];
		$el->appendChild( $content );
		$revisionElement->appendChild( $el );
	}
}
