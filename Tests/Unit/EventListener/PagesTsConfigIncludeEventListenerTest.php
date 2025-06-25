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

use Codappix\PageSpecificTypoScript\EventListener\PagesTsConfigIncludeEventListener;
use Codappix\PageSpecificTypoScript\Service\TypoScriptServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;

#[CoversClass(PagesTsConfigIncludeEventListener::class)]
final class PagesTsConfigIncludeEventListenerTest extends TestCase
{
    private Stub $typoScriptService;

    private PagesTsConfigIncludeEventListener $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typoScriptService = self::createStub(TypoScriptServiceInterface::class);
        $this->typoScriptService
            ->method('getIncludeForFile')
            ->willReturnCallback(static fn (string $fileName) => '@import "EXT:' . $fileName . '"')
        ;

        $this->subject = new PagesTsConfigIncludeEventListener($this->typoScriptService);
    }

    #[Test]
    public function nothingIsChangedIfNoFileExists(): void
    {
        $this->makeSureTsConfigArrayIs([], $this->executeWithFiles([]));
    }

    #[Test]
    public function singlePageTSconfigFileIsAddedIfFound(): void
    {
        $this->makeSureTsConfigArrayIs([
            '@import "EXT:test_ext/Configuration/PageTSconfig/PageSpecific/100/Setup.tsconfig"',
        ], $this->executeWithFiles([
            'PageTSconfig' => [
                '100' => [
                    'test_ext/Configuration/PageTSconfig/PageSpecific/100/Setup.tsconfig',
                ],
            ],
        ]));
    }

    #[Test]
    public function multiplePageTSconfigFilesAreAddedIfFound(): void
    {
        $this->makeSureTsConfigArrayIs([
            '@import "EXT:test_ext/Configuration/PageTSconfig/PageSpecific/1000/Setup.tsconfig"',
            '@import "EXT:test_ext/Configuration/PageTSconfig/PageSpecific/100/Setup.tsconfig"',
        ], $this->executeWithFiles([
            'Setup' => [
                '100' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/100/Setup.typoscript',
                ],
            ],
            'PageTSconfig' => [
                '100' => [
                    'test_ext/Configuration/PageTSconfig/PageSpecific/100/Setup.tsconfig',
                ],
                '1000' => [
                    'test_ext/Configuration/PageTSconfig/PageSpecific/1000/Setup.tsconfig',
                ],
            ],
        ]));
    }

    #[Test]
    public function onlyExpectedFilesAreIncluded(): void
    {
        $this->makeSureTsConfigArrayIs([
            '@import "EXT:test_ext/Configuration/PageTSconfig/PageSpecific/1000/Setup.tsconfig"',
            '@import "EXT:test_ext/Configuration/PageTSconfig/PageSpecific/100/Setup.tsconfig"',
        ], $this->executeWithFiles([
            'Constants' => [
                '100' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/100/Constants.typoscript',
                ],
            ],
            'Setup' => [
                '100' => [
                    'test_ext/Configuration/TypoScript/PageSpecific/100/Setup.typoscript',
                ],
            ],
            'PageTSconfig' => [
                '100' => [
                    'test_ext/Configuration/PageTSconfig/PageSpecific/100/Setup.tsconfig',
                ],
                '1000' => [
                    'test_ext/Configuration/PageTSconfig/PageSpecific/1000/Setup.tsconfig',
                ],
            ],
        ]));
    }

    #[Test]
    public function throwsExceptionIfPageUidIsMissing(): void
    {
        $event = new ModifyLoadedPageTsConfigEvent([], [
            ['uid_missing' => 0],
        ]);

        $subject = $this->subject;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Given UID of rootline page is not numeric.');
        $this->expectExceptionCode(1750834911);

        $subject($event);
    }

    #[Test]
    public function throwsExceptionIfPageUidIsNoneNumeric(): void
    {
        $event = new ModifyLoadedPageTsConfigEvent([], [
            ['uid' => 'word'],
        ]);

        $subject = $this->subject;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Given UID of rootline page is not numeric.');
        $this->expectExceptionCode(1750834911);

        $subject($event);
    }

    /**
     * @param array{string?: array{int: string[]}} $files
     */
    private function executeWithFiles(array $files = []): ModifyLoadedPageTsConfigEvent
    {
        $this->typoScriptService
            ->method('getFilesForPage')
            ->willReturnCallback(static fn (int $pageUid, string $type) => $files[$type][$pageUid] ?? [])
        ;

        $event = new ModifyLoadedPageTsConfigEvent([], [
            ['uid' => 0],
            ['uid' => 1],
            ['uid' => 10],
            ['uid' => 100],
            ['uid' => 1000],
        ]);

        $subject = $this->subject;
        $subject($event);

        return $event;
    }

    /**
     * @param mixed[] $expectedTsConfigArray
     */
    private function makeSureTsConfigArrayIs(array $expectedTsConfigArray, ModifyLoadedPageTsConfigEvent $event): void
    {
        $message = 'TSconfig does not contain expected includes.';
        if ($expectedTsConfigArray === []) {
            $message = 'Entries were added to TSconfig, even if none should be found.';
        }

        self::assertSame($expectedTsConfigArray, $event->getTsConfig(), $message);
    }
}
