<?php 
/* 
Plugin Name: FCK-Firefox-Fixer 
Plugin URI: http://www.latentmotion.com/fck-firefox-fix/
Description: Cleans up erroneous and invalidating javascript injection by FireFox, presumably firebug or other addons. Specifically, the inputs it cleans up are: " <input id="gwProxy" type="hidden" /> " and " <input id="jsProxy" onclick="jsCall();" type="hidden" /> ".
Author: Brett Barros
Author URI: http://www.latentmotion.com
Version: 1.0

	Inspired by Politeifier by Elliott Back --> http://elliottback.com/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/ 

function fckFix($content){
	$fixedPost = str_replace("<input id=\"gwProxy\" type=\"hidden\" />", "", $content);
	$fixedPost = str_replace("<input id=\"jsProxy\" onclick=\"jsCall();\" type=\"hidden\" />", "", $fixedPost);
	return $fixedPost;
}

add_filter('the_content', 'fckFix', 19);
?>