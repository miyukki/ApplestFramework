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

		if(UserModel::find_by_id($user1_id)->name !== 'Jane') {
			echo 'Endpoint 1';
			exit(1);
		}
		if(UserModel::find_by_id($user2_id)->name !== 'Mike') {
			echo 'Endpoint 2';
			exit(1);
		}
	}
}
dispatchAction('TestController');
