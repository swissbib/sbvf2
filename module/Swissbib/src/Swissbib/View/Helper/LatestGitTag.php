<?php
namespace Swissbib\View\Helper;

use Zend\View\Helper\AbstractHelper;


/**
 * Read-out latest git tag
 *
 * Class GitTagNumber
 * @package Swissbib\View\Helper
 */
class LatestGitTag extends AbstractHelper
{

	/**
	 * Get tab specific template path if present
	 *
	 * @return	String
	 */
	public function __invoke()
	{
		$latestTag	= '';
		$latestFileModTime = 0;
		$path	= '.git\\refs\\tags\\';
		$tagFiles	= scandir($path);

		foreach( $tagFiles as $filename) {
			if(str_replace('.', '', $filename) !== '') {
				$fileModTime = filemtime($path . $filename);
				if($fileModTime > $latestFileModTime) {
					$latestFileModTime = $fileModTime;
					$latestTag = $filename;
				}
			}
		}

		return !empty($latestTag) ? 'Tag ' . $latestTag : '';
	}

}
