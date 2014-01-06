<?php
require(dirname(__FILE__).'/../Core.php');

class TestController extends Controller {
	public function exec() {
		// Model
		$user1 = UserModel::create();
		$user1->name = 'Jane';
		$user1_id = $user1->save();

		$user2 = UserModel::create();
		$user2->name = 'Mike';
		$user2_id = $user2->save();

		Test::is(UserModel::find_by_id($user1_id)->name, 'Jane', 'Find a user test 1');
		Test::is(UserModel::find_by_id($user2_id)->name, 'Mike', 'Find a user test 2');

		if(Test::is_fail()) {
			exit(1);
		}
	}
}
dispatchAction('TestController');
