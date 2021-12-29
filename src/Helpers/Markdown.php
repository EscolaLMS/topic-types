<?php

namespace EscolaLms\TopicTypes\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Markdown
{
    protected function unparseUrl($parsedUrl)
    {
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
        $user = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = isset($parsedUrl['query']) ? '?' . urldecode($parsedUrl['query']) : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    public function convertImagesPathsForImageApi(string $input, string $destinationPrefix): array
    {
        $results = [];
        // https://gist.github.com/rohit00082002/2773368 might be better
        $value = preg_replace_callback('/!\[(.*)\]\s?\((.*)()(.*)\)/', function ($match) use ($destinationPrefix, &$results) {
            $filepath = $match[2] ?? null;
            $basename = basename($filepath);
            $destination = sprintf($destinationPrefix . '%s', $basename);
            // three scenarios
            if (strpos($filepath, 'api/images/img') !== false) {
                $results[] = $this->convertFilePathByApiPattern($destinationPrefix, $filepath, $destination);
            } elseif (strpos($filepath, 'http') !== false) {
                $results[] = $this->convertFilePathByHttp($destinationPrefix, $filepath, $destination);
            }

            return str_replace($match[2], $destination, $match[0]);
        }, $input);

        return [
            'value' => $value,
            'results' => $results,
        ];
    }

    private function convertFilePathByApiPattern(string $destinationPrefix, string $filepath, string &$destination): array
    {
        // image is served by images API, default, just checking path
        $string = $filepath;
        $pattern = parse_url($string);
        parse_str($pattern['query'], $vars);
        $filepath = $vars['path'];
        $basename = basename($filepath);
        $destination = sprintf($destinationPrefix . '%s', $basename);
        if (!Storage::exists($destination)) {
            Storage::move($filepath, $destination);
        }
        $results = [$filepath, $destination];
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
        return $results;
    }

    private function convertFilePathByHttp(string $destinationPrefix, string $filepath, string &$destination): array
    {
        //  remote file, download and switch to image API;
        $destination = sprintf($destinationPrefix . '%s', Str::slug(basename($filepath)));
        Storage::put($destination, file_get_contents($filepath));
        $pathInfo = pathinfo($destination);
        if (!array_key_exists('extension', $pathInfo) || (array_key_exists('extension', $pathInfo) && !in_array($pathInfo['extension'], ['apng', 'avif', 'gif', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'svg', 'webp']))) {
            $info = getimagesize(Storage::path($destination));
            $ext = explode('/', $info['mime']);
            if (Storage::exists($destination . '.' . $ext[1])) {
                Storage::delete($destination . '.' . $ext[1]);
            }
            Storage::copy($destination, $destination . '.' . $ext[1]);
            $destination = $destination . '.' . $ext[1];
        }
        $destination = url('api/images/img?path=') . $destination;
        return [$filepath, $destination];
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
