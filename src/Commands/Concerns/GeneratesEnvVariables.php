<?php

namespace LittleApps\LittleJWT\Commands\Concerns;

trait GeneratesEnvVariables
{
    /**
     * Updates the specified env file with the specified variables.
     *
     * @param string $envPath Path to .env file
     * @param array $variables Associative array of environment variables to append or replace.
     * @return bool True if .env file was updated.
     */
    protected function updateEnvFile(string $envPath, array $variables)
    {
        if (($contents = file_get_contents($envPath)) === false) {
            return false;
        }

        $contents = $this->updateEnvFileContents($contents, $variables);

        return file_put_contents($envPath, $contents) !== false;
    }

    /**
     * Updates the contents of the .env file.
     *
     * @param string $contents Existing contents of .env file
     * @param array $variables Associative array of environment variables to append or replace.
     * @return string Updated .env file contents
     */
    protected function updateEnvFileContents(string $contents, array $variables)
    {
        foreach ($variables as $key => $value) {
            if (! $this->envKeyExists($key)) {
                // Append to .env file
                $contents = $this->appendEnvFile($contents, $key, $value);
            } else {
                // Replace in .env file
                $contents = $this->replaceEnvFile($contents, $key, $value);
            }
        }

        return $contents;
    }

    /**
     * Checks if env key already exists.
     *
     * @param string $key
     * @return bool
     */
    protected function envKeyExists(string $key)
    {
        return ! is_null(env($key));
    }

    /**
     * Appends env variable to file contents.
     *
     * @param string $contents Existing .env file contents
     * @param string $key Environment key
     * @param mixed $value Value
     * @return string Updated contents
     */
    protected function appendEnvFile(string $contents, string $key, $value)
    {
        return sprintf('%s%s%s%s', $contents, PHP_EOL, $this->createLineForEnvFile($key, $value), PHP_EOL);
    }

    /**
     * Replaces existing env variable in file.
     *
     * @param string $contents Existing .env file contents
     * @param string $key Environment key
     * @param mixed $value Value
     * @return string Updated contents
     */
    protected function replaceEnvFile(string $contents, string $key, $value)
    {
        $regex = sprintf('/^(%s)=([^\r\n]+)$/m', $key);
        $sub = $this->createLineForEnvFile($key, $value);

        return preg_replace($regex, $sub, $contents);
    }

    /**
     * Generates line to insert into .env file.
     *
     * @param string $key Key
     * @param string $value Value
     * @return string
     */
    protected function createLineForEnvFile(string $key, $value)
    {
        return sprintf('%s=%s', $key, $this->transformEnvValue($value));
    }

    /**
     * Transforms value so it can be stored in .env file.
     *
     * @param mixed $value
     * @return string
     */
    protected function transformEnvValue($value)
    {
        switch (gettype($value)) {
            case 'boolean':
                $value = $value ? 'true' : 'false';

                break;
            case 'NULL':
                $value = 'null';

                break;
            case 'string':
                $value = sprintf('"%s"', $value);

                break;

            default:
                $value = (string) $value;

                break;
        }

        return $value;
    }

    /**
     * Gets the .env file path.
     *
     * @return string
     */
    protected function envPath()
    {
        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        } else {
            return sprintf('%s%s%s', $this->laravel->basePath(), DIRECTORY_SEPARATOR, '.env');
        }
    }
}
