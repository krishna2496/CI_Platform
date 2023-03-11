<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Traits\RestExceptionHandlerTrait;
use App\Exceptions\BucketNotFoundException;
use App\Exceptions\FileNotFoundException;

class S3Helper
{
    use RestExceptionHandlerTrait;

    /**
     * Upload file on AWS s3 bucket
     *
     * @param string $url
     * @param string $tenantName
     * @param string|null $customPath
     *
     * @return string
     */
    public function uploadFileOnS3Bucket(
        string $url,
        string $tenantName,
        string $customPath = null
    ): string {
        $fileName = $this->generateFilename($url);

        $path = '';
        if ($customPath) {
            $path .= "$customPath/";
        }
        $path .= "$fileName";

        return $this->uploadFile(
            $url,
            $tenantName,
            $path
        );
    }

    /**
     * Get all SCSS files list from S3 bucket
     *
     * @param string $tenantName
     */
    public function getAllScssFiles(string $tenantName)
    {
        if (!Storage::disk('s3')->exists($tenantName)) {
            throw new BucketNotFoundException(
                trans('messages.custom_error_message.ERROR_TENANT_ASSET_FOLDER_NOT_FOUND_ON_S3'),
                config('constants.error_codes.ERROR_TENANT_ASSET_FOLDER_NOT_FOUND_ON_S3')
            );
        }

        $allFiles = Storage::disk('s3')->allFiles($tenantName.'/'.env('AWS_S3_ASSETS_FOLDER_NAME'));
        $scssFilesArray = [];
        $i = $j = 0;

        if (count($allFiles) > 0) {
            foreach ($allFiles as $key => $file) {
                // Only scss and css copy
                if (!strpos($file, "/images") && strpos($file, "/scss")
                && !strpos($file, "custom.scss") && !strpos($file, "assets.scss")) {
                    $scssFilesArray['scss_files'][$i++] = [
                        "scss_file_path" =>
                        'https://' . env('AWS_S3_BUCKET_NAME') . '.s3.' . env('AWS_REGION') . '.amazonaws.com/' . $file,
                        "scss_file_name" => basename($file)
                    ];
                }
                if (strpos($file, "/images") && !strpos($file, "/scss")
                && !strpos($file, "custom.scss") && !strpos($file, "assets.scss")) {
                    $scssFilesArray['image_files'][$j++] = [
                        "image_file_path" =>
                        'https://' . env('AWS_S3_BUCKET_NAME') . '.s3.' . env('AWS_REGION') . '.amazonaws.com/' . $file,
                        "image_file_name" => basename($file)
                    ];
                }
            }
        }
        return $scssFilesArray;
    }

    /**
     * Upload profile image on AWS s3 bucket
     *
     * @param string $avatar
     * @param string $tenantName
     * @param int $userId
     *
     * @return string
     */
    public function uploadProfileImageOnS3Bucket(string $avatar, string $tenantName, int $userId): string
    {
        // Get file type from base64
        $fileOpen = finfo_open();
        $mime_type = finfo_buffer($fileOpen, base64_decode($avatar), FILEINFO_MIME_TYPE);

        $type = explode('/', $mime_type);

        $imagePath = $tenantName.'/profile_images/'.$userId.'_'.time().'.'.$type[1];
        Storage::disk('s3')->put($imagePath, base64_decode($avatar), 'public');
        $filePath =  Storage::disk('s3')->url($imagePath);
        return $filePath;
    }

    /**
     * Upload favicon on AWS s3 bucket
     *
     * @param string $favicon
     * @param string $tenantName
     *
     * @return string
     */
    public function uploadFaviconOnS3Bucket(string $favicon, string $tenantName): string
    {
        $imagePath = $tenantName.'/assets/images/favicon/favicon.ico';
        Storage::disk('s3')->put($imagePath, $favicon, 'public');
        return Storage::disk('s3')->url($imagePath);
    }

    /**
     * Retrieve favicon from AWS s3 bucket
     *
     * @param string $tenantName
     *
     * @return string
     */
    public function retrieveFaviconFromS3Bucket(string $tenantName): ?string
    {
        $assetsFolder = env('AWS_S3_ASSETS_FOLDER_NAME');
        $customFaviconName = config('constants.AWS_S3_CUSTOM_FAVICON_NAME');

        $imagePath = $tenantName . '/'
            . $assetsFolder
            . '/images/favicon/'
            . $customFaviconName;

        if (Storage::disk('s3')->exists($imagePath)) {
            return Storage::disk('s3')->url($imagePath);
        } else {
            return null;
        }
    }

