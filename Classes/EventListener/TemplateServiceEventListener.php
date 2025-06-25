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
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\AfterTemplatesHaveBeenDeterminedEvent;

final class TemplateServiceEventListener
{
    public function __construct(
        private readonly TypoScriptServiceInterface $typoScriptService
    ) {
    }

    /**
     * @return mixed[]
     */
    private function getTemplateRowForPage(int $pageUid): array
    {
        $templateRow = [
            'pid' => $pageUid,
            'uid' => $pageUid,
            'title' => 'Auto generated for page: ' . $pageUid,

            'constants' => '',
            'config' => '',

            'description' => '',
            'root' => 0,
            'clear' => 0,
            'static_file_mode' => 0,
            'includeStaticAfterBasedOn' => 0,
            'include_static_file' => '',
            'basedOn' => '',
        ];

        foreach ($this->typoScriptService->getFilesForPage($pageUid, 'Constants') as $file) {
            $templateRow['constants'] .= $this->typoScriptService->getIncludeForFile($file) . PHP_EOL;
        }

        foreach ($this->typoScriptService->getFilesForPage($pageUid, 'Setup') as $file) {
            $templateRow['config'] .= $this->typoScriptService->getIncludeForFile($file) . PHP_EOL;
        }

        $templateRow['constants'] = trim($templateRow['constants']);
        $templateRow['config'] = trim($templateRow['config']);

        if ($templateRow['constants'] !== '' || $templateRow['config'] !== '') {
            return $templateRow;
        }

        return [];
    }

    public function __invoke(AfterTemplatesHaveBeenDeterminedEvent $event): void
    {
        $templateRows = $event->getTemplateRows();

        foreach (array_reverse($event->getRootline()) as $page) {
            if (
                is_array($page) === false
                || is_numeric($page['uid'] ?? null) === false
            ) {
                throw new \RuntimeException('Given UID of rootline page is not numeric.', 1750834989);
            }

            $templateRows[] = $this->getTemplateRowForPage((int)$page['uid']);
        }

        $event->setTemplateRows(array_filter($templateRows));
    }
}
