<?php

/**
 *    Copyright 2015-2017 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * Like array_search but returns null if not found instead of false.
 * Strict mode only.
 */
function array_search_null($value, $array)
{
    $key = array_search($value, $array, true);

    if ($key !== false) {
        return $key;
    }
}

function es_query_and_words($words)
{
    $parts = preg_split("/\s+/", trim($words ?? ''));

    $partsEscaped = [];

    foreach ($parts as $part) {
        $partsEscaped[] = urlencode($part);
    }

    return implode(' AND ', $partsEscaped);
}

function flag_path($country)
{
    return '/images/flags/'.$country.'.png';
}

function get_valid_locale($requestedLocale)
{
    if (in_array($requestedLocale, config('app.available_locales'), true)) {
        return $requestedLocale;
    }

    return array_first(
        config('app.available_locales'),
        function ($_key, $value) use ($requestedLocale) {
            return starts_with($requestedLocale, $value);
        },
        config('app.fallback_locale')
    );
}

function json_time($time)
{
    if ($time !== null) {
        return $time->toIso8601String();
    }
}

function locale_flag($locale)
{
    return App\Libraries\LocaleMeta::flagFor($locale);
}

function locale_name($locale)
{
    return App\Libraries\LocaleMeta::nameFor($locale);
}

function locale_for_timeago($locale)
{
    if ($locale === 'zh') {
        return 'zh-CN';
    }

    return $locale;
}

function osu_url($key)
{
    $url = config("osu.urls.{$key}");

    if (($url[0] ?? null) === '/') {
        $url = config('osu.urls.base').$url;
    }

    return $url;
}

function product_quantity_options($product)
{
    if ($product->stock === null) {
        $max = $product->max_quantity;
    } else {
        $max = min($product->max_quantity, $product->stock);
    }
    $opts = [];
    for ($i = 1; $i <= $max; $i++) {
        $opts[$i] = trans_choice('common.count.item', $i);
    }

    return $opts;
}

function read_image_properties($path)
{
    try {
        $data = getimagesize($path);
    } catch (Exception $_e) {
        return;
    }

    if ($data !== false) {
        return $data;
    }
}

function read_image_properties_from_string($string)
{
    try {
        $data = getimagesizefromstring($string);
    } catch (Exception $_e) {
        return;
    }

    if ($data !== false) {
        return $data;
    }
}

function render_to_string($view, $variables = [])
{
    return view()->make($view, $variables)->render();
}

function obscure_email($email)
{
    $email = explode('@', $email);

    if (!present($email[0]) || !present($email[1] ?? null)) {
        return '<unknown>';
    }

    return $email[0][0].'***'.'@'.$email[1];
}

function countries_array_for_select()
{
    $out = [];

    foreach (App\Models\Country::forStore()->get() as $country) {
        if (!isset($lastDisplay)) {
            $lastDisplay = $country->display;
        } elseif ($lastDisplay !== $country->display) {
            $out['_disabled'] = '---';
        }
        $out[$country->acronym] = $country->name;
    }

    return $out;
}

function currency($price)
{
    if ($price === 0) {
        return 'free!';
    }

    return sprintf('US$%.2f', $price);
}

function error_popup($message, $statusCode = 422)
{
    return response(['error' => $message], $statusCode);
}

function i18n_view($view)
{
    $localViewPath = sprintf('%s-%s', $view, App::getLocale());

    if (view()->exists($localViewPath)) {
        return $localViewPath;
    } else {
        return sprintf('%s-%s', $view, config('app.fallback_locale'));
    }
}

function is_sql_unique_exception($ex)
{
    return starts_with(
        $ex->getMessage(),
        'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry'
    );
}

function js_view($view, $vars = [])
{
    return response()
        ->view($view, $vars)
        ->header('Content-Type', 'application/javascript');
}

function ujs_redirect($url)
{
    if (Request::ajax()) {
        return js_view('layout.ujs-redirect', ['url' => $url]);
    } else {
        return redirect($url);
    }
}

function timeago($date)
{
    $display_date = $date->toRfc850String();
    $attribute_date = json_time($date);

    return "<time class='timeago' datetime='{$attribute_date}'>{$display_date}</time>";
}

function current_action()
{
    $currentAction = \Route::currentRouteAction();
    if ($currentAction !== null) {
        return explode('@', $currentAction, 2)[1];
    }
}

