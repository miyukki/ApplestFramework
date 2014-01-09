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

		$hash1 = array('key1' => array('subkey1' => 'subvalue1'), 'key2' => 'value2');
		$not_hash1 = array();
		$not_hash2 = array('1');
		$not_hash3 = array('value1', 'value2');
		$not_hash4 = array('0' => 'value1', '1' => 'value2');
		Test::is(Util::is_hash($hash1), true, 'Util::is_hash() test1');
		// Test::is(Util::is_hash($not_hash1), false, 'Util::is_hash() test2');
		Test::is(Util::is_hash($not_hash2), false, 'Util::is_hash() test3');
		Test::is(Util::is_hash($not_hash3), false, 'Util::is_hash() test4');
		Test::is(Util::is_hash($not_hash4), false, 'Util::is_hash() test5');

		Test::is(Util::extension_remove('picture.jpg'), 'picture', 'Util::extension_remove() test1');
		Test::is(Util::extension_remove('picture.0.0.jpg'), 'picture', 'Util::extension_remove() test2');

		Test::is(Util::convert_snake_case('UserSource'), 'user_source', 'Util::convert_snake_case() test1');
		Test::is(Util::convert_snake_case('getUserSource'), 'get_user_source', 'Util::convert_snake_case() test2');

		Test::is(Util::tableize('User'), 'users', 'Util::tableize() test1');
		Test::is(Util::tableize('UserEvent'), 'user_events', 'Util::tableize() test2');

		// Test::is(Util::untableize('users'), 'User', 'Util::untableize() test1');
		// Test::is(Util::untableize('user_events'), 'UserEvent', 'Util::untableize() test2');

		
		// Test::is(UserModel::find_by_id($user2_id)->name, 'Mike', 'Util::is_hash() test1');
		// $needle1 = TestModel::create();
		// $needle2 = TestModel::create();


		if(Test::is_fail()) {
			exit(1);
		}
	}
}
dispatchAction('TestController');
