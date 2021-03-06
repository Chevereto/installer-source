<?php

function password(int $length)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';

    return substr(str_shuffle($chars), 0, $length);
}
function dump()
{
    echo '<pre>';
    foreach (func_get_args() as $value) {
        print_r($value);
    }
    echo '</pre>';
}
function append(string $filename, string $contents)
{
    prepareDirFor($filename);
    if (false === @file_put_contents($filename, $contents, FILE_APPEND)) {
        throw new RuntimeException('Unable to append content to file ' . $filename);
    }
}
function put(string $filename, string $contents)
{
    prepareDirFor($filename);
    if (false === @file_put_contents($filename, $contents)) {
        throw new RuntimeException('Unable to put content to file ' . $filename);
    }
}

function prepareDirFor(string $filename)
{
    if (!file_exists($filename)) {
        $dirname = dirname($filename);
        if (!file_exists($dirname)) {
            createPath($dirname);
        }
    }
}

function createPath(string $path): string
{
    if (!mkdir($path, 0777, true)) {
        throw new RuntimeException('Unable to create path ' . $path);
    }
    return $path;
}

function logger(string $message)
{
    if(PHP_SAPI !== 'cli') {
        return;
    }
    fwrite(fopen('php://stdout', 'r+'), $message);
}

function progressCallback($resource, $download_size = 0, $downloaded = 0, $upload_size = 0, $uploaded = 0)
{
    if($download_size == 0) {
        return;
    }
    logger(progress_bar($downloaded, $download_size, ' download'));
}

function progress_bar($done, $total, $info="", $width=50) {
    $perc = (int) round(($done * 100) / $total);
    $bar = (int) round(($width * $perc) / 100);
    return sprintf("  %s%%[%s>%s]%s\r", $perc, str_repeat("=", $bar), str_repeat(" ", $width-$bar), $info);
}

function set_status_header($code)
{
    if(headers_sent()) {
        return;
    }
    $desc = get_set_status_header_desc($code);
    if (empty($desc)) {
        return false;
    }
    $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
        $protocol = 'HTTP/1.0';
    }
    $set_status_header = "$protocol $code $desc";
    header($set_status_header, true, $code);
}

function get_set_status_header_desc($code)
{
    $codes_to_desc = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        510 => 'Not Extended',
    );

    return $codes_to_desc[$code] ?? 'n/a';
}

function writeToStderr(string $message) {
    fwrite(fopen('php://stderr', 'wb'), $message . "\n");
}

function isDocker(): bool {
    return ($_ENV['CHEVERETO_SERVICING'] ?? null) == 'docker';
}