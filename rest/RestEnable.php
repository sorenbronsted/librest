<?php
namespace sbronsted;

/**
 * Interface RestEnable are methods required for making an object rest enable
 */
interface RestEnable {
	/**
	 * Get an object by a given class and uid.
	 * @param $uid
	 * 	The uniq identifier for an object
	 * @return RestEnable
	 * 	The object
	 */
	public static function getByUid(int $uid) : object;

	/**
	 * Get a list of objects by a given class which match all the properties in $qbe (QueryByExample)
	 * @param $qbe
	 * 	A map of properties to match
	 * @param $order
	 * 	An array properties to order by
	 * @return array
	 * 	An array of RestEnable objects
	 */
	public static function getBy(array $qbe, array $order) : iterable;

	/**
	 * Get all objects for an given class
	 * @param $order
	 * 	An array properties to order by
	 * @return array
	 * 	An array of RestEnable objects
	 */
	public static function getAll(array $order) : iterable;

	/**
	 * Set the properties of a given class
	 * @param $data
	 * 	A map properties and values
	 */
	public function setData(array $data) : void ;

	/**
	 * Persist the object
	 */
	public function save() : void ;

	/**
	 * Deletes a given object
	 */
	public function destroy() : void;
}