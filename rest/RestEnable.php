<?php

/**
 * Interface RestEnable are methods required for makeing an object rest enable
 */
interface RestEnable {
	/**
	 * Get an object by a given class and uid.
	 * @param $uid uniq identifier for an object
	 * @return RestEnable object
	 */
	public static function getByUid($uid);

	/**
	 * Get a list of objects by a given class which match all the properties in $qbe (QueryByExample)
	 * @param $qbe a map of properties to match
	 * @param $order an array properties to order by
	 * @return array of RestEnable objects
	 */
	public static function getBy(array $qbe, array $order);

	/**
	 * Get all objects for an given class
	 * @param $order an array properties to order by
	 * @return array of RestEnable objects
	 */
	public static function getAll(array $order);

	/**
	 * Set the properties of a given class
	 * @param $data a map properties and values
	 * @return none
	 */
	public function setData(array $data);

	/**
	 * Persist the object
	 * @return map with the uid
	 */
	public function save();
}