<?php
/*
Plugin Name: Aweber
Plugin URI: http://www.gurucs.com/products/wordpress-aweber-plugin/
Description: Aweber allows you to subscribe people to an aweber list when they register or post a comment to your blog.
Version: 1.0.0
Author: Guru Consulting Services, Inc.
Author URI: http://www.gurucs.com

	Copyright 2008, GuruCS.com

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

include('wpaweber.class.php');
$aweber = & new WPAweber();
$aweber->hook();
?>