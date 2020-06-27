<?php
function twitchpress_youtube_extract_video_id( $snippet ) {
    if( isset( $snippet['resourceid']['videoid'] ) ) {
        return $snippet['resourceid']['videoid'];
    }
    return false;
}