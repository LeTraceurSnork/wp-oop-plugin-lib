<?php
/**
 * Tests for Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Get_Request
 *
 * @package wp-oop-plugin-lib
 */

namespace Felix_Arntz\WP_OOP_Plugin_Lib\PHPUnit\Tests\HTTP;

use Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Get_Request;
use Felix_Arntz\WP_OOP_Plugin_Lib\PHPUnit\Includes\Test_Case;

/**
 * @group http
 */
class Get_Request_Tests extends Test_Case {

	public function test_constructor() {
		$request = new Get_Request( 'https://my-api.com/v1/entries/3' );
		$this->assertSame( Request::GET, $request->get_method() );
	}
}
