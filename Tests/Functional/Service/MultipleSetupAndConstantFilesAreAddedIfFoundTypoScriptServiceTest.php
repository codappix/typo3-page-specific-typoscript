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

namespace Codappix\PageSpecificTypoScript\Tests\Functional\Service;

use Codappix\PageSpecificTypoScript\Service\TypoScriptService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TypoScriptService::class)]
final class MultipleSetupAndConstantFilesAreAddedIfFoundTypoScriptServiceTest extends AbstractTypoScriptServiceTestCase
{
    protected function getFixtureExtensionFolderName(): string
    {
        return 'MultipleSetupAndConstantFilesAreAddedIfFound';
    }

    protected function getFixtureExtensionName(): string
    {
        return 'multiple_setup_and_constant_files_are_added_if_found';
    }

    #[Test]
    public function filesAreLoaded(): void
    {
        $this->makeSureConstantsAre([
            'multiple_setup_and_constant_files_are_added_if_found/Configuration/TypoScript/PageSpecific/100/Constants.typoscript',
            'multiple_setup_and_constant_files_are_added_if_found1/Configuration/TypoScript/PageSpecific/100/Constants.typoscript',
        ]);
        $this->makeSureNoSetupIs([
            'multiple_setup_and_constant_files_are_added_if_found0/Configuration/TypoScript/PageSpecific/100/Setup.typoscript',
            'multiple_setup_and_constant_files_are_added_if_found1/Configuration/TypoScript/PageSpecific/100/Setup.typoscript',
        ]);
    }
}
