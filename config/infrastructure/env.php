<?php      

$ENV_PATH = realpath(__DIR__ . '/../../.env');

if (!$ENV_PATH || !file_exists($ENV_PATH)) {
    throw new Exception("Environment file not found at expected path: " . __DIR__ . '/../../.env');
}

function loadEnv($path){
    if (!file_exists($path)) {
        throw new Exception("File not found: " . $path);
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;

        $name  = trim($parts[0]);
        $value = trim($parts[1]);

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $name  = trim($name);
        $value = trim($value);

        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?? $default;
}

loadEnv($ENV_PATH);