    /**
     * Upload document on AWS s3 bucket
     *
     * @param $file
     * @param string $tenantName
     * @param int $userId
     * @param string $folderName
     * @return string
     */
    public function uploadDocumentOnS3Bucket($file, string $tenantName, int $userId, string $folderName): string
    {
        set_time_limit(0);
        $context = stream_context_create(array('http'=> array(
            'timeout' => 1200
        )));

        $disk = Storage::disk('s3');
        $fileName = preg_replace(
            "/[^A-Za-z0-9\-]/",
            "",
            pathinfo($file->getClientOriginalName())['filename'] . '_' . time()
        );
        $fileExtension = pathinfo($file->getClientOriginalName())['extension'];
        $documentPath = 'users/'
            . $userId
            . '/'
            . $folderName
            . '/'
            . $fileName . '.' . $fileExtension;
        $pathInS3 = S3Helper::makeTenantS3BaseUrl($tenantName) . $documentPath;
        $disk->put( $tenantName . '/' . $documentPath, @file_get_contents($file, false, $context));
        return $pathInS3;
    }

    /**
     * Get language file url from S3 bucket
     *
     * @param string $tenantName
     * @param string $code
     */
    public function getLanguageFile(string $tenantName, string $code)
    {
        $languageFilePath = $this->getCustomLanguageFilePath($tenantName, $code);
        $languageFileUrl = Storage::disk('s3')->url($languageFilePath);

		if (!Storage::disk('s3')->exists($languageFilePath)) {
			$defaultLanguagePath = config('constants.AWS_S3_DEFAULT_LANGUAGE_FOLDER_NAME').'/'.
			$code.config('constants.AWS_S3_LANGUAGE_FILE_EXTENSION');
			$languageFileUrl = Storage::disk('s3')->url($defaultLanguagePath);
		}

        return $languageFileUrl;
    }

    /**
     * @param string $tenantName
     * @param string $isoCode
     * @return string
     */
    public function getCustomLanguageFilePath(string $tenantName, string $isoCode)
    {
        return
            $tenantName
            . '/'
            . config('constants.AWS_S3_LANGUAGES_FOLDER_NAME')
            . '/'
            . $isoCode
            . config('constants.AWS_S3_LANGUAGE_FILE_EXTENSION');
    }

    /**
     * Get default language file url from S3 bucket
     *
     * @param string $code
     */
    public function getDefaultLanguageFile(string $code)
    {
        $defaultLanguagePath = config('constants.AWS_S3_DEFAULT_LANGUAGE_FOLDER_NAME').'/'.
        $code.config('constants.AWS_S3_LANGUAGE_FILE_EXTENSION');
        $languageFileUrl = Storage::disk('s3')->url($defaultLanguagePath);
        return $languageFileUrl;
    }

    /**
     * @param string $tenantName
     * @return string
     */
    static function makeTenantS3BaseUrl(string $tenantName): string
    {
        return 'https://'
            . env('AWS_S3_BUCKET_NAME')
            . '.s3.'
            . env('AWS_REGION')
            . '.amazonaws.com/'
            . $tenantName
            .'/';
    }

    /**
     * Upload file to the Storage disk
     *
     * @param string $url
     *
     * @return string
     */
    private function uploadFile(
        string $url,
        string $tenantName,
        string $path
    ): string {
        set_time_limit(0);
        $context = stream_context_create(array('http'=> array(
            'timeout' => 1200
        )));

        $disk = Storage::disk('s3');
        $disk->put(
            "$tenantName/$path",
            file_get_contents($url, false, $context)
        );

        return self::makeTenantS3BaseUrl($tenantName).$path;
    }

    /**
     * Generate upload filename
     *
     * @param string $url
     *
     * @return string
     */
    private function generateFilename(string $url)
    {
        $headers = get_headers($url, 1);
        // Get name from Content Disposition
        if (isset($headers['Content-Disposition'])) {
            return substr($headers['Content-Disposition'], strpos($headers['Content-Disposition'], "=")+1);
        }

        // Get name from base name
        return basename($url);
    }
}
