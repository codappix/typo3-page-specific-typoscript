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
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(TypoScriptService::class)]
abstract class AbstractTypoScriptServiceTestCase extends FunctionalTestCase
{
    private TypoScriptService $subject;

    protected function setUp(): void
    {
        $this->testExtensionsToLoad = [
            'codappix/typo3-page-specific-typoscript',
            'typo3conf/ext/codappix_pagespecific_typoscript/Tests/Functional/Service/TypoScriptServiceTest/Extensions/' . $this->getFixtureExtensionFolderName() . '/' . $this->getFixtureExtensionName(),
            'typo3conf/ext/codappix_pagespecific_typoscript/Tests/Functional/Service/TypoScriptServiceTest/Extensions/' . $this->getFixtureExtensionFolderName() . '/' . $this->getFixtureExtensionName() . '0',
            'typo3conf/ext/codappix_pagespecific_typoscript/Tests/Functional/Service/TypoScriptServiceTest/Extensions/' . $this->getFixtureExtensionFolderName() . '/' . $this->getFixtureExtensionName() . '1',
        ];

        parent::setUp();

        $subject = $this->get(TypoScriptService::class);
        self::assertInstanceOf(TypoScriptService::class, $subject);
        $this->subject = $subject;
    }

    /**
     * @return string The folder name providing the fixture extensions.
     */
    abstract protected function getFixtureExtensionFolderName(): string;

    /**
     * @return string The name of the fixture extensions.
     */
    abstract protected function getFixtureExtensionName(): string;

    /**
     * @param mixed[] $constantsArray
     */
    protected function makeSureConstantsAre(array $constantsArray): void
    {
        $message = 'Constants does not contain expected includes.';
        if ($constantsArray === []) {
            $message = 'Entries were added to constants, even if none should be found.';
        }

        self::assertSame($constantsArray, $this->subject->getFilesForPage(100, 'Constants'), $message);
    }

    /**
     * @param mixed[] $setupArray
     */
    protected function makeSureNoSetupIs(array $setupArray): void
    {
        $message = 'Setup does not contain expected includes.';
        if ($setupArray === []) {
            $message = 'Entries were added to config, even if none should be found.';
        }

        self::assertSame($setupArray, $this->subject->getFilesForPage(100, 'Setup'), $message);
    }
}
