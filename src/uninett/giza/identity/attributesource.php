<?php namespace uninett\giza\identity;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
interface AttributeSource {

	/**
	 * Return the identity which is identified by a Unique IDentifier.
	 * This is generally used for adding attributes to a profile.
	 *
	 * @param AttributeAssertion[] $previousAssertions
	 * @return AttributeAssertion
	 */
	function getAssertionFor(array $previousAssertions);

	/**
	 * Return the identity of the currently authenticated user.
	 * If no user is authenticated, this method may either redirect and not return,
	 * or it may return NULL, depending on the source's ability to authenticate users.
	 *
	 * @return AttributeAssertion assertion with only uid
	 */
	function getAuthenticationAssertion();

}
