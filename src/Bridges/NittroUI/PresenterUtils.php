<?php

namespace Nittro\Bridges\NittroUI;

use Nette\Http\SessionSection;


trait PresenterUtils {
    use ComponentUtils;


    /** @var bool */
    private $redrawDefault = true;

    /** @var array */
    private $defaultSnippets = [
        'content'
    ];


    /**
     * @param string $snippet
     * @return bool
     */
    abstract public function isControlInvalid($snippet = NULL);

    /**
     * @param string $snippet
     * @param bool $redraw
     * @return void
     */
    abstract public function redrawControl($snippet = NULL, $redraw = TRUE);

    /**
     * @return bool
     */
    abstract public function hasFlashSession();

    /**
     * @return SessionSection
     */
    abstract public function getFlashSession();





    /************* Snippets *************/


    /**
     * @param bool $value
     * @return $this
     */
    public function setRedrawDefault($value = true)
    {
        $this->redrawDefault = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function willRedrawDefault()
    {
        return $this->redrawDefault;
    }

    /**
     * @param array $snippets
     * @return $this
     */
    public function setDefaultSnippets(array $snippets)
    {
        $this->defaultSnippets = $snippets;
        return $this;
    }

    /**
     * @param bool $force
     * @return $this
     */
    public function redrawDefault($force = false)
    {
        if ($force || $this->redrawDefault && !$this->isControlInvalid()) {
            foreach ($this->defaultSnippets as $snippet) {
                $this->redrawControl($snippet);
            }
        }

        return $this;

    }


    /************* Flash messages *************/

    /**
     * @return array
     */
    public function exportFlashSession()
    {
        return $this->hasFlashSession()
            ? iterator_to_array($this->getFlashSession()->getIterator())
            : [];
    }
}
