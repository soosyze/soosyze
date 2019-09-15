<?php

namespace SoosyzeCore\Translate\Services;

class Translation extends \Soosyze\Config
{
    protected $lang;

    /**
     * @var \Soosyze\App
     */
    protected $core;

    protected $dir;

    protected $iso639_1 = [
        'aa'  => 'Afar',
        'ab'  => 'Abkhazian',
        'ae'  => 'Avestan',
        'af'  => 'Afrikaans',
        'ak'  => 'Akan',
        'am'  => 'Amharic',
        'an'  => 'Aragonese',
        'ar'  => 'Arabic',
        'as'  => 'Assamese',
        'av'  => 'Avaric',
        'ay'  => 'Aymara',
        'az'  => 'Azerbaijani',
        'ba'  => 'Bashkir',
        'be'  => 'Belarusian',
        'bg'  => 'Bulgarian',
        'bh'  => 'Bihari',
        'bi'  => 'Bislama',
        'bm'  => 'Bambara',
        'bn'  => 'Bengali',
        'bo'  => 'Tibetan',
        'br'  => 'Breton',
        'bs'  => 'Bosnian',
        'ca'  => 'Catalan',
        'ce'  => 'Chechen',
        'ch'  => 'Chamorro',
        'co'  => 'Corsican',
        'cr'  => 'Cree',
        'cs'  => 'Czech',
        'cu'  => 'Old Church Slavonic',
        'cv'  => 'Chuvash',
        'cy'  => 'Welsh',
        'da'  => 'Danish',
        'de'  => 'German',
        'dv'  => 'Divehi',
        'dz'  => 'Dzongkha',
        'ee'  => 'Ewe',
        'el'  => 'Greek',
        'en'  => 'English',
        'eo'  => 'Esperanto',
        'es'  => 'Spanish',
        'et'  => 'Estonian',
        'eu'  => 'Basque',
        'fa'  => 'Persian',
        'ff'  => 'Fulah',
        'fi'  => 'Finnish',
        'fj'  => 'Fijian',
        'fo'  => 'Faroese',
        'fr'  => 'French',
        'fy'  => 'Western Frisian',
        'ga'  => 'Irish',
        'gd'  => 'Scottish Gaelic',
        'gl'  => 'Galician',
        'gn'  => 'Guarani',
        'gu'  => 'Gujarati',
        'gv'  => 'Manx',
        'ha'  => 'Hausa',
        'he'  => 'Hebrew',
        'hi'  => 'Hindi',
        'ho'  => 'Hiri Motu',
        'hr'  => 'Croatian',
        'ht'  => 'Haitian',
        'hu'  => 'Hungarian',
        'hy'  => 'Armenian',
        'hz'  => 'Herero',
        'ia'  => 'Interlingua',
        'id'  => 'Indonesian',
        'ie'  => 'Interlingue',
        'ig'  => 'Igbo',
        'ii'  => 'Sichuan Yi',
        'ik'  => 'Inupiaq',
        'io'  => 'Ido',
        'is'  => 'Icelandic',
        'it'  => 'Italian',
        'iu'  => 'Inuktitut',
        'ja'  => 'Japanese',
        'jv'  => 'Javanese',
        'ka'  => 'Georgian',
        'kg'  => 'Kongo',
        'ki'  => 'Kikuyu',
        'kj'  => 'Kwanyama',
        'kk'  => 'Kazakh',
        'kl'  => 'Kalaallisut',
        'km'  => 'Khmer',
        'kn'  => 'Kannada',
        'ko'  => 'Korean',
        'kr'  => 'Kanuri',
        'ks'  => 'Kashmiri',
        'ku'  => 'Kurdish',
        'kv'  => 'Komi',
        'kw'  => 'Cornish',
        'ky'  => 'Kirghiz',
        'la'  => 'Latin',
        'lb'  => 'Luxembourgish',
        'lg'  => 'Ganda',
        'li'  => 'Limburgish',
        'ln'  => 'Lingala',
        'lo'  => 'Lao',
        'lt'  => 'Lithuanian',
        'lu'  => 'Luba',
        'lv'  => 'Latvian',
        'mg'  => 'Malagasy',
        'mh'  => 'Marshallese',
        'mi'  => 'Māori',
        'mk'  => 'Macedonian',
        'ml'  => 'Malayalam',
        'mn'  => 'Mongolian',
        'mo'  => 'Moldavian',
        'mr'  => 'Marathi',
        'ms'  => 'Malay',
        'mt'  => 'Maltese',
        'my'  => 'Burmese',
        'na'  => 'Nauru',
        'nb'  => 'Norwegian Bokmål',
        'nd'  => 'North Ndebele',
        'ne'  => 'Nepali',
        'ng'  => 'Ndonga',
        'nl'  => 'Dutch',
        'nn'  => 'Norwegian Nynorsk',
        'no'  => 'Norwegian',
        'nr'  => 'South Ndebele',
        'nv'  => 'Navajo',
        'ny'  => 'Chichewa',
        'oc'  => 'Occitan',
        'oj'  => 'Ojibwa',
        'om'  => 'Oromo',
        'or'  => 'Oriya',
        'os'  => 'Ossetian',
        'pa'  => 'Panjabi',
        'pi'  => 'Pāli ',
        'pl'  => 'Polish',
        'ps'  => 'Pashto',
        'pt'  => 'Portuguese',
        'qu'  => 'Quechua',
        'rc ' => 'Reunionese ',
        'rm'  => 'Romansh',
        'rn'  => 'Kirundi',
        'ro'  => 'Romanian',
        'ru'  => 'Russian',
        'rw'  => 'Kinyarwanda',
        'sa'  => 'Sanskrit',
        'sc'  => 'Sardinian',
        'sd'  => 'Sindhi',
        'se'  => 'Northern Sami',
        'sg'  => 'Sango',
        'sh'  => 'Serbo-Croatian ',
        'si'  => 'Sinhalese',
        'sk'  => 'Slovak',
        'sl'  => 'Slovenian',
        'sm'  => 'Samoan',
        'sn'  => 'Shona',
        'so'  => 'Somali',
        'sq'  => 'Albanian',
        'sr'  => 'Serbian',
        'ss'  => 'Swati',
        'st'  => 'Sotho',
        'su'  => 'Sundanese',
        'sv'  => 'Swedish',
        'sw'  => 'Swahili',
        'ta'  => 'Tamil',
        'te'  => 'Telugu',
        'tg'  => 'Tajik',
        'th'  => 'Thai',
        'ti'  => 'Tigrinya',
        'tk'  => 'Turkmen',
        'tl'  => 'Tagalog',
        'tn'  => 'Tswana',
        'to'  => 'Tonga',
        'tr'  => 'Turkish',
        'ts'  => 'Tsonga',
        'tt'  => 'Tatar',
        'tw'  => 'Twi',
        'ty'  => 'Tahitian',
        'ug'  => 'Uighur',
        'uk'  => 'Ukrainian',
        'ur'  => 'Urdu',
        'uz'  => 'Uzbek',
        've'  => 'Venda',
        'vi'  => 'Viêt Namese',
        'vo'  => 'Volapük',
        'wa'  => 'Walloon',
        'wo'  => 'Wolof',
        'xh'  => 'Xhosa',
        'yi'  => 'Yiddish',
        'yo'  => 'Yoruba',
        'za'  => 'Zhuang',
        'zh'  => 'Chinese',
        'zu'  => 'Zulu'
    ];

