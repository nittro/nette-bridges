<?php

namespace Nittro\Bridges\NittroUI;
use Nette\Http\SessionSection,
    Nette\Application\AbortException;



trait PresenterUtils {

    /** @var bool */
    private $redrawDefault = true;

    /** @var array */
    private $defaultSnippets = [
        'content'
    ];


    /**
     * @return bool
     */
    abstract public function isAjax();

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
     * @param string $destination
     * @param array $args
     * @return string
     */
    abstract public function link($destination, $args = []);

    /**
     * @param int $code
     * @param string $destination
     * @param array $args
     * @return void
     */
    abstract public function redirect($code, $destination = NULL, $args = []);

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
     * @return $this
     */
    public function redrawDefault()
    {
        if ($this->redrawDefault && !$this->isControlInvalid()) {
            foreach ($this->defaultSnippets as $snippet) {
                $this->redrawControl($snippet);
            }
        }

        return $this;

    }



    /************* Redirects *************/

    /**
     * @param string $destination
     * @param array $args
     * @throws AbortException
     * @return $this
     */
    public function postGet($destination, $args = [])
    {
        if ($this->isAjax()) {
            $this->payload->postGet = true;
            $this->payload->url = call_user_func_array([$this, 'link'], func_get_args());
        } else {
            call_user_func_array([$this, 'redirect'], func_get_args());
        }

        return $this;

    }

    /**
     * @return $this
     */
    public function allowAjax() {
        $this->payload->allowAjax = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disallowAjax() {
        $this->payload->allowAjax = false;
        return $this;
    }


    /************* Flash messages *************/

    /**
     * @return array
     */
    public function exportFlashSession()
    {
        return iterator_to_array($this->getFlashSession()->getIterator());
    }
}