function link_to_user($user_id, $user_name, $user_color)
{
    $user_name = e($user_name);
    $style = user_color_style($user_color, 'color');

    if ($user_id) {
        $user_url = e(route('users.show', $user_id));

        return "<a class='user-name' href='{$user_url}' style='{$style}'>{$user_name}</a>";
    } else {
        return "<span class='user-name'>{$user_name}</span>";
    }
}

function issue_icon($issue)
{
    switch ($issue) {
        case 'added': return 'fa-cogs';
        case 'assigned': return 'fa-user';
        case 'confirmed': return 'fa-exclamation-triangle';
        case 'resolved': return 'fa-check-circle';
        case 'duplicate': return 'fa-copy';
        case 'invalid': return 'fa-times-circle';
    }
}

function post_url($topicId, $postId, $jumpHash = true, $tail = false)
{
    $postIdParamKey = 'start';
    if ($tail === true) {
        $postIdParamKey = 'end';
    }

    $url = route('forum.topics.show', ['topics' => $topicId, $postIdParamKey => $postId]);

    return $url;
}

function wiki_url($page = 'Welcome', $locale = null)
{
    $url = route('wiki.show', ['page' => $page]);

    if (present($locale) && $locale !== App::getLocale()) {
        $url .= '?locale='.$locale;
    }

    return $url;
}

function bbcode($text, $uid, $withGallery = false)
{
    return (new App\Libraries\BBCodeFromDB($text, $uid, $withGallery))->toHTML();
}

function bbcode_for_editor($text, $uid)
{
    return (new App\Libraries\BBCodeFromDB($text, $uid))->toEditor();
}

function proxy_image($url)
{
    $decoded = urldecode(html_entity_decode($url));

    if (config('osu.camo.key') === '') {
        return $decoded;
    }

    $isProxied = starts_with($decoded, config('osu.camo.prefix'));
    if ($isProxied) {
        return $decoded;
    }

    $url = bin2hex($decoded);
    $secret = hash_hmac('sha1', $decoded, config('osu.camo.key'));

    return config('osu.camo.prefix')."{$secret}/{$url}";
}

function lazy_load_image($url, $class = '', $alt = '')
{
    $url = e($url);

    return "<img class='{$class}' src='data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' data-normal='{$url}' alt='{$alt}' />";
}

function nav_links()
{
    $links = [];

    $links['home'] = [
        'getNews' => osu_url('home.news'),
        'getChangelog' => route('changelog'),
        'getDownload' => route('download'),
    ];
    $links['help'] = [
        'getWiki' => wiki_url('Welcome'),
        'getFaq' => wiki_url('FAQ'),
        'getSupport' => osu_url('help.support'),
    ];
    $links['rankings'] = [
        'index' => route('ranking', ['mode' => 'osu', 'type' => 'performance']),
        'charts' => osu_url('rankings.charts'),
        'country' => osu_url('rankings.country'),
        'kudosu' => osu_url('rankings.kudosu'),
    ];
    $links['beatmaps'] = [
        'index' => route('beatmapsets.index'),
        'artists' => route('artists.index'),
    ];
    $links['community'] = [
        'forum-forums-index' => route('forum.forums.index'),
        'contests' => route('contests.index'),
        'tournaments' => route('tournaments.index'),
        'getLive' => route('livestreams.index'),
        'dev' => osu_url('dev'),
    ];
    $links['store'] = [
        'getListing' => action('StoreController@getListing'),
        'getCart' => action('StoreController@getCart'),
    ];

    return $links;
}

function footer_links()
{
    $links = [];
    $links['general'] = [
        'home' => route('home'),
        'changelog' => route('changelog'),
        'beatmaps' => action('BeatmapsetsController@index'),
        'download' => osu_url('home.download'),
        'wiki' => wiki_url('Welcome'),
    ];
    $links['help'] = [
        'faq' => wiki_url('FAQ'),
        'forum' => route('forum.forums.index'),
        'livestreams' => route('livestreams.index'),
        'report' => route('forum.topics.create', ['forum_id' => 5]),
    ];
    $links['support'] = [
        'tags' => route('support-the-game'),
        'merchandise' => action('StoreController@getListing'),
    ];
    $links['legal'] = [
        'tos' => wiki_url('Legal/TOS'),
        'copyright' => wiki_url('Legal/Copyright'),
        'serverStatus' => osu_url('status.server'),
        'osuStatus' => osu_url('status.osustatus'),
    ];

    return $links;
}

function presence($string, $valueIfBlank = null)
{
    return present($string) ? $string : $valueIfBlank;
}

function present($string)
{
    return $string !== null && $string !== '';
}

function user_color_style($color, $style)
{
    if (!present($color)) {
        return '';
    }

    return sprintf('%s: #%s', $style, e($color));
}

