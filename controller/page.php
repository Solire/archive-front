<?php

namespace App\Front\Controller;

use Slrfw\Registry;


class Page extends Main
{
    /**
     *
     * @var \Slrfw\Model\gabaritPage
     */
    public $_page = null;

    /**
     *
     * @var \Slrfw\Model\gabaritPage[]
     */
    public $_parents = null;

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

    /**
     *
     *
     * @return void
     */
    public function startAction()
    {
        /**
         * En cas de prévisualisation.
         */
        if ($this->_utilisateurAdmin->isConnected()
            && isset($_POST['id_gabarit'])
        ) {
            $this->_previsu();
        } else {
            $this->_display();
            $this->_page->setConnected($this->_utilisateurAdmin->isConnected());
        }

        if (isset($this->_parents[1])) {
            $firstChild = $this->_gabaritManager->getFirstChild(
                ID_VERSION, $this->_parents[1]->getMeta('id')
            );
            $this->_parents[1]->setFirstChild($firstChild);
        }

        /**
         * Balise META
         */
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
        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('front');
        $hook->controller = $this;
        $hook->exec($view . 'Gabarit');

        $this->_view->setViewPath('page' . DS . $view);
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

            $breadCrumbs = array(
                'label' => $parent->getMeta('titre'),
            );
            if ($parent->getGabarit()->getView()) {
                $breadCrumbs['url'] = implode('/', $this->_fullRewriting) . '/';
            }
            $this->_view->breadCrumbs[] = $breadCrumbs;
        }
    }

    protected function _display()
    {
        $homepage = false;
        if (empty($this->rew)) {
            $homepage = true;
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

            /**
             * Dans le cas de la homepage, on part du principe que sont id est
             * toujours 1
             */
            if($homepage) {
                $id_gab_page = 1;
            } else {
                $id_gab_page = $this->_gabaritManager->getIdByRewriting(
                    ID_VERSION, \Slrfw\FrontController::$idApiRew, $rewriting,
                    $id_parent
                );
            }

            if (!$id_gab_page) {
                $this->pageNotFound();
            }

            $page = $this->_gabaritManager->getPage(
                ID_VERSION, \Slrfw\FrontController::$idApiRew, $id_gab_page, 0,
                $last, true
            );
            if (!$page) {
                $this->pageNotFound();
            }

            $this->_fullRewriting[]     = $rewriting;

            $breadCrumbs = array(
                'label' => $page->getMeta('titre'),
            );
            if ($page->getGabarit()->getView()) {
                $breadCrumbs['url'] = implode('/', $this->_fullRewriting) . '/';
            }
            $this->_view->breadCrumbs[] = $breadCrumbs;

            if ($last) {
                $this->_page      = $page;
            } else {
                $this->_parents[] = $page;
            }

            $id_parent      = $id_gab_page;
        }
    }
}
