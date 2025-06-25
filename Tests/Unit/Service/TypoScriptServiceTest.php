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

namespace Codappix\PageSpecificTypoScript\Tests\Unit\Service;

use Codappix\PageSpecificTypoScript\Service\TypoScriptService;
use Codappix\PageSpecificTypoScript\Tests\Unit\Fixture\PackageManagerFixture;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Package\Package;

#[CoversClass(TypoScriptService::class)]
final class TypoScriptServiceTest extends TestCase
{
    protected Stub $packageManager;

    protected TypoScriptService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packageManager = self::createStub(PackageManagerFixture::class);
        $this->subject = new TypoScriptService($this->packageManager);
    }

    #[Test]
    public function noFilesAreReturnedIfNoExtensionExists(): void
    {
        $this->setActivePackages([]);
        self::assertSame(
            [],
            $this->subject->getFilesForPage(1, 'Setup'),
            'Expected empty files list when no extensions are installed.'
        );
    }

    #[Test]
    public function exceptionIsThrownForUnknownType(): void
    {
        $this->setActivePackages(['one_package']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Given type "NonExistingType" is not allowed. Only the following are supported: "Setup, Constants, PageTSconfig"');
        $this->expectExceptionCode(1551783115);

        $this->subject->getFilesForPage(1, 'NonExistingType');
    }

    #[Test]
    public function returnsIncludeForFile(): void
    {
        $result = $this->subject->getIncludeForFile('extension_key/Configuration/TypoScript/PageSpecific/10/setup.typoscript');

        self::assertSame('@import "EXT:extension_key/Configuration/TypoScript/PageSpecific/10/setup.typoscript"', $result);
    }

    /**
     * @param string[] $packageKeys
     */
    private function setActivePackages(array $packageKeys = []): void
    {
        $packages = [];

        foreach ($packageKeys as $packageKey) {
            $package = self::createStub(Package::class);
            $package
                ->method('getPackageKey')
                ->willReturn($packageKey)
            ;

            $packages[] = $package;
        }

        $this->packageManager
            ->method('getActivePackages')
            ->willReturn($packages)
        ;
    }
}
