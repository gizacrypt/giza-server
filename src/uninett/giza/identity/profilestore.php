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
	 * @param Profile $profile	The new profile object
	 *
	 * @return void
	 */
	function store(Profile $profile);

	/**
	 * Get the newest version of all profiles.
	 *
	 * @return Profile[] profiles
	 */
	function getNewestProfiles();

}
