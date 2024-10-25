<?php

namespace App\libraries;

use Illuminate\Support\Facades\App;

class Env
{
    /**
     * Get the environment value or default if not exist.
     *
     * @param string $key
     * @param string|null $default
     * @return void
     */
    public static function get(string $key, string $default = null)
    {
        return env($key, $default);
    }

    /**
     * set enviroment value
     *
     * @param string $key
     * @param string $value
     * @param string|null $envFilePath
     * @return void
     */
    public static function set(string $key, string $value, string $envFilePath = null): void
    {
        try {
            $envFilePath = $envFilePath ?? App::environmentFilePath();
        } catch (InvalidArgumentException $e) {
            return;
        }

        $content = file_get_contents($envFilePath);
        [$newEnvFileContent, $isNewVariableSet] = self::setEnvVariable($content, $key, $value);

        self::writeFile($envFilePath, $newEnvFileContent);
    }

    /**
     * Set or update env-variable.
     *
     * @param string $envFileContent Content of the .env file.
     * @param string $key            Name of the variable.
     * @param string $value          Value of the variable.
     *
     * @return array [string newEnvFileContent, bool isNewVariableSet].
     */
    protected static function setEnvVariable(string $envFileContent, string $key, string $value): array
    {
        $oldPair = self::readKeyValuePair($envFileContent, $key);

        // Wrap values that have a space or equals in quotes to escape them
        if (preg_match('/\s/',$value) || strpos($value, '=') !== false) {
            $value = '"' . $value . '"';
        }

        $newPair = $key . '=' . $value;

        // For existed key.
        if ($oldPair !== null) {
            $replaced = preg_replace('/^' . preg_quote($oldPair, '/') . '$/uimU', $newPair, $envFileContent);
            return [$replaced, false];
        }

        // For a new key.
        return [$envFileContent . "\n" . $newPair . "\n", true];
    }

    /**
     * Read the "key=value" string of a given key from an environment file.
     * This function returns original "key=value" string and doesn't modify it.
     *
     * @param string $envFileContent
     * @param string $key
     *
     * @return string|null Key=value string or null if the key is not exists.
     */
    protected static function readKeyValuePair(string $envFileContent, string $key): ?string
    {
        // Match the given key at the beginning of a line
        if (preg_match("#^ *{$key} *= *[^\r\n]*$#uimU", $envFileContent, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * writeFile
     *
     * @param string $path
     * @param string $contents
     * @return boolean
     */
    protected static function writeFile(string $path, string $contents): bool
    {
        return (bool)file_put_contents($path, $contents, LOCK_EX);
    }
}