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
final class NothingIsChanedIfNoFileExistsTypoScriptServiceTest extends AbstractTypoScriptServiceTestCase
{
    protected function getFixtureExtensionFolderName(): string
    {
        return 'NothingIsChangedIfNoFileExists';
    }

    protected function getFixtureExtensionName(): string
    {
        return 'nothing_is_changed_if_no_file_exists';
    }

    #[Test]
    public function filesAreLoaded(): void
    {
        $this->makeSureConstantsAre([]);
        $this->makeSureNoSetupIs([]);
    }
}
