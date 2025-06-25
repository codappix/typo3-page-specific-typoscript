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

/**
 * Service to ease work with TypoScript.
 *
 * E.g. allows to fetch all files for a single page for autoloading.
 */
interface TypoScriptServiceInterface
{
    /**
     * Returns a list of relative file names for the given page type combination.
     *
     * "type" defines which files to search for. See $typeConfiguration property for allowed types.
     *
     * @throws InvalidArgumentException If $type is not valid.
     * @return string[]
     */
    public function getFilesForPage(int $pageUid, string $type): array;

    /**
     * Returns TypoScript include for given file.
     *
     * @param string $fileName Has to start with extensionkey, e.g.: extension_key/Configuration/file.typoscript
     */
    public function getIncludeForFile(string $fileName): string;
}
