<?php
/**
 * @group settings
 * @group routing
 */
class BP_Tests_Routing_Settings extends BP_UnitTestCase {
	protected $old_current_user = 0;
	protected $permalink_structure = '';

	public function set_up() {
		parent::set_up();

		$this->old_current_user = get_current_user_id();
		$this->permalink_structure = get_option( 'permalink_structure', '' );
		$this->set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
	}

	public function tear_down() {
		parent::tear_down();
		$this->set_current_user( $this->old_current_user );
		$this->set_permalink_structure( $this->permalink_structure );
	}

	function test_member_settings() {
		$this->set_permalink_structure( '/%postname%/' );
		$this->go_to(
			bp_members_get_user_url(
				bp_loggedin_user_id(),
				array(
					'single_item_component' => bp_rewrites_get_slug( 'members', 'member_settings', bp_get_settings_slug() ),
				)
			)
		);
		$this->assertTrue( bp_is_user_settings_general() );
	}

	function test_member_settings_notifications() {
		$this->set_permalink_structure( '/%postname%/' );
		$this->go_to(
			bp_members_get_user_url(
				bp_loggedin_user_id(),
				array(
					'single_item_component' => bp_rewrites_get_slug( 'members', 'member_settings', bp_get_settings_slug() ),
					'single_item_action'    => bp_rewrites_get_slug( 'members', 'member_settings_notifications', 'notifications' ),
				)
			)
		);
		$this->assertTrue( bp_is_user_settings_notifications() );
	}

	// @todo How best to test this?
	/*function bp_is_user_settings_account_capbilities() {
		$this->set_permalink_structure( '/%postname%/' );
		$this->go_to(
			bp_members_get_user_url(
				bp_loggedin_user_id(),
				array(
					'single_item_component' => bp_rewrites_get_slug( 'members', 'member_settings', bp_get_settings_slug() ),
					'single_item_action'    => bp_rewrites_get_slug( 'members', 'member_settings_capabilities', 'capabilities' ),
				)
			)
		);
	}*/

	function bp_is_user_settings_account_delete() {
		$this->set_permalink_structure( '/%postname%/' );
		$this->go_to(
			bp_members_get_user_url(
				bp_loggedin_user_id(),
				array(
					'single_item_component' => bp_rewrites_get_slug( 'members', 'member_settings', bp_get_settings_slug() ),
					'single_item_action'    => bp_rewrites_get_slug( 'members', 'member_settings_delete_account', 'delete-account' ),
				)
			)
		);
		$this->assertTrue( bp_is_user_settings_account_delete() );
	}
}
