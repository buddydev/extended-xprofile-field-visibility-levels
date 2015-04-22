<?php

/**
 * Plugin Name: Extended XProfile Field Visibility Levels
 * Version: 1.0
 * Author: Brajesh Singh
 * Author URI: http://buddydev.com
 * 
 * Description: An example plugin to showcase how to extend/manipulate BuddyPress profile visibility levels
 */

/**
 * Filter visibility level and add/remove the visibility levels
 */
add_filter( 'bp_xprofile_get_visibility_levels', 'buddydev_customize_profile_visibility' );

function buddydev_customize_profile_visibility( $allowed_visibilities ) {
	
	/**
	 * By default, $allowed_visibilities is an associative array of visibility level key and values like this
	 * 
	 */
	/*
		array(
			'public' => array(
				'id'	  => 'public',
				'label' => _x( 'Everyone', 'Visibility level setting', 'buddypress' )
			),
			'adminsonly' => array(
				'id'	  => 'adminsonly',
				'label' => _x( 'Only Me', 'Visibility level setting', 'buddypress' )
			),
			'loggedin' => array(
				'id'	  => 'loggedin',
				'label' => _x( 'All Members', 'Visibility level setting', 'buddypress' )
			),
		
			'friends'	=> array(
				'id'	=> 'friends',
				'label'	=> _x( 'My Friends', 'Visibility level setting', 'buddypress' )
			)
		);
	*/
	
	//as you can see the keys, that's public|adminsonly|loggedin|friends
	
	//if you want to remove an existing privacy level,
	//just unset the key 
	
	//no more friends privacy
	//unset( $allowed_visibilities['friends'] );
	
	//add a custom visibility 
	//let us add a groups only
	if( bp_is_active( 'groups' ) ) {
	
		$allowed_visibilities['groups'] = array(
			'id'	=> 'groups',
			'label'	=> _x( 'My Group Members', 'Visibility level setting', 'bp-extended-profile-visibility' )
		);
		
	}	
	
	return $allowed_visibilities;
}

//now do some magic

add_filter( 'bp_xprofile_get_hidden_field_types_for_user', 'buddydev_get_hidden_visibility_types_for_user', 10, 3 );
		
function buddydev_get_hidden_visibility_types_for_user( $hidden_levels, $displayed_user_id, $current_user_id ) {

	//if it is not my data and super admin is not viewing and there are no common groups, then hide
	if( ( $displayed_user_id != $current_user_id ) &&  ! buddydev_user_has_common_group( $displayed_user_id, $current_user_id ) && ! is_super_admin() ) {
		
		$hidden_levels[] = 'groups'; //profile field with this privacy level will be hidden for the user
		
	}
	
	return $hidden_levels;
	
}
/**
 * Check if there are any common groups between these two users
 * 
 * @param int $user_id
 * @param int $other_id
 * @return boolean true if ye, else false
 */
function buddydev_user_has_common_group( $user_id, $other_id ) {
	
	$common_groups = buddydev_get_common_groups( $user_id, $other_id );
	
	if( ! empty( $common_groups ) )
		return true;
	
	return false;
	
}
/**
 * Get all the common groups between these two users
 * 
 * @global type $wpdb
 * @param int $user_id
 * @param int $other_id
 * @return mixed array of group ids 
 */
function buddydev_get_common_groups( $user_id, $other_id ) {
	
	global $wpdb;
	
	//members table
	$table = buddypress()->groups->table_name_members;
	
	$query_user_groups = $wpdb->prepare( "SELECT group_id FROM {$table} WHERE user_id = %d AND is_confirmed = %d ", $user_id, 1 );
	$query_other_user_groups = $wpdb->prepare( "SELECT group_id FROM {$table} WHERE user_id = %d AND is_confirmed = %d ", $other_id, 1 );
	
//should we put a LIMIT clause too, I am not putting as someone may find this function useful for other purposes
	$commpon_groups = $wpdb->get_col( "{$query_user_groups} AND group_id IN ({$query_other_user_groups})" );
	
	
	return $commpon_groups;
}
