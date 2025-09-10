<?php

namespace App\Media;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Video;

class CustomVideoGenerator extends Video
{
    public function convert(string $file, Conversion $conversion = null): string
    {
        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('medialibrary.ffmpeg_path'),
            'ffprobe.binaries' => config('medialibrary.ffprobe_path'),
        ]);

        $video = $ffmpeg->open($file);
        $duration = $ffmpeg->getFFProbe()->format($file)->get('duration');

        $seconds = $conversion ? $conversion->getExtractVideoFrameAtSecond() : 0;
        $seconds = $duration < $seconds ? 0 : $seconds;

        $frame = $video->frame(TimeCode::fromSeconds($seconds));
        $frame->save($imageFile);

        return $imageFile;
    }
}