    public function __construct($core, $dir, $langDefault = 'en')
    {
        $this->core = $core;
        $this->lang = $core->get('config')->get('settings.lang', $langDefault);
        parent::__construct($dir);
    }

    public function t($str, array $vars = [])
    {
        if (empty($str) || !\is_string($str)) {
            return '';
        }
        $subject = $this->get($str, $str);
        $out     = str_replace(array_keys($vars), $vars, $subject);

        return htmlspecialchars($out);
    }

    public function getLang()
    {
        $path = $this->getPath();

        $dir_iterator = new \DirectoryIterator($path);

        /* On supprime chaque dossier et chaque fichier	du dossier cible */
        $output = [];
        foreach ($dir_iterator as $file) {
            /* Fichier instance de SplFileInfo */
            if (in_array($file->getBasename(), [ '..', '.' ]) || $file->isDir()) {
                continue;
            }
            $name = $file->getBasename('.' . $file->getExtension());
          
            if (isset($this->iso639_1[ $name ])) {
                $output[$name] = [
                    'value' => $name,
                    'label' => $this->iso639_1[ $name ]
                ];
            }
        }

        return $output;
    }

    protected function prepareKey($strKey)
    {
        if (!is_string($strKey) || $strKey === '') {
            throw new \InvalidArgumentException('The key must be a non-empty string.');
        }

        return [ $this->lang, $strKey ];
    }
}
