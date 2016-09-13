<?php
namespace Greenfieldr\Tool\Resource;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *  (c) 2016 Marcel Wieser <typo3dev@marcel-wieser.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Resource: File - abstraction over a File in order to get sizes, paths, extension
 * etc from a file. Used by \Greenfieldr\Tool\Resource\FileResourceObjectStorage to allow OO for'
 * files 100% compatible with the way TYPO3 treats files, upload folders and all.
 *
 * @package Tool
 * @subpackage Resource
 */
class FileResource {

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var string
	 */
	protected $targetFilename;

	/**
	 * @var string
	 */
	protected $basename;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $extension;

	/**
	 * @var integer
	 */
	protected $size;

	/**
	 * @var \DateTime
	 */
	protected $modified;

	/**
	 * @var \DateTime
	 */
	protected $created;

	/**
	 * @var string
	 */
	protected $relativePath;

	/**
	 * @var string
	 */
	protected $absolutePath;

	/**
	 * CONSTRUCTOR, takes absolute or relative path to file as only argument
	 *
	 * @param string $filename
	 */
	public function __construct($filename) {
		if (file_exists($filename)) {
			$this->setAbsolutePath($filename);
		} elseif (file_exists(PATH_site . $filename)) {
			$this->setAbsolutePath(PATH_site . $filename);
		}
	}

	/**
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * @param string $filename
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
	}

	/**
	 * @return string
	 */
	public function getTargetFilename() {
		return $this->targetFilename;
	}

	/**
	 * @param string $targetFilename
	 */
	public function setTargetFilename($targetFilename) {
		$this->targetFilename = $targetFilename;
	}

	/**
	 * @return string
	 */
	public function getBasename() {
		return $this->basename;
	}

	/**
	 * @param string $basename
	 */
	public function setBasename($basename) {
		$this->basename = $basename;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * @return string
	 */
	public function getExtension() {
		return $this->extension;
	}

	/**
	 * @param string $extension
	 */
	public function setExtension($extension) {
		$this->extension = $extension;
	}

	/**
	 * @return integer
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @param integer $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}

	/**
	 * @return \DateTime
	 */
	public function getModified() {
		return $this->modified;
	}

	/**
	 * @param \DateTime $modified
	 */
	public function setModified(\DateTime $modified) {
		$this->modified = $modified;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @param \DateTime $created
	 */
	public function setCreated(\DateTime $created) {
		$this->created = $created;
	}

	/**
	 * @return string
	 */
	public function getAbsolutePath() {
		return $this->absolutePath;
	}

	/**
	 * @param string $absolutePath
	 */
	public function setAbsolutePath($absolutePath) {
		$this->created = new \DateTime();
		$this->modified = new \DateTime();
		if (file_exists($absolutePath)) {
			$pathinfo = pathinfo($absolutePath);
			$this->extension = $pathinfo['extension'];
			$this->filename = $pathinfo['filename'];
			$this->basename = $pathinfo['basename'];
			$this->path = $pathinfo['dirname'];
			$this->size = filesize($absolutePath);
			$this->absolutePath = $absolutePath;
			$this->relativePath = str_replace(PATH_site, '', $absolutePath);
			if (method_exists($this->created, 'setTimestamp')) {
				$this->created->setTimestamp(filectime($absolutePath));
				$this->modified->setTimestamp(filemtime($absolutePath));
			}
		}
	}

	/**
	 * @return string
	 */
	public function getRelativePath() {
		return $this->relativePath;
	}

	/**
	 * @param string $relativePath
	 */
	public function setRelativePath($relativePath) {
		$this->relativePath = $relativePath;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getRelativePath();
	}

}
