<?php
/**
 * Represents PreCondition tags for use before parser main parser (on first OnParseDocument).
 *
 * @package modx
 */
class modPreConditionTag extends modTag
{
    /**
     * Overrides modTag::__construct to set the PreCondition Tag token
     * {@inheritdoc}
     *
     * @var modX $modx
     */
    function __construct(modX & $modx)
    {
        parent :: __construct($modx);
        $this->setToken('^');
    }

    /**
     * Get the raw source content of the field.
     *
     * {@inheritdoc}
     */
    public function getContent(array $options = array())
    {
        if (!$this->isCacheable() || !is_string($this->_content) || $this->_content === '') {
            if (isset($options['content']) && !empty($options['content'])) {
                $this->_content = $options['content'];
            } elseif ($this->modx->resource instanceof modResource) {
                if ($this->get('name') === 'content') {
                    $this->_content = $this->modx->resource->getContent($options);
                } else {
                    $this->_content = $this->modx->resource->get($this->get('name'));
                }
            }
        }
        return $this->_content;
    }

    /**
     * Process the modPreConditionTag and return the output
     *
     * {@inheritdoc}
     */
    public function process($properties = null, $content = null)
    {
        parent :: process($properties, $content);
        if (!$this->_processed) {
            $this->_output = $this->_content;
            $this->filterOutput();
            $this->cache();
            $this->_processed = true;
        }
        /* finally, return the processed element content */
        return $this->_output;
    }

}
