<?php
//
// ZoneMinder web watch feed view file, $Date$, $Revision$
// Copyright (C) 2001-2008 Philip Coombes
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//

require_once('includes/Monitor.php');

if ( !canView( 'Stream' ) )
{
    $view = "error";
    return;
}

// This is for input sanitation
$mid = intval( $_REQUEST['mid'] );
if ( ! visibleMonitor( $mid ) ) {
    $view = "error";
    return;
}

$sql = 'SELECT C.*, M.* FROM Monitors AS M LEFT JOIN Controls AS C ON (M.ControlId = C.Id ) WHERE M.Id = ?';
$monitor = new Monitor( $mid );
#dbFetchOne( $sql, NULL, array( $_REQUEST['mid'] ) );

if ( isset($_REQUEST['showControls']) )
    $showControls = validInt($_REQUEST['showControls']);
else
    $showControls = (canView( 'Control' ) && ($monitor->DefaultView() == 'Control'));

$showPtzControls = ( ZM_OPT_CONTROL && $monitor->Controllable() && canView( 'Control' ) );

if ( isset( $_REQUEST['scale'] ) )
    $scale = validInt($_REQUEST['scale']);
else
    $scale = reScale( SCALE_BASE, $monitor->DefaultScale, ZM_WEB_DEFAULT_SCALE );

$connkey = generateConnKey();

if ( ZM_WEB_STREAM_METHOD == 'mpeg' && ZM_MPEG_LIVE_FORMAT )
{
    $streamMode = "mpeg";
    $streamSrc = $monitor->getStreamSrc( array( "mode=".$streamMode, "scale=".$scale, "bitrate=".ZM_WEB_VIDEO_BITRATE, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "format=".ZM_MPEG_LIVE_FORMAT ) );
}
elseif ( canStream() )
{
    $streamMode = "jpeg";
    $streamSrc = $monitor->getStreamSrc( array( "mode=".$streamMode, "scale=".$scale, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "buffer=".$monitor->StreamReplayBuffer() ) );
}
else
{
    $streamMode = "single";
    $streamSrc = $monitor->getStreamSrc( array( "mode=".$streamMode, "scale=".$scale ) );
    Info( "The system has fallen back to single jpeg mode for streaming. Consider enabling Cambozola or upgrading the client browser.");
}

$showDvrControls = ( $streamMode == 'jpeg' && $monitor->StreamReplayBuffer() != 0 );

noCacheHeaders();

xhtmlHeaders( __FILE__, $monitor->Name()." - ".translate('Feed') );
?>
<body>
<div id="page0" style="margin:auto; position:absolute; top: 0px; z-index: 20; left: 50%; margin-left: +350px;">
    <a href="index.php?view=watch2&amp;mid=1" style="font-size: 150px; color:#222222;">&#8635;</a>
</div>
<div id="page1" style="margin:auto; position:absolute; top: 390px; z-index: 20; left: 50%; margin-left: +380px;">
    <a href="index.php?view=watch3&amp;mid=1" style="font-size: 150px; color:#000000;">&#10006;</a>
</div>
<div id="page" style="margin:auto; position:absolute; top: -265px; left: -150px; z-index: 2;">
    <?php outputImageStream("liveStream", $streamSrc, reScale($monitor->Width(), 175), reScale($monitor->Height(), 140), $monitor->Name()); ?>
</div>

<?php
$output = file_get_contents('http://www.rp5.ru/%D0%9F%D0%BE%D0%B3%D0%BE%D0%B4%D0%B0_%D0%B2_%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3%D0%B5');
$output = str_replace("=\"/", "=\"http://rp5.ru/", $output);
$output = str_replace('style="display: none;"', '', $output);
$pattern = '/(\<table id\=\"forecastTable\_3(.+?)\/table\>)/is';
preg_match($pattern, $output, $output);

header('Content-Type: text/html; charset=utf-8');
?>

<div id="page4" style="margin:auto; position:absolute; top: -7px; left: -95px; z-index: 18; overflow: hidden; width: 485px">
    <?php
    echo $output[0];
    ?>
</div>
</body>
<script type='text/javascript'>
    function reload_it() {
        window.location.href='index.php?view=watch2&mid=1&rando=<?php echo rand(5, 999999); ?>';
    }
    setInterval("reload_it()", 1800000);
</script>
</html>
