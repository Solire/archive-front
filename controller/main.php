<?php


namespace Slrfw\App\Front\Controller;

use Slrfw\Library\Registry;

/**
 * Class example of MainController with always call
 *
 * @category Application
 * @package  Controller
 * @author   Monnot Stéphane (Shin) <monnot.stephane@gmail.com>
 * @license  Licence Shin
 */
class Main extends \Slrfw\Library\Controller {

    /**
     *
     * @var \Slrfw\Model\utilisateur
     */
    protected $_utilisateurAdmin;

    /**
     * Always execute before other method in controller
     *
     * @return void
     */
    public function start() {
        parent::start();

        /** Set title of page ! */
        $this->_seo->setTitle($this->_project->getName());

        /** Noindex Nofollow pour tout */
//        $this->_seo->disableIndex();
//        $this->_seo->disableFollow();


        $this->_view->google_analytics = Registry::get('analytics');

        $this->_view->fil_ariane = null;

        $this->_gabaritManager = new \Slrfw\Model\gabaritManagerOptimized();

        /**
         * MODE PREVISUALISATION
         *
         * On teste si utilisateur de l'admin loggué
         *  = possibilité de voir le site sans tenir compte de la visibilité
         *
         */
        $this->_utilisateurAdmin = new \Slrfw\Library\Session('back');
        $this->_view->utilisateurAdmin = $this->_utilisateurAdmin;

        if ($this->_utilisateurAdmin->isConnected() && $this->_ajax == FALSE) {
            if (isset($_GET["mode_previsualisation"])) {
                $_SESSION["mode_previsualisation"] = (bool) $_GET["mode_previsualisation"];
            }

            if (!isset($_SESSION["mode_previsualisation"])) {
                $_SESSION["mode_previsualisation"] = 0;
            }

            $this->_gabaritManager->setModePrevisualisation($_SESSION["mode_previsualisation"]);

            //Inclusion Bootstrap twitter
            $this->_javascript->addLibrary('back/js/bootstrap/bootstrap.min.js', false);
            $this->_css->addLibrary('back/css/bootstrap/bootstrap.min.css', 'screen', false);

            $this->_view->site = Registry::get('project-name');
            $this->_view->modePrevisualisation = $_SESSION["mode_previsualisation"];

        }


        /** EXEMPLE DE RECUPERATION DES RUBRIQUE parent et leurs enfants */
        $this->_rubriques = $this->_gabaritManager->getList(ID_VERSION, ID_API, 0, array(3, 4, 5), TRUE);
        foreach ($this->_rubriques as $ii => $rubrique) {
            $pages = $this->_gabaritManager->getList(ID_VERSION, ID_API, $rubrique->getMeta('id'), FALSE, 1);
            $rubrique->setChildren($pages);

            foreach ($pages as $page) {
                $firstChild = $this->_gabaritManager->getFirstChild(ID_VERSION, $page->getMeta('id'));
                if ($firstChild)
                    $page->setFirstChild($firstChild);
            }
        }
        $this->_view->rubriques = $this->_rubriques;

        //Recupération des gabarits main
        $this->_view->mainPage = $this->_gabaritManager->getMain(ID_VERSION, ID_API);

        //On recupere la page elements communs qui sera disponible sur toutes les pages
        $this->_view->mainPage["element_commun"] = $this->_gabaritManager->getPage(ID_VERSION, ID_API, $this->_view->mainPage["element_commun"][0]->getMeta("id"), 0, FALSE, TRUE);

        $this->_view->breadCrumbs = array();
        $this->_view->breadCrumbs[] = array(
            "label" => "Accueil",
            "url" => "./",
        );

    }

}