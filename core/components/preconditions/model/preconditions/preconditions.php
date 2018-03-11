<?php
/**
 * Represents PreCondition tags for use before main parser (on first OnParseDocument).
 *
 * @package modx
 */
class modPreConditions
{
    /**
     * A reference to the modX instance
     * @var modX $modx
     */
    public $modx = null;

    /**
     * A recursion status (when parse sub elements)
     * @var bool $_substatus
     */
    protected $_substatus = false;

    /**
     * Class constructor
     * @var modX $modx
     */
    function __construct(modX & $modx)
    {
        $this->modx =& $modx;
    }

    /**
     * Collects and processes any set of pre tags.
     *
     * @param string $parentTag The tag representing the element processing this
     * tag.  Pass an empty string to allow parsing without this recursion check.
     * @param string $content The content to process and act on (by reference).
     * @param boolean $processUncacheable Determines if noncacheable tags are to
     * be processed (default= false).
     * @param string $prefix The characters that define the start of a tag (default= "[[").
     * @param string $suffix The characters that define the end of a tag (default= "]]").
     * @param string $token The characters that define the default tag token (default= "^").
     * @param integer $depth The maximum iterations to recursively process tags
     * returned by prior passes, 0 by default.
     * @return int The number of processed tags
     */
    public function processElementPreTags($parentTag, & $content, $processUncacheable = false, $prefix = "[[", $suffix = "]]", $token = '^', $depth = 0)
    {
        $depth = $depth > 0 ? $depth - 1 : 0;
        $processed = 0;
        $tags = [];

        if ($this->collectElementTags($content, $tags, $prefix, $suffix, $token)) {
            $tagMap = [];
            foreach ($tags as $tag) {
                if ($tag[0] === $parentTag) {
                    $tagMap[$tag[0]] = '';
                    $processed++;
                    continue;
                }

                $tagOutput = $this->processPreTag($tag, $processUncacheable);

                if ($tagOutput === null || $tagOutput === false) {
                    $tagMap[$tag[0]] = '';
                    $processed++;
                }
                elseif ($tagOutput !== null && $tagOutput !== false) {
                    $tagMap[$tag[0]] = $tagOutput;
                    if ($tag[0] !== $tagOutput) $processed++;
                }
            }
            $this->mergeTagOutput($tagMap, $content);
            if ($processed > 0 && $depth > 0) {
                $processed+= $this->processElementPreTags($parentTag, $content, $processUncacheable, $prefix, $suffix, $token, $depth);
            }
        }

        $this->_substatus = false;
        return $processed;
    }

    /**
     * Processes a modPreConditionTag tag and returns the result.
     *
     * @param string $tag A full tag string parsed from content.
     * @param boolean $processUncacheable
     * @return string The output of the processed tag.
     */
    public function processPreTag($tag, $processUncacheable = false) {
        $element = null;
        $elementOutput = null;

        $outerTag = $tag[0];
        $innerTag = $tag[1];

        /* Avoid all processing for comment tags, e.g. [[- comments here]] */
        if (substr($innerTag, 0, 1) === '-') {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[PreConditions]: unbelievable but tag with comments was catched.');
            return '';
        }

        $this->_substatus = true;
        /* collect any nested element tags in the innerTag and process them */
        $this->processElementPreTags($outerTag, $innerTag, $processUncacheable);
        $outerTag = '[[' . $innerTag . ']]';

        // process tag parts
        $tagParts = xPDO :: escSplit('?', $innerTag, '`', 2);
        $tagName = trim($tagParts[0]);
        $tagPropString = null;
        if (isset ($tagParts[1])) {
            $tagPropString = trim($tagParts[1]);
        }

        $token = substr($tagName, 0, 1);
        $tokenOffset = 0;

        if ('^' === $token) {
            $tagName = substr($tagName, 1 + $tokenOffset);
            if (is_array($this->modx->resource->_fieldMeta) && in_array($this->realname($tagName), array_keys($this->modx->resource->_fieldMeta))) {
                $element = new modPreConditionTag($this->modx);
                $element->set('name', $tagName);
                $element->setTag('');
                $element->setCacheable(false);
                $elementOutput = $element->process($tagPropString);
            } else {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[PreConditions]: wrong condition ' . $tagName . ' was passed.');
            }
        } else {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[PreConditions]: unbelievable but tags with wrong token was catched.');
        }
        if (($elementOutput === null || $elementOutput === false) && $outerTag !== $tag[0]) {
            $elementOutput = $outerTag;
        }
        if ($this->modx->getDebug() === true) {
            $this->modx->log(xPDO::LOG_LEVEL_DEBUG, "Processing {$outerTag} as {$innerTag} using tagname {$tagName}:\n" . print_r($elementOutput, 1) . "\n\n");
            /* $this->modx->cacheManager->writeFile(MODX_BASE_PATH . 'parser.log', "Processing {$outerTag} as {$innerTag}:\n" . print_r($elementOutput, 1) . "\n\n", 'a'); */
        }

        return $elementOutput;
    }

