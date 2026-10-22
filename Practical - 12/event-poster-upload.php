<?php
/** Uploaded poster validator; intended to be required by event-form.php only. */
declare(strict_types=1);
const EVENT_POSTER_MAX_BYTES = 2 * 1024 * 1024; // 2 MiB

/** Returns new relative path, null if no new upload, and errors by reference. */
function storeValidatedEventPoster(array &$errors): ?string {
    if (!isset($_FILES['poster']) || !is_array($_FILES['poster'])) return null;
    $file=$_FILES['poster'];
    $code=$file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($code === UPLOAD_ERR_NO_FILE) return null;
    if (!is_int($code) || $code !== UPLOAD_ERR_OK) {
        $errors[] = $code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE
            ? 'Poster exceeds the upload limit (maximum 2 MB).'
            : 'Poster upload failed. Select a JPG, PNG or WebP image and retry.';
        return null;
    }
    $tmp=$file['tmp_name'] ?? '';
    $size=$file['size'] ?? 0;
    if (!is_string($tmp) || !is_uploaded_file($tmp) || !is_int($size) || $size < 1 || $size > EVENT_POSTER_MAX_BYTES) {
        $errors[]='Poster must be a valid uploaded image not larger than 2 MB.';
        return null;
    }
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $supported=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!is_string($mime) || !isset($supported[$mime])) {
        $errors[]='Invalid poster file type. Only JPG, PNG and WebP images are allowed.';
        return null;
    }
    // Confirm actual decodable image header (not filename/Content-Type alone).
    $imageInfo=@getimagesize($tmp);
    $expectedImageTypes=['image/jpeg'=>IMAGETYPE_JPEG,'image/png'=>IMAGETYPE_PNG,'image/webp'=>IMAGETYPE_WEBP];
    if (!is_array($imageInfo) || ($imageInfo[2] ?? null) !== $expectedImageTypes[$mime]
        || $imageInfo[0]<100 || $imageInfo[1]<100 || $imageInfo[0]>5000 || $imageInfo[1]>5000) {
        $errors[]='Poster image is invalid; dimensions must be between 100 and 5000 pixels.';
        return null;
    }
    $folder=__DIR__.DIRECTORY_SEPARATOR.'event-posters';
    if (!is_dir($folder) || !is_writable($folder)) {
        $errors[]='Upload folder unavailable. Check event-posters folder and XAMPP permissions.';
        return null;
    }
    $filename=bin2hex(random_bytes(16)).'.'.$supported[$mime];
    if (!move_uploaded_file($tmp,$folder.DIRECTORY_SEPARATOR.$filename)) {
        $errors[]='Unable to save uploaded poster. Check write permissions.';
        return null;
    }
    return 'event-posters/'.$filename;
}
function deleteOwnedEventPoster(?string $path): void {
    if (!is_string($path) || !preg_match('~^event-posters/[a-f0-9]{32}\.(jpg|png|webp)$~D',$path)) return;
    $full=__DIR__.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$path);
    if (is_file($full)) @unlink($full);
}
