<?php
namespace App;

class Sitemap {

	private $doc;
	private $urlset;
	private $prefix;
	private $debug;
	private $debug_data;

	public function __construct($prefix = '', $debug = false) {
		$this->doc = new \DomDocument('1.0', 'utf-8');
		$this->urlset = $this->doc->createElement('urlset');
		$this->urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$this->urlset->setAttribute('create_date', date('Y-m-d H:i:s'));
		$this->prefix = $prefix;
		$this->debug = $debug;
	}

	public function add_url($loc, $lastmod = '', $changefreq = '', $priority = '') {
		if ($this->debug) {
			$this->debug_data[] = $loc;
		}
		$url = $this->doc->createElement('url');
		$this->check_loc($loc);
		$loc = $this->doc->createElement('loc', $this->prefix . $loc);
		$url->appendChild($loc);
		if (!empty($lastmod)) {
			$lastmod = $this->doc->createElement('lastmod', $lastmod);
			$url->appendChild($lastmod);
		}
		if (!empty($changefreq)) {
			$changefreq = $this->doc->createElement('changefreq', $changefreq);
			$url->appendChild($changefreq);
		}
		if (!empty($priority)) {
			$priority = $this->doc->createElement('priority', $priority);
			$url->appendChild($priority);
		}
		$this->urlset->appendChild($url);
	}

	private function commit() {
		$this->doc->appendChild($this->urlset);
	}

	public function save($filename = 'sitemap.xml') {
		$this->commit();
		@unlink(public_path($filename));

		$this->doc->save(public_path($filename));
	}

	public function get_raw() {
		$this->commit();

		return $this->doc->saveXML();
	}

	private function check_loc(&$loc) {
		if (!empty($loc) && !empty($this->prefix)) {
			$len = strlen($this->prefix) - 1;
			if ($this->prefix[$len] != '/' && $loc[0] != '/') {
				$loc = '/' . $loc;
			}
		}
	}

	public function get_debug() {
		return $this->debug_data;
	}
}
