<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Codappix\PageSpecificTypoScript\EventListener\PagesTsConfigIncludeEventListener;
use Codappix\PageSpecificTypoScript\EventListener\TemplateServiceEventListener;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\AfterTemplatesHaveBeenDeterminedEvent;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services
        ->load('Codappix\\PageSpecificTypoScript\\', '../Classes/*')
    ;

    $services->get(TemplateServiceEventListener::class)
        ->tag('event.listener', [
            'event' => AfterTemplatesHaveBeenDeterminedEvent::class,
        ])
    ;
    $services->get(PagesTsConfigIncludeEventListener::class)
        ->tag('event.listener', [
            'event' => ModifyLoadedPageTsConfigEvent::class,
        ])
    ;
};
