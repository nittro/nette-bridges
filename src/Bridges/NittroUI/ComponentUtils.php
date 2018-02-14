<?php

declare(strict_types=1);

namespace Nittro\Bridges\NittroUI;

use Nette\Application\UI\Presenter;


trait ComponentUtils {

    abstract public function getPresenter(bool $throw = TRUE) : ?Presenter;

    abstract public function getSnippetId(string $name) : string;


    /************* Redirects *************/

    public function postGet(string $destination, $args = []) : self
    {
        $presenter = $this->getPresenter();

        if ($presenter->isAjax()) {
            $presenter->payload->postGet = true;
            $presenter->payload->url = call_user_func_array([$presenter, 'link'], func_get_args());
        } else {
            call_user_func_array([$presenter, 'redirect'], func_get_args());
        }

        return $this;
    }

    public function allowAjax() : self
    {
        $this->getPresenter()->payload->allowAjax = true;
        return $this;
    }

    public function disallowAjax() : self
    {
        $this->getPresenter()->payload->allowAjax = false;
        return $this;
    }


    /************* Forms *************/

    public function allowFormReset() : self
    {
        $this->getPresenter()->payload->allowReset = true;
        return $this;
    }

    public function disallowFormReset() : self
    {
        $this->getPresenter()->payload->allowReset = false;
        return $this;
    }


    /************* Dialogs *************/

    public function openInDialog(string $snippet, string $name) : self
    {
        $this->getPresenter()->payload->dialogs[$name] = $this->getSnippetId($snippet);
        return $this;
    }

    public function closeDialog(string $name) : self
    {
        $this->getPresenter()->payload->dialogs[$name] = false;
        return $this;
    }

}