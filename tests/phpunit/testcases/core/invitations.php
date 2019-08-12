<?php

include_once BP_TESTS_DIR . 'assets/invitations-extensions.php';

/**
 * @group core
 * @group invitations
 */
 class BP_Tests_Invitations extends BP_UnitTestCase {
	public function test_bp_invitations_add_invitation_vanilla() {
		$old_current_user = get_current_user_id();

		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		$invites_class = new BPTest_Invitation_Manager_Extension();

		// Create a couple of invitations.
		$invite_args = array(
			'user_id'           => $u3,
			'inviter_id'		=> $u1,
			'item_id'           => 1,
			'send_invite'       => 'sent',
		);
		$i1 = $invites_class->add_invitation( $invite_args );
		$invite_args['inviter_id'] = $u2;
		$i2 = $invites_class->add_invitation( $invite_args );

		$get_invites = array(
			'user_id'        => $u3,
			'fields'         => 'ids',
		);
		$invites = $invites_class->get_invitations( $get_invites );
		$this->assertEqualSets( array( $i1, $i2 ), $invites );

		$this->set_current_user( $old_current_user );
	}

	public function test_bp_invitations_add_invitation_avoid_duplicates() {
		$old_current_user = get_current_user_id();

		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		$invites_class = new BPTest_Invitation_Manager_Extension();

		// Create an invitation.
		$invite_args = array(
			'user_id'           => $u2,
			'inviter_id'		=> $u1,
			'item_id'           => 1,
			'send_invite'       => 'sent',
		);
		$i1 = $invites_class->add_invitation( $invite_args );
		// Attempt to create a duplicate.
		$this->assertFalse( $invites_class->add_invitation( $invite_args ) );

		$this->set_current_user( $old_current_user );
	}

	public function test_bp_invitations_add_invitation_invite_plus_request_should_accept() {
		$old_current_user = get_current_user_id();

		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		$invites_class = new BPTest_Invitation_Manager_Extension();

		// Create an invitation.
		$invite_args = array(
			'user_id'           => $u3,
			'inviter_id'		=> $u1,
			'item_id'           => 1,
			'send_invite'       => 'sent',
		);
		$i1 = $invites_class->add_invitation( $invite_args );

		// Create a request.
		$request_args = array(
			'user_id'           => $u3,
			'item_id'           => 1,
		);
		$r1 = $invites_class->add_request( $request_args );

		$get_invites = array(
			'user_id'          => $u3,
			'accepted'         => 'accepted'
		);
		$invites = $invites_class->get_invitations( $get_invites );
		$this->assertEqualSets( array( $i1 ), wp_list_pluck( $invites, 'id' ) );

		$this->set_current_user( $old_current_user );
	}

	public function test_bp_invitations_add_invitation_unsent_invite_plus_request_should_not_accept() {
		$old_current_user = get_current_user_id();

		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		$invites_class = new BPTest_Invitation_Manager_Extension();

		// Create an invitation.
		$invite_args = array(
			'user_id'           => $u3,
			'inviter_id'		=> $u1,
			'item_id'           => 1,
			'send_invite'       => 0,
		);
		$i1 = $invites_class->add_invitation( $invite_args );

		// Create a request.
		$request_args = array(
			'user_id'           => $u3,
			'item_id'           => 1,
		);
		$r1 = $invites_class->add_request( $request_args );

		$get_invites = array(
			'user_id'          => $u3,
			'accepted'         => 'accepted'
		);
		$invites = $invites_class->get_invitations( $get_invites );
		$this->assertEqualSets( array(), wp_list_pluck( $invites, 'id' ) );

		$this->set_current_user( $old_current_user );
	}

	public function test_bp_invitations_add_invitation_unsent_invite_plus_request_then_send_invite_should_accept() {
		$old_current_user = get_current_user_id();

		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$u3 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		$invites_class = new BPTest_Invitation_Manager_Extension();

		// Create an invitation.
		$invite_args = array(
			'user_id'           => $u3,
			'inviter_id'		=> $u1,
			'item_id'           => 1,
			'send_invite'       => 0,
		);
		$i1 = $invites_class->add_invitation( $invite_args );

		// Create a request.
		$request_args = array(
			'user_id'           => $u3,
			'item_id'           => 1,
		);
		$r1 = $invites_class->add_request( $request_args );

		$invites_class->send_invitation_by_id( $i1 );

		// Check that both the request and invitation are marked 'accepted'.
		$get_invites = array(
			'user_id'          => $u3,
			'type'             => 'all',
			'accepted'         => 'accepted',
			'fields'           => 'ids'
		);
		$invites = $invites_class->get_invitations( $get_invites );
		$this->assertEqualSets( array( $i1, $r1 ), $invites );

		$this->set_current_user( $old_current_user );
	}
	
	public function test_bp_invitations_add_request_vanilla() {
		$old_current_user = get_current_user_id();

		$u1 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		$invites_class = new BPTest_Invitation_Manager_Extension();

		// Create a couple of requests.
		$request_args = array(
			'user_id'           => $u1,
			'item_id'           => 7,
		);
		$r1 = $invites_class->add_request( $request_args );
		$request_args['item_id'] = 4;
		$r2 = $invites_class->add_request( $request_args );

		$get_requests = array(
			'user_id'           => $u1,
			'fields'            => 'ids'
		);
		$requests = $invites_class->get_requests( $get_requests );
		$this->assertEqualSets( array( $r1, $r2 ), $requests );

		$this->set_current_user( $old_current_user );
	}

	public function test_bp_invitations_add_request_avoid_duplicates() {
		$old_current_user = get_current_user_id();

		$invites_class = new BPTest_Invitation_Manager_Extension();

		$u1 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		// Create a couple of requests.
		$request_args = array(
			'user_id'           => $u1,
			'item_id'           => 7,
		);
		$r1 = $invites_class->add_request( $request_args );
		// Attempt to create a duplicate.
		$this->assertFalse( $invites_class->add_request( $request_args ) );

		$this->set_current_user( $old_current_user );
	}

	public function test_bp_invitations_add_request_request_plus_sent_invite_should_accept() {
		$old_current_user = get_current_user_id();

		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		$invites_class = new BPTest_Invitation_Manager_Extension();

		// Create a request.
		$request_args = array(
			'user_id'           => $u2,
			'item_id'           => 1,
		);
		$r1 = $invites_class->add_request( $request_args );

		// Create an invitation.
		$invite_args = array(
			'user_id'           => $u2,
			'inviter_id'		=> $u1,
			'item_id'           => 1,
			'send_invite'       => 1,
		);
		$i1 = $invites_class->add_invitation( $invite_args );

		// Check that both the request and invitation are marked 'accepted'.
		$get_invites = array(
			'user_id'          => $u2,
			'type'             => 'all',
			'accepted'         => 'accepted',
			'fields'           => 'ids'
		);
		$invites = $invites_class->get_invitations( $get_invites );
		$this->assertEqualSets( array( $r1, $i1 ), $invites );

		$this->set_current_user( $old_current_user );
	}

	public function test_bp_invitations_sending_should_clear_cache() {
		$old_current_user = get_current_user_id();

		$u1 = $this->factory->user->create();
		$u2 = $this->factory->user->create();
		$this->set_current_user( $u1 );

		$invites_class = new BPTest_Invitation_Manager_Extension();

		// Create an invitation.
		$invite_args = array(
			'user_id'           => $u2,
			'inviter_id'		=> $u1,
			'item_id'           => 1,
		);
		$i1 = $invites_class->add_invitation( $invite_args );

		$invite = new BP_Invitation( $i1 );
		$this->assertEquals( 0, $invite->invite_sent );

		$invites_class->send_invitation_by_id( $i1 );

		$invite = new BP_Invitation( $i1 );
		$this->assertEquals( 1, $invite->invite_sent );

		$this->set_current_user( $old_current_user );
	}

}
