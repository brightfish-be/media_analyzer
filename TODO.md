# spx_media_analyzer
Analyze media files: audio, video, images, DCPs

- level 0: filesystem metadata (e.g. filename, filesize, filetype)
- level 1: media metadata (by just reading the header information, e.g. codec, resolution)
- level 2: content metadata (by reading the whole file, e.g. audio volume, color palette)

## Development

### Generic
- [ ] use ffprobe output, not ffmpeg

### Video

### Audio
- [ ] audio volume

### Image
- [ ] investigate: use image-magick to analyse files

### DCPs
- [ ] build DCP analysis   


## Testing

### Video
- [ ] test support for > 4GB files

### Audio

### Images
- [ ] test support for RAW files (Canon/Nikon)

##
### Completed Column ✓
- [x] parse ffmpeg information
- [x] Analyze audio: wav/mp3/...
- [x] Analyze video: mp4/avi/mov/...
- [x] Analyze image: png/jpg/gif/ico/...  