function base62_encode($input)
{
    $numbers = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $base = strlen($numbers);

    $output = '';
    $remaining = $input;

    do {
        $output = $numbers[($remaining % $base)].$output;
        $remaining = floor($remaining / $base);
    } while ($remaining > 0);

    return $output;
}

function display_regdate($user)
{
    if ($user->user_regdate === null) {
        return;
    }

    if ($user->user_regdate < Carbon\Carbon::createFromDate(2008, 1, 1)) {
        return trans('users.show.first_members');
    }

    return trans('users.show.joined_at', [
        'date' => '<strong>'.$user->user_regdate->formatLocalized('%B %Y').'</strong>',
    ]);
}

function i18n_date($datetime, $format = IntlDateFormatter::LONG)
{
    $formatter = IntlDateFormatter::create(
        App::getLocale(),
        $format,
        IntlDateFormatter::NONE
    );

    return $formatter->format($datetime);
}

function open_image($path, $dimensions = null)
{
    if ($dimensions === null) {
        $dimensions = read_image_properties($path);
    }

    if (!isset($dimensions[2]) || !is_int($dimensions[2])) {
        return;
    }

    try {
        $image = null;

        switch ($dimensions[2]) {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($path);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($path);
                break;
        }

        if ($image !== false) {
            return $image;
        }
    } catch (ErrorException $_e) {
        // do nothing
    }
}

function json_collection($model, $transformer, $includes = null)
{
    $manager = new League\Fractal\Manager();
    if ($includes !== null) {
        $manager->parseIncludes($includes);
    }
    $manager->setSerializer(new App\Serializers\ApiSerializer());

    // da bess
    if (is_string($transformer)) {
        $transformer = 'App\Transformers\\'.str_replace('/', '\\', $transformer).'Transformer';
        $transformer = new $transformer();
    }

    // we're using collection instead of item here, so we can peek at the items beforehand
    $collection = new League\Fractal\Resource\Collection($model, $transformer);

    return $manager->createData($collection)->toArray();
}

function json_item($model, $transformer, $includes = null)
{
    return json_collection([$model], $transformer, $includes)[0];
}

function fast_imagesize($url)
{
    return Cache::remember("imageSize:{$url}", Carbon\Carbon::now()->addMonth(1), function () use ($url) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Range: bytes=0-32768',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
        ]);
        $data = curl_exec($curl);
        curl_close($curl);

        return read_image_properties_from_string($data);
    });
}

function get_arr($input, $callback)
{
    if (!is_array($input)) {
        return;
    }

    $result = [];
    foreach ($input as $value) {
        $casted = call_user_func($callback, $value);

        if ($casted !== null) {
            $result[] = $casted;
        }
    }

    return $result;
}

function get_bool($string)
{
    if (is_bool($string)) {
        return $string;
    } elseif ($string === '1' || $string === 'on' || $string === 'true') {
        return true;
    } elseif ($string === '0' || $string === 'false') {
        return false;
    }
}

/*
 * Parses a string. If it's not an empty string or null,
 * return parsed integer value of it, otherwise return null.
 */
function get_int($string)
{
    if (present($string)) {
        return (int) $string;
    }
}

function get_file($input)
{
    if ($input instanceof Symfony\Component\HttpFoundation\File\UploadedFile) {
        return $input->getRealPath();
    }
}

function get_string($input)
{
    if (is_string($input)) {
        return $input;
    }
}

function get_class_basename($className)
{
    return substr($className, strrpos($className, '\\') + 1);
}

function get_class_namespace($className)
{
    return substr($className, 0, strrpos($className, '\\'));
}

function get_model_basename($model)
{
    if (!is_string($model)) {
        $model = get_class($model);
    }

    return str_replace('\\', '', snake_case(substr($model, strlen('App\\Models\\'))));
}

function ci_file_search($fileName)
{
    if (file_exists($fileName)) {
        return $fileName;
    }

    $directoryName = dirname($fileName);
    $fileArray = glob($directoryName.'/*', GLOB_NOSORT);
    $fileNameLowerCase = strtolower($fileName);
    foreach ($fileArray as $file) {
        if (strtolower($file) === $fileNameLowerCase) {
            return $file;
        }
    }

    return false;
}

function sanitize_filename($file)
{
    $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
    $file = mb_ereg_replace("([\.]{2,})", '', $file);

    return $file;
}

