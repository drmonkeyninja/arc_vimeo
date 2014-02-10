<?php
$plugin['name'] = 'arc_vimeo';

$plugin['version'] = '1.0';
$plugin['author'] = 'Andy Carter';
$plugin['author_uri'] = 'http://andy-carter.com/';
$plugin['description'] = 'Embed Vimeo videos with customised player';
$plugin['type'] = 0;

@include_once('zem_tpl.php');

if (0) {
	?>
# --- BEGIN PLUGIN HELP ---

h1. arc_vimeo

h2. Description

Easily embed Vimeo videos in articles and customise the appearance of the player.


h2. Installation

To install go to the 'plugins' tab under 'admin' and paste the plugin code into the 'Install plugin' box, 'upload' and then 'install'. Please note that you will need to set-up a custom field to use for associating videos with articles, unless you choose to directly embed the new tag in the article text.


h2. Syntax

bc.. <txp:arc_vimeo />

<txp:arc_vimeo video='86295452' width='500' ratio='16:9' />

<txp:arc_vimeo video='http://vimeo.com/86295452' width='500' ratio='16:9' />

h2. Usage

h3. Video

|_. Attribute|_. Description|_. Default|_. Example|
|video|Vimeo url or video ID for the video you want to embed| _unset_|video='86295452'|
|custom|Name of the custom field containing video IDs/urls associated with article|Vimeo ID|custom='video'|

h3. Basics

|_. Attribute|_. Description|_. Default|_. Example|
|label|Label for the video| _no label_|label='Vimeo video'|
|labeltag|Independent wraptag for label| _empty_|labeltag='h3'|
|wraptag|HTML tag to be used as the wraptag, without brackets| _unset_|wraptag='div'|
|class|CSS class attribute for wraptag|arc_vimeo|class='vimeo'|

h3. Customising the Vimeo player

You can customise the appearance of the Vimeo player using this plugin to define things like colours and size.

|_. Attribute|_. Description|_. Default|_. Example|
|width|Width of video|0|width='200'|
|height|Height of video|0|height='150'|
|ratio|Aspect ratio|4:3|ration='16:9'|
|color|A hex colour code for the player UI elements|00adef|color='ff6500'|
|portrait|'0' to disable the user's portrait|1| |
|title|'0' to disable the video's title|1| |
|byline|'0' to disable the video's byline|1| |
|badge|'0' to disable the video's badge|1| |
|loop|'1' to loop the video on play|0| |
|autoplay|'1' to autoplay the video, '0' to turn off autoplay (default)|0| |
|autopause|'1' to autopause the video when another is played on the same page|0| |

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
    if ($video && isset($thisarticle[$custom])) {
        $video = $thisarticle[$custom];
    }

    $match = _arc_is_vimeo($video);
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

	$out = "<iframe src='$src' width='$width' height='$height' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";

	return doLabel($label, $labeltag) . (($wraptag) ? doTag($out, $wraptag, $class) : $out);

}

function arc_if_vimeo($atts, $thing)
{
	extract(lAtts(array(
		'video' => ''
	), $atts));

	return parse(EvalElse($thing, _arc_is_vimeo($video)));
}

function _arc_is_vimeo($video)
{
	if (preg_match('#^http://((player|www)\.)?vimeo\.com(/video)?/(\d+)#i', $video, $matches)) {
    	return $matches[4];
    }

    return false;
}

# --- END PLUGIN CODE ---

?>