    /**
     * Collects element tags in content and return them as array.
     *
     * @param string $origContent The content to collect tags from.
     * @param array &$matches An array in which the collected tags will be
     * stored (by reference)
     * @param string $prefix The characters that define the start of a tag (default= "[[").
     * @param string $suffix The characters that define the end of a tag (default= "]]").
     * @param string $token The characters that define the default tag token (default= "^").
     * @return integer The number of tags collected from the content.
     */
    public function collectElementTags($origContent, array &$matches, $prefix = '[[', $suffix = ']]', $token = '^')
    {
        $matchCount = 0;
        if (!empty($origContent) && is_string($origContent) && strpos($origContent, $prefix) !== false) {
            if (!$this->_substatus) {
                if ($matchCount = preg_match_all('/(?:\s+)?((?<!\`)\[\[(\\' . $token. '\w+((?:\:\w+\=\`.*\`)+)?)\]\](?!\s*\`))(?:\s+)?/isSU', $origContent, $found, PREG_SET_ORDER)) {
                    $matches = array_map(function($v) { return [$v[1], $v[2]]; }, $found);
                    //$matches = array_map(function($v) { return $v[1]; }, $found);
                }
            } else {
                if ($matchCount = preg_match('/(\[\[(\\' . $token. '\w+((?:\:\w+\=\`.*\`)+)?)\]\])/isS', $origContent, $found)) {
                    $matches[] = [ $found[1], $found[2] ];
                }
            }
        }
        return $matchCount;
    }

    /**
     * Copied from modParser (core/model/modx/modparser.class.php)
     * Gets the real name of an element containing filter modifiers.
     *
     * @param string $unfiltered The unfiltered name of a {@link modElement}.
     * @return string The name minus any filter modifiers.
     */
    public function realname($unfiltered) {
        $filtered = $unfiltered;
        $split = xPDO :: escSplit(':', $filtered);
        if ($split && isset($split[0])) {
            $filtered = $split[0];
            $propsetSplit = xPDO :: escSplit('@', $filtered);
            if ($propsetSplit && isset($propsetSplit[0])) {
                $filtered = $propsetSplit[0];
            }
        }
        return $filtered;
    }

    /**
     * Copied from modParser (core/model/modx/modparser.class.php)
     * Mergeso processed tag output intprovided content string.
     *
     * @param array $tagMap An array with full tags as keys and processed output
     * as the values.
     * @param string $content The content to merge the tag output with (passed by
     * reference).
     */
    public function mergeTagOutput(array $tagMap, & $content) {
        if (!empty ($content) && is_array($tagMap) && !empty ($tagMap)) {
            $content = str_replace(array_keys($tagMap), array_values($tagMap), $content);
        }
    }

}
