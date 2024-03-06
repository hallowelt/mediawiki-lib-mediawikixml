<?php

namespace HalloWelt\MediaWiki\Lib\MediaWikiXML;

class UserBuilder extends Builder {

	/** @var array */
	private $users = [];

	public function build() {
		$this->initDOM();
		foreach ( $this->users as $user ) {
			$this->currentPageEl = $this->dom->createElement( 'user' );

			$this->addElement( $user, 'name' );
			$this->addElement( $user, 'realName' );
			$this->addElement( $user, 'email' );
			$this->addElement( $user, 'groups' );

			$this->dom->documentElement->appendChild( $this->currentPageEl );
		}
	}

	/**
	 * @param array $user
	 * @param string $element
	 */
	private function addElement( $user, $element ) {
		$nameElement = $this->dom->createElement( $element );
		$userName = $this->dom->createTextNode( $user[$element] );
		$nameElement->appendChild( $userName );
		$this->currentPageEl->appendChild( $nameElement );
	}

	/**
	 * @param string $name
	 * @param string $realName
	 * @param string $email
	 * @param array $groups
	 */
	public function addUser( $name, $realName, $email, $groups ) {
		$this->users[] = [
			'name' => $name,
			'realName' => $realName,
			'email' => $email,
			'groups' => $groups
		];
	}
}
