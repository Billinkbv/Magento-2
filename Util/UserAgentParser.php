<?php

namespace Billink\Billink\Util;

use InvalidArgumentException;

use function array_intersect;
use function array_map;
use function array_search;
use function array_unique;
use function is_numeric;
use function preg_grep;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function reset;
use function strlen;
use function strtolower;
use function substr;

use const PREG_PATTERN_ORDER;

/**
 * Parses a user agent string into its important parts
 *
 * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
 *
 * @return string[] an array with browser, version and platform keys
 * @throws InvalidArgumentException on not having a proper user agent to parse.
 * @link https://github.com/donatj/PhpUserAgent
 * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
 */
class UserAgentParser
{
    public static function parse_user_agent($u_agent = null): array
    {
        if ($u_agent === null) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $u_agent = $_SERVER['HTTP_USER_AGENT'];
            } else {
                throw new InvalidArgumentException('parse_user_agent requires a user agent');
            }
        }
        $platform = null;
        $browser = null;
        $version = null;
        $empty = ['platform' => $platform, 'browser' => $browser, 'version' => $version];
        if (!$u_agent) {
            return $empty;
        }
        if (preg_match('/\((.*?)\)/im', $u_agent, $parent_matches)) {
            preg_match_all(
                '/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS)|Xbox(\ One)?)
				(?:\ [^;]*)?
				(?:;|$)/imx',
                $parent_matches[1],
                $result,
                PREG_PATTERN_ORDER
            );
            $priority = ['Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'CrOS', 'X11'];
            $result['platform'] = array_unique($result['platform']);
            if (count($result['platform']) > 1) {
                if ($keys = array_intersect($priority, $result['platform'])) {
                    $platform = reset($keys);
                } else {
                    $platform = $result['platform'][0];
                }
            } elseif (isset($result['platform'][0])) {
                $platform = $result['platform'][0];
            }
        }
        if ($platform === 'linux-gnu' || $platform === 'X11') {
            $platform = 'Linux';
        } elseif ($platform === 'CrOS') {
            $platform = 'Chrome OS';
        }
        preg_match_all(
            '%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
				TizenBrowser|Chrome|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|UCBrowser|Puffin|SamsungBrowser|
				Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
				Valve\ Steam\ Tenfoot|
				NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
				(?:\)?;?)
				(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
            $u_agent,
            $result,
            PREG_PATTERN_ORDER
        );
        // If nothing matched, return null (to avoid undefined index errors)
        if (!isset($result['browser'][0], $result['version'][0])) {
            if (preg_match('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result)) {
                return [
                    'platform' => $platform ?: null,
                    'browser' => $result['browser'],
                    'version' => isset($result['version']) ? $result['version'] ?: null : null
                ];
            }

            return $empty;
        }
        if (preg_match('/rv:(?P<version>[0-9A-Z.]+)/si', $u_agent, $rv_result)) {
            $rv_result = $rv_result['version'];
        }
        $browser = $result['browser'][0];
        $version = $result['version'][0];
        $lowerBrowser = array_map('\strtolower', $result['browser']);
        $find = static function ($search, &$key, &$value = null) use ($lowerBrowser) {
            $search = (array) $search;
            foreach ($search as $val) {
                $xkey = array_search(strtolower($val), $lowerBrowser);
                if ($xkey !== false) {
                    $value = $val;
                    $key = $xkey;

                    return true;
                }
            }

            return false;
        };
        $key = 0;
        $val = '';
        if ($browser === 'Iceweasel' || strtolower($browser) === 'icecat') {
            $browser = 'Firefox';
        } elseif ($find('Playstation Vita', $key)) {
            $platform = 'PlayStation Vita';
            $browser = 'Browser';
        } elseif ($find(['Kindle Fire', 'Silk'], $key, $val)) {
            $browser = $val === 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if (!($version = $result['version'][$key]) || !is_numeric($version[0])) {
                $version = $result['version'][array_search('Version', $result['browser'])];
            }
        } elseif ($find('NintendoBrowser', $key) || $platform === 'Nintendo 3DS') {
            $browser = 'NintendoBrowser';
            $version = $result['version'][$key];
        } elseif ($find('Kindle', $key, $platform)) {
            $browser = $result['browser'][$key];
            $version = $result['version'][$key];
        } elseif ($find('OPR', $key)) {
            $browser = 'Opera Next';
            $version = $result['version'][$key];
        } elseif ($find('Opera', $key, $browser)) {
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif ($find('Puffin', $key, $browser)) {
            $version = $result['version'][$key];
            if (strlen($version) > 3) {
                $part = substr($version, -2);
                if (ctype_upper($part)) {
                    $version = substr($version, 0, -2);
                    $flags = [
                        'IP' => 'iPhone',
                        'IT' => 'iPad',
                        'AP' => 'Android',
                        'AT' => 'Android',
                        'WP' => 'Windows Phone',
                        'WT' => 'Windows'
                    ];
                    if (isset($flags[$part])) {
                        $platform = $flags[$part];
                    }
                }
            }
        } elseif (
            $find(
                ['IEMobile', 'Edge', 'Midori', 'Vivaldi', 'SamsungBrowser', 'Valve Steam Tenfoot', 'Chrome'],
                $key,
                $browser
            )
        ) {
            $version = $result['version'][$key];
        } elseif ($rv_result && $find('Trident', $key)) {
            $browser = 'MSIE';
            $version = $rv_result;
        } elseif ($find('UCBrowser', $key)) {
            $browser = 'UC Browser';
            $version = $result['version'][$key];
        } elseif ($find('CriOS', $key)) {
            $browser = 'Chrome';
            $version = $result['version'][$key];
        } elseif ($browser === 'AppleWebKit') {
            if ($platform === 'Android' && !($key = 0)) {
                $browser = 'Android Browser';
            } elseif (str_starts_with($platform, 'BB')) {
                $browser = 'BlackBerry Browser';
                $platform = 'BlackBerry';
            } elseif ($platform === 'BlackBerry' || $platform === 'PlayBook') {
                $browser = 'BlackBerry Browser';
            } else {
                $find('Safari', $key, $browser) || $find('TizenBrowser', $key, $browser);
            }
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif ($pKey = preg_grep('/playstation \d/i', array_map('\strtolower', $result['browser']))) {
            $pKey = reset($pKey);
            $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $pKey);
            $browser = 'NetFront';
        }

        return ['platform' => $platform ?: null, 'browser' => $browser ?: null, 'version' => $version ?: null];
    }
}
