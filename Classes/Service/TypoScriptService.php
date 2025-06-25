<?php

declare(strict_types=1);

/*
 * Copyright (C) 2025 Daniel Siepmann <daniel.siepmann@codappix.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

namespace Codappix\PageSpecificTypoScript\Service;

use InvalidArgumentException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to ease work with TypoScript.
 *
 * E.g. allows to fetch all files for a single page for autoloading.
 */
final class TypoScriptService implements TypoScriptServiceInterface
{
    public function __construct(
        private readonly PackageManager $packageManager
    ) {
    }

    public function getFilesForPage(int $pageUid, string $type): array
    {
        $files = [];

        foreach ($this->packageManager->getActivePackages() as $extension) {
            $files[] = $this->getFileFromExtension($extension->getPackageKey(), $pageUid, $type);
        }

        // use array_values in order to re index keys after filtering.
        // This eases phpunit tests
        return array_values(array_filter($files));
    }

    public function getIncludeForFile(string $fileName): string
    {
        return '@import "EXT:' . $fileName . '"';
    }

    /**
     * Either returns the file path or an empty string, if file does not exist.
     */
    private function getFileFromExtension(string $extensionKey, int $pageUid, string $type): string
    {
        $file = sprintf(
            $this->getTypeFilePattern($type),
            $extensionKey,
            $pageUid,
            $type
        );

        $absoluePath = GeneralUtility::getFileAbsFileName('EXT:' . $file);
        if (is_file($absoluePath)) {
            return $file;
        }

        return '';
    }

    private function getTypeFilePattern(string $type): string
    {
        $configurations = $this->getTypeConfigurations();

        if (array_key_exists($type, $configurations) === false) {
            throw new InvalidArgumentException(
                'Given type "' . $type . '" is not allowed.'
                . ' Only the following are supported: "' . implode(', ', array_keys($configurations)) . '"',
                1551783115
            );
        }

        return $configurations[$type]['filePattern'];
    }

    /**
     * Configured allowed types for includes.
     *
     * Each type is a key on root level, containing an array with further settings.
     *
     * @return array{'Setup': array{'filePattern': string}, 'Constants': array{'filePattern': string},'PageTSconfig': array{'filePattern': string} }
     */
    private function getTypeConfigurations(): array
    {
        return  [
            'Setup' => [
                'filePattern' => '%s/Configuration/TypoScript/PageSpecific/%d/%s.typoscript',
            ],
            'Constants' => [
                'filePattern' => '%s/Configuration/TypoScript/PageSpecific/%d/%s.typoscript',
            ],
            'PageTSconfig' => [
                'filePattern' => '%s/Configuration/TSconfig/PageSpecific/%d/Setup.tsconfig',
            ],
            // Not yet in use, activate and test as soon as needed
            // 'UserTSconfig' => [
            //     'filePattern' => '%s/Configuration/UserTSconfig/PageSpecific/%d/Setup.tsconfig',
            // ],
        ];
    }
}
