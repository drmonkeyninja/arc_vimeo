<?php
$plugin['name'] = 'arc_vimeo';

$plugin['version'] = '1.1';
$plugin['author'] = 'Andy Carter';
$plugin['author_uri'] = 'http://andy-carter.com/';
$plugin['description'] = 'Embed Vimeo videos with customised player';
$plugin['type'] = 0;

@include_once('zem_tpl.php');

if (0) {
	?>
# --- BEGIN PLUGIN HELP ---

h1. arc_vimeo

Easily embed Vimeo videos in articles and customise the appearance of the player.

h2. Table of contents

# "Plugin requirements":#help-section01
# "Installation":#help-section02
# "Tags":#help-section03
# "Examples":#help-section04
# "Author":#help-section05
# "License":#help-section06

h2(#help-section01). Plugin requirements

arc_vimeo's minimum requirements:

* Textpattern 4.5+


h2(#help-section02). Installation

To install go to the 'plugins' tab under 'admin' and paste the plugin code into the 'Install plugin' box, 'upload' and then 'install'. Please note that you will need to set-up a custom field to use for associating videos with articles, unless you choose to directly embed the new tag in the article text.


h2(#help-section03). Tags

h3. arc_vimeo

Embeds a Vimeo video in the page using an iframe.

bc. <txp:arc_vimeo />

h4. Video attributes

* _video_ - Vimeo url or video ID for the video you want to embed
* _custom_ - Name of the custom field containing video IDs/urls associated with article

h4. Basic attributes

* _label_ - Label for the video
* _labeltag_ - Independent wraptag for label
* _wraptag_ - HTML tag to be used as the wraptag, without brackets
* _class_ - CSS class attribute for wraptag

h3. Customising the Vimeo player

You can customise the appearance of the Vimeo player using this plugin to define things like colours and size.

* _width_ - Width of video
* _height_ - Height of video
* _ratio_ - Aspect ratio (defaults 4:3)
* _color_ - A hex colour code for the player UI elements
* _portrait_ - '0' to disable the user's portrait
* _title_ - '0' to disable the video's title
* _byline_ - '0' to disable the video's byline
* _badge_ - '0' to disable the video's badge
* _loop_ - '1' to loop the video on play
* _autoplay_ - '1' to autoplay the video, '0' to turn off autoplay (default)
* _autopause_ - '1' to autopause the video when another is played on the same page

h2. arc_if_vimeo

In addition to arc_vimeo this plugin also comes with arc_if_vimeo, a conditional tag for checking if the video URL is a Vimeo one.

bc. <txp:arc_if_vimeo video="[URL]"></txp:arc_if_vimeo>

h4. Attributes

Use one or the other of the following:-

* _custom_ - Name of the custom field containing video IDs/urls associated with article
* _video_ - A URL to check if it is a valid Vimeo URL

h2(#help-section04). Examples

h3. Example 1: Use custom field to associate video with an article

bc. <txp:arc_vimeo custom="Vimeo" />

h3. Example 2: Set the size of the player

bc. <txp:arc_vimeo video="https://vimeo.com/86295452" width="500" ratio="16:9" />

h3. Example 3: Using the conditional tag

bc.. <txp:arc_if_vimeo video="https://vimeo.com/86295452">
	Yes
<txp:else />
	No
</txp:arc_if_vimeo>

h2(#help-section05). Author

"Andy Carter":http://andy-carter.com. For other Textpattern plugins by me visit my "Plugins page":http://andy-carter.com/txp.

Contributors: Andy Carter and Kevin Ashworth.

Thanks to "Kevin Ashworth":http://kevinashworth.com/ for pointing out several bugs with the plugin.

h2(#help-section06). License

The MIT License (MIT)

Copyright (c) 2014 Andy Carter

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

function arc_vimeo($atts, $thing)
{
	global $thisarticle;

	$defaults = array(
		'video'     => '',
		'custom'    => 'Vimeo ID',
		'width'     => '0',
		'height'    => '0',
		'ratio'		=> '4:3',
		'color'		=> null,
		'portrait'	=> null,
		'title'		=> null,
		'byline'	=> null,
		'badge'		=> null,
		'loop'		=> null,
		'autopause'	=> null,
		'autoplay'	=> null,
		'label'     => '',
		'labeltag'  => '',
		'wraptag'   => '',
		'class'     => __FUNCTION__
	);

	extract(lAtts($defaults, $atts));

    $custom = strtolower($custom);
    if (!$video && isset($thisarticle[$custom])) {
        $video = $thisarticle[$custom];
    }

    $match = _arc_vimeo($video);
    if ($match) {
    	$video = $match;
    } elseif (empty($video)) {
    	return '';
    }

    // If the width and/or height has not been set we want to calculate new
    // ones using the aspect ratio.
    if (!$width || !$height) {
    	
    	// Work out the aspect ratio.
    	preg_match("/(\d+):(\d+)/", $ratio, $matches);
    	if ($matches[0] && $matches[1]!=0 && $matches[2]!=0) {
			$aspect = $matches[1]/$matches[2];
		} else {
			$aspect = 1.333;
		}

		// Calcuate the new width/height.
		if ($width) {
			$height = $width/$aspect;
		} elseif ($height) {
			$width = $height*$aspect;
		} else {
			$width = 425;
			$height = 344;
		}

    }

	$src = '//player.vimeo.com/video/' . $video;

	$qString = array();

	// Check if the player's UI colour is being customised.
	if ($color && preg_match('|^#?([a-z0-9]{6})$|i', $color, $match)) {
		$qString[] = 'color=' . $match[1];
	}

	// Check whether to show or hide the user's portrait from the video.
	if ($portrait!==null) {
		$qString[] = 'portrait=' . ($portrait ? '1' : '0');
	}

	// Check whether to show or hide the video title.
	if ($title!==null) {
		$qString[] = 'title=' . ($title ? '1' : '0');
	}

	// Check whether to show or hide the user's byline.
	if ($byline!==null) {
		$qString[] = 'byline=' . ($byline ? '1' : '0');
	}

	// Check whether to show or hide the badge.
	if ($badge!==null) {
		$qString[] = 'badge=' . ($badge ? '1' : '0');
	}

	// Check whether to play the video on loop.
	if ($loop!==null) {
		$qString[] = 'loop=' . ($loop ? '1' : '0');
	}

	// Check whether to enable/disable autopause.
	if ($autopause!==null) {
		$qString[] = 'autopause=' . ($autopause ? '1' : '0');
	}

	// Check whether to enable/disable autoplay.
	if ($autoplay!==null) {
		$qString[] = 'autoplay=' . ($autoplay ? '1' : '0');
	}

	// Check if we need to append a query string to the video src.
	if (!empty($qString)) {
		$src .= '?' . implode('&amp;', $qString);
	}

	$out = "<iframe src=\"$src\" width=\"$width\" height=\"$height\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";

	return doLabel($label, $labeltag) . (($wraptag) ? doTag($out, $wraptag, $class) : $out);

}

function arc_if_vimeo($atts, $thing)
{
	global $thisarticle;

	extract(lAtts(array(
		'custom' => null,
		'video' => null
	), $atts));

	$result = $video ? _arc_vimeo($video) : _arc_vimeo($thisarticle[strtolower($custom)]);

	return parse(EvalElse($thing, $result));
}

function _arc_vimeo($video)
{
	if (preg_match('#^https?://((player|www)\.)?vimeo\.com(/video)?/(\d+)#i', $video, $matches)) {
    	return $matches[4];
    }

    return false;
}

# --- END PLUGIN CODE ---

?>
