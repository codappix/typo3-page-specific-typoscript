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

namespace Codappix\PageSpecificTypoScript\EventListener;

use Codappix\PageSpecificTypoScript\Service\TypoScriptServiceInterface;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;

final class PagesTsConfigIncludeEventListener
{
    public function __construct(
        private readonly TypoScriptServiceInterface $typoScriptService
    ) {
    }

    public function __invoke(ModifyLoadedPageTsConfigEvent $event): void
    {
        foreach (array_reverse($event->getRootLine()) as $page) {
            if (
                is_array($page) === false
                || is_numeric($page['uid'] ?? null) === false
            ) {
                throw new \RuntimeException('Given UID of rootline page is not numeric.', 1750834911);
            }

            $files = $this->typoScriptService->getFilesForPage((int)$page['uid'], 'PageTSconfig');

            foreach ($files as $file) {
                $event->addTsConfig($this->typoScriptService->getIncludeForFile($file));
            }
        }
    }
}
