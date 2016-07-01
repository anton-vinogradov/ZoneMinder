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
<div id="pageBlack"
     style="margin:auto; position:absolute; z-index: 1; background:#000000; width:2000px; height:2000px"></div>
<div id="page0" style="margin:auto; position:absolute; top: 20px; z-index: 20; left: 50%; margin-left: +350px;">
    <a href="index.php?view=watch2&amp;mid=1" style="font-size: 200px; color:#222222;">*</a>
</div>
<div id="page1" style="margin:auto; position:absolute; top: 20px; z-index: 20; left: 50%; margin-left: -150px;">
    <a href="index.php?view=watch3&amp;mid=1" style="font-size: 200px; color:#000000;">*</a>
</div>
<div id="page" style="margin:auto; position:absolute; top: -265px; left: -150px; z-index: 2;">
    <?php outputImageStream("liveStream", $streamSrc, reScale($monitor->Width(), 175), reScale($monitor->Height(), 140), $monitor->Name()); ?>
</div>
<div id="page" style="margin:auto; position:absolute; top: 0px; left: 0px; z-index: 18;">
    <table cellpadding=0 cellspacing=0 width=300
           style="border:solid 1px #000000;font-family:Arial;font-size:18px;background-color:#000000">
        <tr>
            <td valign=top style="padding:0;">
                <iframe
                    src="http://rp5.ru/htmla.php?id=7285&lang=ru&um=00001&bg=%23000000&ft=%23ffffff&fc=%23000000&c=%23ffffff&f=Arial&s=24&sc=4"
                    width=100% height=500 frameborder=0 scrolling=no style="margin:0;"></iframe>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
