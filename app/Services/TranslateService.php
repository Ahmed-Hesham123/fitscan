<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use LanguageDetector\LanguageDetector;
use Stichoza\GoogleTranslate\GoogleTranslate;



class TranslateService
{

    public static function translateInstance($input)
    {
        $keysToTranslate = [
            "status",
            "order_status",
            "payment_method",
            "bannerable_type",
        ];

        foreach ($input as &$item) {
            // handle array of objects
            if (is_object($item) || is_array($item)) {
                $item = self::translateInstance($item);
            }

            // handel normal values
            else {
                if (is_array($input) || is_object($input)) {
                    foreach ($keysToTranslate as $key) {
                        if (isset($input[$key])) {
                            if (is_string($input[$key])) {
                                $input['display_' . $key] = self::translateWord($input[$key]);
                            }
                        }
                    }
                }
            }
        }

        return $input;
    }

    public static function translateWord($word)
    {
        $translatedWord = __('translate.' . $word);
        return $translatedWord;
    }

    public static function detectLanguage($text)
    {
        $detector = new LanguageDetector();

        $language = $detector->detect($text, ['en', 'ar']);
        return $language;
    }

    public static function translateText($text, $from, $to)
    {
        $translator = new GoogleTranslate();
        $translator->setSource($from);
        $translator->setTarget($to);
        return $translator->translate($text);
    }

    public static function getLocalizedValue($text)
    {
        if (TranslateService::detectLanguage($text) == 'en') {
            return ['en' => $text, 'ar' => TranslateService::translateText($text, 'en', 'ar')];
        } else {
            return ['en' => TranslateService::translateText($text, 'ar', 'en'), 'ar' => $text];
        }
    }

    public static function localizedColumn($column_name, $alias = null, $unQuoted = false)
    {
        if (!$alias) {
            $alias = $column_name;
        }
        $locale = app()->getLocale();
        if ($unQuoted) {
            return DB::raw("JSON_UNQUOTE(JSON_EXTRACT($column_name, '$.$locale')) as $alias");
        }
        return DB::raw("JSON_EXTRACT($column_name, '$.$locale') as $alias");
    }
}
