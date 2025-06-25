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

namespace Codappix\PageSpecificTypoScript\Tests\Unit\EventListener;

use Codappix\PageSpecificTypoScript\EventListener\TemplateServiceEventListener;
use Codappix\PageSpecificTypoScript\Service\TypoScriptServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\AfterTemplatesHaveBeenDeterminedEvent;

#[CoversClass(TemplateServiceEventListener::class)]
final class TemplateServiceEventListenerTest extends TestCase
{
    private TemplateServiceEventListener $subject;

    private Stub $typoScriptService;

    private AfterTemplatesHaveBeenDeterminedEvent $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typoScriptService = self::createStub(TypoScriptServiceInterface::class);
        $this->typoScriptService
            ->method('getIncludeForFile')
            ->willReturnCallback(static fn (string $fileName) => '@import "EXT:' . $fileName . '"')
        ;

        $this->subject = new TemplateServiceEventListener($this->typoScriptService);
    }

    #[Test]
    public function nothingIsChangedIfNoFileExists(): void
    {
        $this->executeWithFiles([]);

        $this->makeSureConstantsAre([]);
        $this->makeSureSetupIs([]);
    }

    #[Test]
    public function singleConstantFileIsAddedIfFound(): void
    {
        $this->executeWithFiles([
            'Constants' => [
                '100' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/100/Constants.typoscript',
                ],
            ],
        ]);

        $this->makeSureConstantsAre([
            '@import "EXT:test_ext/Configuration/TypoScript/PageSpecific/100/Constants.typoscript"',
        ]);
        $this->makeSureSetupIs([]);
    }

    #[Test]
    public function multipleConstantFilesAreAddedIfFound(): void
    {
        $this->executeWithFiles([
            'Constants' => [
                '100' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/100/Constants.typoscript',
                ],
                '1000' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/1000/Constants.typoscript',
                ],
            ],
        ]);

        $this->makeSureConstantsAre([
            '@import "EXT:test_ext/Configuration/TypoScript/PageSpecific/1000/Constants.typoscript"',
            '@import "EXT:test_ext/Configuration/TypoScript/PageSpecific/100/Constants.typoscript"',
        ]);
        $this->makeSureSetupIs([]);
    }

    #[Test]
    public function singleSetupFileIsAddedIfFound(): void
    {
        $this->executeWithFiles([
            'Setup' => [
                '100' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/100/Setup.typoscript',
                ],
            ],
        ]);

        $this->makeSureConstantsAre([]);
        $this->makeSureSetupIs([
            '@import "EXT:test_ext/Configuration/TypoScript/PageSpecific/100/Setup.typoscript"',
        ]);
    }

    #[Test]
    public function multipleSetupFilesAreAddedIfFound(): void
    {
        $this->executeWithFiles([
            'Setup' => [
                '100' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/100/Setup.typoscript',
                ],
                '1000' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/1000/Setup.typoscript',
                ],
            ],
        ]);

        $this->makeSureConstantsAre([]);
        $this->makeSureSetupIs([
            '@import "EXT:test_ext/Configuration/TypoScript/PageSpecific/1000/Setup.typoscript"',
            '@import "EXT:test_ext/Configuration/TypoScript/PageSpecific/100/Setup.typoscript"',
        ]);
    }

    #[Test]
    public function multipleSetupAndConstantFilesAreAddedIfFound(): void
    {
        $this->executeWithFiles([
            'Constants' => [
                '100' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/100/Constants.typoscript',
                    'test_ext1/Configuration/TypoScript/PageSpecific/100/Constants.typoscript',
                ],
            ],
            'Setup' => [
                '100' => [
                    'test_ext0/Configuration/TypoScript/PageSpecific/100/Setup.typoscript',
                    'test_ext1/Configuration/TypoScript/PageSpecific/100/Setup.typoscript',
                ],
            ],
        ]);

        $this->makeSureConstantsAre([
            '@import "EXT:test_ext/Configuration/TypoScript/PageSpecific/100/Constants.typoscript"',
            '@import "EXT:test_ext1/Configuration/TypoScript/PageSpecific/100/Constants.typoscript"',
        ]);
        $this->makeSureSetupIs([
            '@import "EXT:test_ext0/Configuration/TypoScript/PageSpecific/100/Setup.typoscript"',
            '@import "EXT:test_ext1/Configuration/TypoScript/PageSpecific/100/Setup.typoscript"',
        ]);
    }

    #[Test]
    public function keepsExistingEntriesIfNothingIsAdded(): void
    {
        $this->typoScriptService
            ->method('getFilesForPage')
            ->willReturn([])
        ;

        $this->event = new AfterTemplatesHaveBeenDeterminedEvent(
            [
                ['uid' => 1],
                ['uid' => 10],
                ['uid' => 100],
                ['uid' => 1000],
            ],
            null,
            [
                [
                    'constants' => 'constant = 1',
                    'config' => 'SomeLine = TEXT',
                ],
            ]
        );

        $subject = $this->subject;
        $subject($this->event);

        $this->makeSureConstantsAre([
            'constant = 1',
        ]);
        $this->makeSureSetupIs([
            'SomeLine = TEXT',
        ]);
    }

    #[Test]
    public function keepsExistingEntriesIfSomethingIsAdded(): void
    {
        $this->typoScriptService
            ->method('getFilesForPage')
            ->willReturnCallback(static function (int $pageUid, string $type) {
                if ($pageUid === 100) {
                    return ['filePath'];
                }

                return [];
            })
        ;

        $this->event = new AfterTemplatesHaveBeenDeterminedEvent(
            [
                ['uid' => 1],
                ['uid' => 10],
                ['uid' => 100],
                ['uid' => 1000],
            ],
            null,
            [
                [
                    'constants' => 'constant = 1',
                    'config' => 'SomeLine = TEXT',
                ],
            ]
        );

        $subject = $this->subject;
        $subject($this->event);

        $this->makeSureConstantsAre([
            'constant = 1',
            '@import "EXT:filePath"',
        ]);
        $this->makeSureSetupIs([
            'SomeLine = TEXT',
            '@import "EXT:filePath"',
        ]);
    }

    #[Test]
    public function throwsExceptionIfPageUidIsMissing(): void
    {
        $this->event = new AfterTemplatesHaveBeenDeterminedEvent(
            [
                ['uid_missing' => 1],
            ],
            null,
            [
                [
                    'constants' => 'constant = 1',
                    'config' => 'SomeLine = TEXT',
                ],
            ]
        );

        $subject = $this->subject;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Given UID of rootline page is not numeric.');
        $this->expectExceptionCode(1750834989);

        $subject($this->event);
    }

    #[Test]
    public function throwsExceptionIfPageUidIsNoneNumeric(): void
    {
        $this->event = new AfterTemplatesHaveBeenDeterminedEvent(
            [
                ['uid' => 'words'],
            ],
            null,
            [
                [
                    'constants' => 'constant = 1',
                    'config' => 'SomeLine = TEXT',
                ],
            ]
        );

        $subject = $this->subject;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Given UID of rootline page is not numeric.');
        $this->expectExceptionCode(1750834989);

        $subject($this->event);
    }

    /**
     * @param array{string?: array{int: string[]}} $files
     */
    private function executeWithFiles(array $files = []): void
    {
        $this->typoScriptService
            ->method('getFilesForPage')
            ->willReturnCallback(static fn (int $pageUid, string $type) => $files[$type][$pageUid] ?? [])
        ;

        $this->event = new AfterTemplatesHaveBeenDeterminedEvent(
            [
                ['uid' => 1],
                ['uid' => 10],
                ['uid' => 100],
                ['uid' => 1000],
            ],
            null,
            []
        );

        $subject = $this->subject;
        $subject($this->event);
    }

    /**
     * @param string[] $constantsArray
     */
    private function makeSureConstantsAre(array $constantsArray): void
    {
        $message = 'Constants does not contain expected includes.';
        if ($constantsArray === []) {
            $message = 'Entries were added to constants, even if none should be found.';
        }

        $expected = implode(PHP_EOL, $constantsArray);
        $actual = implode(PHP_EOL, array_filter(array_map(
            static fn (array $row): string => $row['constants'],
            $this->event->getTemplateRows()
        )));
        self::assertSame($expected, $actual, $message);
    }

    /**
     * @param string[] $setupArray
     */
    private function makeSureSetupIs(array $setupArray): void
    {
        $message = 'Setup does not contain expected includes.';
        if ($setupArray === []) {
            $message = 'Entries were added to config, even if none should be found.';
        }

        $expected = implode(PHP_EOL, $setupArray);
        $actual = implode(PHP_EOL, array_filter(array_map(
            static fn (array $row): string => $row['config'],
            $this->event->getTemplateRows()
        )));

        self::assertSame($expected, $actual, $message);
    }
}
