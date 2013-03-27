<?php


namespace App\Front\Controller;

use Slrfw\Registry;

/**
 * Class example of MainController with always call
 *
 * @category Application
 * @package  Controller
 * @author   Monnot Stéphane (Shin) <monnot.stephane@gmail.com>
 * @license  Licence Shin
 */
class Main extends \Slrfw\Controller {

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
        $this->_seo->setTitle($this->_mainConfig->get("project", "name"));

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
        $this->_utilisateurAdmin = new \Slrfw\Session('back');
        $this->_view->utilisateurAdmin = $this->_utilisateurAdmin;

        if ($this->_utilisateurAdmin->isConnected() && $this->_ajax == FALSE && !isset($_POST['id_gabarit'])) {
            if (isset($_GET["mode_previsualisation"])) {
                $_SESSION["mode_previsualisation"] = (bool) $_GET["mode_previsualisation"];
            }

            if (!isset($_SESSION["mode_previsualisation"])) {
                $_SESSION["mode_previsualisation"] = 0;
            }

            $this->_gabaritManager->setModePrevisualisation($_SESSION["mode_previsualisation"]);

            //Inclusion mercury editor pour l'édition des pages (middle office)
            $this->_javascript->addLibrary('app/back/js/mercury/mercury_loader.js?src=' . $this->_url . 'app/back/js/mercury&pack=bundled&visible=false');

            //Inclusion Bootstrap twitter
            $this->_javascript->addLibrary('back/js/bootstrap/bootstrap.min.js');
            $this->_css->addLibrary('back/css/bootstrap/bootstrap.min.css', 'screen', false);

            $this->_view->site = Registry::get('project-name');
            $this->_view->modePrevisualisation = $_SESSION["mode_previsualisation"];

        }

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