function deltree($dir)
{
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? deltree("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}

function get_param_value($input, $type)
{
    switch ($type) {
        case 'bool':
            return get_bool($input);
            break;
        case 'int':
            return get_int($input);
            break;
        case 'file':
            return get_file($input);
            break;
        case 'string':
            return get_string($input);
        case 'string_split':
            return get_arr(explode("\r\n", $input), 'get_string');
            break;
        case 'string[]':
            return get_arr($input, 'get_string');
            break;
        case 'int[]':
            return get_arr($input, 'get_int');
            break;
        default:
            return presence((string) $input);
    }
}

function get_params($input, $namespace, $keys)
{
    if ($namespace !== null) {
        $input = array_get($input, $namespace);
    }

    $params = [];

    foreach ($keys as $keyAndType) {
        $keyAndType = explode(':', $keyAndType);

        $key = $keyAndType[0];
        $type = $keyAndType[1] ?? null;

        $value = get_param_value(array_get($input, $key), $type);

        if ($value !== null) {
            array_set($params, $key, $value);
        }
    }

    return $params;
}

function array_rand_val($array)
{
    return $array[array_rand($array)];
}

/**
 * Just like original builder's "pluck" but with actual casting.
 * I mean "lists" in 5.1 which then replaced by replaced "pluck"
 * function. I mean, they deprecated the "pluck" function in 5.1
 * and then goes on changing what the function does.
 *
 * If need to pluck for all rows, just call `select()` on the class.
 */
function model_pluck($builder, $key)
{
    $result = [];

    foreach ($builder->select($key)->get() as $el) {
        $result[] = $el->$key;
    }

    return $result;
}

// Returns null if timestamp is null or 0.
// Technically it's not null if 0 but some tables have not null constraints
// despite null being a valid value. Instead it's filled in with 0 so this
// helper returns null if it's 0 and parses the timestamp otherwise.
function get_time_or_null($timestamp)
{
    if ($timestamp !== null && $timestamp !== 0) {
        return Carbon\Carbon::createFromTimestamp($timestamp);
    }
}

function format_duration_for_display($seconds)
{
    return floor($seconds / 60).':'.str_pad(($seconds % 60), 2, '0', STR_PAD_LEFT);
}

// Converts a standard image url to a retina one
// e.g. https://local.host/test.jpg -> https://local.host/test@2x.jpg
function retinaify($url)
{
    return preg_replace('/(\.[^.]+)$/', '@2x\1', $url);
}

function priv_check($ability, $args = null)
{
    return priv_check_user(Auth::user(), $ability, $args);
}

function priv_check_user($user, $ability, $args = null)
{
    return app()->make('OsuAuthorize')->doCheckUser($user, $ability, $args);
}

// Used to generate x,y pairs for fancy-chart.coffee
function array_to_graph_json(array &$array, $property_to_use)
{
    $index = 0;

    return array_map(function ($e) use (&$index, $property_to_use) {
        return [
            'x' => $index++,
            'y' => $e[$property_to_use],
        ];
    }, $array);
}

// Fisher-Yates
function seeded_shuffle(array &$items, int $seed = 0)
{
    mt_srand($seed);
    for ($i = count($items) - 1; $i > 0; $i--) {
        $j = mt_rand(0, $i);
        $tmp = $items[$i];
        $items[$i] = $items[$j];
        $items[$j] = $tmp;
    }
    mt_srand();
}

function first_paragraph($html, $split_on = "\n")
{
    $text = strip_tags($html);
    $match_pos = strpos($text, $split_on);

    return ($match_pos === false) ? $text : substr($text, 0, $match_pos);
}

function find_images($html)
{
    // regex based on answer in http://stackoverflow.com/questions/12933528/regular-expression-pattern-to-match-image-url-from-text
    $regex = "/(?:https?\:\/\/[a-zA-Z](?:[\w\-]+\.)+(?:[\w]{2,5}))(?:\:[\d]{1,6})?\/(?:[^\s\/]+\/)*(?:[^\s]+\.(?:jpe?g|gif|png))(?:\?\w?(?:=\w)?(?:&\w?(?:=\w)?)*)?/";
    $matches = [];
    preg_match_all($regex, $html, $matches);

    return $matches[0];
}

function find_first_image($html)
{
    $post_images = find_images($html);

    if (!is_array($post_images) || count($post_images) < 1) {
        return;
    }

    return $post_images[0];
}

function build_icon($prefix)
{
    switch ($prefix) {
        case 'add': return 'plus';
        case 'fix': return 'wrench';
        case 'misc': return 'question';
    }
}

// clamps $number to be between $min and $max
function clamp($number, $min, $max)
{
    return min($max, max($min, $number));
}
