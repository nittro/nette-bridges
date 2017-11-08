<?php

declare(strict_types=1);

namespace Nittro\Bridges\NittroUI;

use Nette\Http\SessionSection;


trait PresenterUtils {

    /** @var bool */
    private $redrawDefault = true;

    /** @var array */
    private $defaultSnippets = [
        'content'
    ];


    abstract public function isAjax() : bool;

    abstract public function isControlInvalid(string $snippet = NULL) : bool;

    /**
     * @param string $snippet
     * @param bool $redraw
     * @return void
     */
    abstract public function redrawControl($snippet = NULL, bool $redraw = TRUE) : void;


    /**
     * @param string $destination
     * @param array $args
     * @return string
     */
    abstract public function link(string $destination, $args = []) : string;

    /**
     * @param int $code
     * @param string $destination
     * @param array $args
     * @return void
     */
    abstract public function redirect($code, $destination = NULL, $args = []) : void;


    abstract public function hasFlashSession() : bool;

    abstract public function getFlashSession() : SessionSection;



    /************* Snippets *************/

    public function setRedrawDefault(bool $value = true) : self
    {
        $this->redrawDefault = $value;
        return $this;
    }

    public function willRedrawDefault() : bool
    {
        return $this->redrawDefault;
    }

    public function setDefaultSnippets(array $snippets) : self
    {
        $this->defaultSnippets = $snippets;
        return $this;
    }

    public function redrawDefault(bool $force = false) : self
    {
        if ($force || $this->redrawDefault && !$this->isControlInvalid()) {
            foreach ($this->defaultSnippets as $snippet) {
                $this->redrawControl($snippet);
            }
        }

        return $this;

    }



    /************* Redirects *************/

    public function postGet(string $destination, $args = []) : self
    {
        if ($this->isAjax()) {
            $this->payload->postGet = true;
            $this->payload->url = call_user_func_array([$this, 'link'], func_get_args());
        } else {
            call_user_func_array([$this, 'redirect'], func_get_args());
        }

        return $this;

    }

    public function allowAjax() : self
    {
        $this->payload->allowAjax = true;
        return $this;
    }

    public function disallowAjax() : self
    {
        $this->payload->allowAjax = false;
        return $this;
    }


    /************* Forms *************/

    public function allowFormReset() : self
    {
        $this->payload->allowReset = true;
        return $this;
    }

    public function disallowFormReset() : self
    {
        $this->payload->allowReset = false;
        return $this;
    }

    /************* Flash messages *************/

    public function exportFlashSession() : array
    {
        return $this->hasFlashSession()
            ? iterator_to_array($this->getFlashSession()->getIterator())
            : [];
    }
}
