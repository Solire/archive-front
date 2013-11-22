<?php

namespace App\Front\Controller;

use Slrfw\Registry;


class Page extends Main
{
    /**
     *
     * @var \Slrfw\Model\gabaritPage
     */
    protected $_page = null;

    /**
     *
     * @var \Slrfw\Model\gabaritPage[]
     */
    protected $_parents = null;

    /**
     * Accepte les rewritings
     *
     * @var boolean
     */
    public $acceptRew = true;

    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start()
    {
        parent::start();
    }

    public function startAction()
    {
        $this->_view->enable(false);

        /** En cas de prévisualisation. */
        if ($this->_utilisateurAdmin->isConnected() && isset($_POST['id_gabarit'])) {
            $this->_previsu();
        }
        else {
            $this->_display();
            $this->_page->setConnected($this->_utilisateurAdmin->isConnected());
        }

        if (isset($this->_parents[1])) {
            $firstChild = $this->_gabaritManager->getFirstChild(
                ID_VERSION, $this->_parents[1]->getMeta('id')
            );
            $this->_parents[1]->setFirstChild($firstChild);
        }

        //Balise META
        $this->_seo->setTitle($this->_page->getMeta('bal_title'));
        $this->_seo->setDescription($this->_page->getMeta('bal_descr'));
        $this->_seo->addKeyword($this->_page->getMeta('bal_key'));
        $this->_seo->setUrlCanonical($this->_page->getMeta('canonical'));
        if ($this->_page->getMeta('author') > 0) {
            $authors = $this->_view->mainPage['element_commun']->getBlocs('author_google')->getValues();
            foreach ($authors as $author) {
                if($author['id'] == $this->_page->getMeta('author')) {
                    $this->_seo->setAuthor($author['compte_google']);
                    $this->_seo->setAuthorName($author['nom_de_lauteur']);
                    break;
                }
            }
        }

        if ($this->_page->getMeta('no_index')) {
            $this->_seo->disableIndex();
        }

        $this->_view->page      = $this->_page;
        $this->_view->parents   = $this->_parents;

        $view = $this->_page->getGabarit()->getName();
        if (method_exists($this, '_' . $view . 'Gabarit')) {
            $this->{'_' . $view . 'Gabarit'}();
        }

        $this->shutdown();
        $this->_view->display('page', $view);
    }


    protected function _previsu()
    {
        $this->_page = $this->_gabaritManager->previsu($_POST);

        $this->_parents = array_reverse($this->_page->getParents());
        foreach ($this->_parents as $ii => $parent) {
            $this->_parents[$ii] = $this->_gabaritManager->getPage(
                $_POST['id_version'], $_POST['id_api'], $parent->getMeta('id'),
                0, false, false
            );

            $this->_fullRewriting[] = $parent->getMeta('rewriting') . '/';

            $this->_view->breadCrumbs[] = array(
                'label' => $parent->getMeta('titre'),
                'url'   => implode('/', $this->_fullRewriting) . '/',
            );
        }
    }

    protected function _display()
    {
        if (empty($this->rew)) {
            $this->rew[] = 'accueil';
        }
        $this->_parents         = array();
        $this->_fullRewriting   = array();

        $id_parent = 0 ;

        foreach ($this->rew as $ii => $rewriting) {
            if (!$rewriting) {
                $this->pageNotFound();
            }

            $last = ($ii == count($this->rew) - 1);

            $id_gab_page    = $this->_gabaritManager->getIdByRewriting(
                ID_VERSION, \Slrfw\FrontController::$idApiRew, $rewriting, $id_parent
            );
            if (!$id_gab_page) {
                $this->pageNotFound();
            }

            $page           = $this->_gabaritManager->getPage(
                ID_VERSION, \Slrfw\FrontController::$idApiRew, $id_gab_page, 0, $last, true
            );
            if (!$page) {
                $this->pageNotFound();
            }

            $this->_fullRewriting[]     = $rewriting;

            $this->_view->breadCrumbs[]  = array(
                'label'    => $page->getMeta('titre'),
                'url'      => implode('/', $this->_fullRewriting) . '/',
            );

            if ($last) {
                $this->_page        = $page;
            } else {
                $this->_parents[]   = $page;
            }

            $id_parent      = $id_gab_page;
        }
    }
}
