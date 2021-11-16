<?php

namespace EscolaLms\TopicTypes\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Markdown
{
    private function unparseUrl($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?'.urldecode($parsed_url['query']) : '';
        $fragment = isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    public function convertImagesPathsForImageApi(string $input, string $destination_prefix): array
    {
        $results = [];

        // https://gist.github.com/rohit00082002/2773368 might be better
        $value = preg_replace_callback('/!\[(.*)\]\s?\((.*)()(.*)\)/', function ($match) use ($destination_prefix, $results) {
            $filepath = $match[2];

            $basename = basename($filepath);

            $destination = sprintf($destination_prefix.'%s', $basename);

            // three scenarios

            if (strpos($filepath, 'api/images/img') !== false) {
                // image is served by images API, default, just checking path

                $string = $filepath;
                $pattern = parse_url($string);

                parse_str($pattern['query'], $vars);

                $filepath = $vars['path'];
                $basename = basename($filepath);

                $destination = sprintf($destination_prefix.'%s', $basename);

                if (!Storage::exists($destination)) {
                    Storage::move($filepath, $destination);
                }
                $results[] = [$filepath, $destination];

                $destination = $this->unparseUrl(array_merge(
                    $pattern,
                    [
                        'query' => http_build_query(array_merge(
                            $vars,
                            [
                                'path' => urldecode($destination),
                            ]
                        )),
                    ]
                ));
            } elseif (strpos($filepath, 'http') !== false) {
                //  remote file, download and switch to image API;

                $destination = sprintf($destination_prefix.'%s', Str::slug(basename($filepath)));

                Storage::put($destination, file_get_contents($filepath));

                $pathinfo = pathinfo($destination);

                if (!array_key_exists('extension', $pathinfo) || (array_key_exists('extension', $pathinfo) && !in_array($pathinfo['extension'], ['apng', 'avif', 'gif', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'svg', 'webp']))) {
                    $info = getimagesize(Storage::path($destination));
                    $ext = explode('/', $info['mime']);

                    if (Storage::exists($destination.'.'.$ext[1])) {
                        Storage::delete($destination.'.'.$ext[1]);
                    }
                    Storage::copy($destination, $destination.'.'.$ext[1]);
                    $destination = $destination.'.'.$ext[1];
                }

                $destination = url('api/images/img?path=').$destination;

                $results[] = [$filepath, $destination];
            }

            return str_replace($match[2], $destination, $match[0]);
        }, $input);

        return [
            'value' => $value,
            'results' => $results,
        ];
    }

    public function getImagesPathsWithoutImageApi(string $input): string
    {
        return preg_replace_callback('/!\[(.*)\]\s?\((.*)()(.*)\)/', function ($match) {
            $filepath = $match[2];

            if (strpos($filepath, 'api/images/img') !== false) {
                // image is served by images API, default, just checking path

                $pattern = parse_url($filepath);
                parse_str($pattern['query'], $vars);
                $destination = $vars['path'];

                return str_replace($match[2], $destination, $match[0]);
            }

            return $match[0];
        }, $input);
    }
}
