<?php

class SiteTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testIndexSite() {
		$response = $this->call('GET', '/');

		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testRobotsSite() {
		$response = $this->call('GET', 'robots.txt');
		$this->assertEquals('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
	}

}
