<?php
/**
 * MODx Revolution plugin allows manipulate data in specified tags only in templates before
 * before main parser (on first OnParseDocument) by using Conditional output modifiers syntax.
 *
 * @package notfoundparamalert
 * @var modX $modx
 */
if ($modx->event->name === 'OnParseTemplate') {

    if ('web' !== $modx->context->key) {
        return '';
    }

    include_once MODX_CORE_PATH . 'components/preconditions/model/preconditions/preconditions.php';
    include_once MODX_CORE_PATH . 'components/preconditions/model/preconditions/preconditiontag.php';

    if (false !== strpos($content, '[[^')) { // possibility for set token
        $preconditions = new modPreConditions($modx);
        $iterations = intval($modx->getOption('parser_max_iterations', null, 10, true));
        $preconditions->processElementPreTags('[[]]', $content, false, '[[', ']]', '^', $iterations);

        $modx->resource->Template->_output = $content;
    }

}