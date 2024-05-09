<?php

namespace LittleApps\LittleJWT\Tests\Concerns;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

trait CreatesEnvFile
{
    /**
     * Creates an empty .env file.
     *
     * @return $this
     */
    protected function createEnvFile(): static {
        $this->writeEnvFile(
            $this->getEnvFilePath(),
            "# This file was automatically generated for testing"
        );

        return $this;
    }

    /**
     * Creates a .env file with existing variables.
     *
     * @param array $keyValues
     * @param bool $useExisting If true, merges with existing .env variables.
     * @return $this
     */
    protected function createEnvFileWithExisting(array $keyValues, bool $useExisting = false): static {
        $variables = $useExisting ? array_merge($_ENV, $keyValues) : $keyValues;

        $lines = array_map(function ($value, $key) {
            return $this->transformEnvKeyValueToLine($key, $value);
        }, $variables, array_keys($variables));

        $this->writeEnvFile(
            $this->getEnvFilePath(),
            "# This file was automatically generated for testing" . PHP_EOL .
            implode(PHP_EOL, $lines)
        );

        return $this;
    }

    /**
     * Reloads env variables from .env file.
     *
     * @return $this
     */
    protected function reloadEnv(): static {
        (new LoadEnvironmentVariables)->bootstrap($this->app);

        return $this;
    }

    /**
     * Asserts env variable is set.
     *
     * @param string $variable
     * @return $this
     */
    protected function assertEnvSet(string $variable) {
        return $this->assertTrue(isset($_ENV[$variable]), "The environment variable '{$variable}' is not set.");
    }

    /**
     * Asserts .env variables equals expected.
     *
     * @param string $variable
     * @param mixed $expected
     * @return $this
     */
    protected function assertEnvEquals(string $variable, $expected) {
        return $this->assertEquals($expected, $this->getEnv($variable));
    }

    /**
     * Asserts .env variables doesn't equal expected.
     *
     * @param string $variable
     * @param mixed $expected
     * @return $this
     */
    protected function assertEnvNotEquals(string $variable, $expected) {
        return $this->assertNotEquals($expected, $this->getEnv($variable));
    }

    /**
     * Gets env variable value.
     *
     * @param string $variable
     * @return mixed
     */
    protected function getEnv(string $variable) {
        return $_ENV[$variable];
    }

    /**
     * Writes to .env file.
     *
     * @param string $path
     * @param string $contents
     * @return boolean
     */
    protected function writeEnvFile(string $path, string $contents): bool {
        return (bool) file_put_contents($path, $contents);
    }

    /**
     * Gets path to .env file.
     *
     * @return string
     */
    protected function getEnvFilePath(): string {
        return $this->app->environmentFilePath();
    }

    /**
     * Transforms key and value to line for .env file.
     *
     * @param string $key
     * @param mixed $value
     * @return string
     */
    protected function transformEnvKeyValueToLine(string $key, $value): string {
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
                $value = !empty($value) ? sprintf('"%s"', (string) $value) : '(empty)';

            default:
                $value = sprintf('"%s"', (string) $value);

                break;
        }

        return $value;
    }
}
