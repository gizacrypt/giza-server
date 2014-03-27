<?php namespace uninett\giza\identity;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
interface ProfileStore extends AttributeSource {

	/**
	 * Store a profile. If the uid already exists in the store,
	 * it is overwritten. If it doesn't exist, a new entry is created.
	 *
	 * @param MutableAttributeAssertion $assertion	The new profile object
	 *
	 * @return void
	 */
	function store(Profile $assertion);

	/**
	 * Remove a profile from the store.
	 *
	 * @param MutableAttributeAssertion $assertion	The profile to remove
	 *
	 * @return void
	 */
	function remove(Profile $assertion);

}
