<?php

namespace Tests\Unit;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

/**
 * Guards the credentials policy: sensitive values must be read out of config,
 * and env() must never be called from application code outside config/.
 */
class NoEnvInApplicationCodeTest extends TestCase
{
    public function test_application_code_never_calls_env_directly(): void
    {
        $violations = [];

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());

            if (preg_match('/(?<![\w>\\\\])env\s*\(/', $contents) === 1) {
                $violations[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }

        $this->assertSame(
            [],
            $violations,
            'env() must only be used inside config files. Violations: '.implode(', ', $violations)
        );
    }
}
