<?php
namespace App\Rules;

use Illuminate\Support\Facades\Validator;
use App\Helpers\IPValidationHelper;
use App\Models\Skill;
use DB;

class CustomValidationRules
{
    public static function validate()
    {
        Validator::extend('valid_media_path', function ($attribute, $value) {
            try {
                $urlMimeType = isset(get_headers($value, 1)['Content-Type']) ? get_headers($value, 1)['Content-Type'] :
                get_headers($value, 1)['content-type'];
                $validMimeTypes = config('constants.slider_image_mime_types');
                return (!in_array($urlMimeType, $validMimeTypes)) ? false : true;
            } catch (\Exception $e) {
                return false;
            }
        });

        Validator::extend('valid_document_path', function ($attribute, $value) {
            try {
                $urlMimeType = isset(get_headers($value, 1)['Content-Type']) ? get_headers($value, 1)['Content-Type'] :
                get_headers($value, 1)['content-type'];
                $validMimeTypes = config('constants.document_mime_types');
                return (!in_array($urlMimeType, $validMimeTypes)) ? false : true;
            } catch (\Exception $e) {
                return false;
            }
        });

        Validator::extend('valid_video_url', function ($attribute, $value) {
            return (preg_match(
                '~^(?:https?://)?(?:www[.])?(?:youtube[.]com/watch[?]v=|youtu[.]be/)([^&]{11}) ~x',
                $value
            ))
            ? true : false;
        });

        Validator::extend('valid_profile_image', function ($attribute, $value, $params, $validator) {
            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $value));
            $f = finfo_open();
            $result = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
            return in_array($result, config('constants.profile_image_types'));
        });

        Validator::extend('valid_parent_skill', function ($attribute, $value) {
            return ($value == 0) ? true : ((empty(Skill::where('skill_id', $value)->get()->toArray())) ? false : true);
        });

        Validator::extend('valid_linkedin_url', function ($attribute, $value) {
            // Be sure to update the validation in the MyAccount vue file as well
            return (preg_match(
                '/^https:\/\/(|[a-z]{2,3}\.)linkedin\.com\/(in|company)\/[-a-z0-9]+(|[\/#\?][^\n\r]*)$/Ds',
                $value
            ))
            ? true : false;
        });

        Validator::extend('valid_timesheet_document_type', function ($attribute, $value) {
            $extension = $value->getClientOriginalExtension();
            $urlExtension = strtolower($extension);
            return (!in_array($urlExtension, config('constants.timesheet_document_types'))) ? false : true;
        });

        Validator::extend('valid_story_image_type', function ($attribute, $value) {
            $urlExtension = $value->getClientOriginalExtension();
            $imageUrlExtension = strtolower($urlExtension);
            return (!in_array($imageUrlExtension, config('constants.story_image_types'))) ? false : true;
        });

        Validator::extend('valid_story_video_url', function ($attribute, $value) {
            $storyVideos = explode(",", $value);
            $val = true;
            for ($i=0; $i < count($storyVideos); $i++) {
                $val = (preg_match(
                    '~^(?:https?://)?(?:www[.])?(?:youtube[.]com/watch[?]v=|youtu[.]be/)([^&]{11}) ~x',
                    $storyVideos[$i]
                )) ? true : false;

                if (!$val) {
                    return false;
                }
            }
            return $val;
        });

        Validator::extend('max_video_url', function ($attribute, $value) {
            $storyVideos = explode(",", $value);
            if (count($storyVideos) > config('constants.STORY_MAX_VIDEO_LIMIT')) {
                return false;
            }
            return true;
        });

        Validator::extend('ip_whitelist_pattern', function ($attribute, $value) {
            $ipHelper = new IPValidationHelper();
            // Check for valid range pattern
            if ($ipHelper->validRangePattern($value)) {
                return true;
            }
            // Check for valid wildcard pattern
            if ($ipHelper->validWildcardPattern($value)) {
                return true;
            }
            // Check for valid cidr pattern
            if ($ipHelper->validCidrPattern($value)) {
                return true;
            }
            // Check for valid valid ip
            if ($ipHelper->validIp($value)) {
                return true;
            }
            return false;
        });

        Validator::extend('max_item', function ($attribute, $value, $params) {
            $itemCount = DB::table($params[0])
                ->whereNull('deleted_at')
                ->count();

            return $itemCount + 1 <= $params[1];
        });

        Validator::replacer('max_item', function($message, $attribute, $rule, $parameters) {
            return str_replace(':max_item', $parameters[1], $message);
        });

        Validator::extend('valid_icon_path', function ($attribute, $value) {
            try {
                $urlMimeType = isset(get_headers($value, 1)['Content-Type']) ? get_headers($value, 1)['Content-Type'] :
                get_headers($value, 1)['content-type'];
                $validMimeTypes = config('constants.icon_image_mime_types');
                return (!in_array($urlMimeType, $validMimeTypes)) ? false : true;
            } catch (\Exception $e) {
                return false;
            }
        });

        Validator::extend('within_range', function ($attribute, $value, $parameters) {
            $parameters = array_map('intval', $parameters);
            return $value >= min($parameters) && $value <= max($parameters);
        });

        Validator::replacer('within_range', function($message, $attribute, $rule, $parameters) {
            $parameters = array_map('intval', $parameters);
            $message = str_replace(':minvalue', min($parameters), $message);
            $message = str_replace(':maxvalue', max($parameters), $message);
            return $message;
        });

        Validator::extend('prefix_with', function ($attribute, $value, $parameters) {
            $prefix = $parameters[0];
            return $prefix == substr($value, 0, strlen($prefix));
        });

        Validator::replacer('prefix_with', function($message, $attribute, $rule, $parameters) {
            return str_replace(':prefix_with', $parameters[0], $message);
        });

        Validator::extend('max_html_stripped', function($attribute, $value, $params) {
            return strlen(strip_tags($value)) <= $params[0];
        });

        Validator::replacer('max_html_stripped',
            function($message, $attribute, $rule, $params) {
                return str_replace(':max', $params[0], $message);
            }
        );

        Validator::extend('date_range', function ($attribute, $value) {
            list($from, $to) = explode(':', $value);
            $startDate = strtotime($from);
            $endDate = strtotime($to);
            if ($startDate === false || $endDate === false || $endDate < $startDate) {
                return false;
            }
            return true;
        });
    }
}
