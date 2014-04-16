<?php
/**
 * Class example of MainController with always call
 *
 * @category Application
 * @package  Controller
 * @author   smonnot <smonnot@solire.fr>
 * @license  Solire http://www.solire.fr/
 */

namespace App\Front\Controller;

use Slrfw\Registry;

/**
 * Class example of MainController with always call
 *
 * @category Application
 * @package  Controller
 * @author   smonnot <smonnot@solire.fr>
 * @license  Solire http://www.solire.fr/
 */
class Main extends \Slrfw\Controller
{
    /**
     *
     * @var \Slrfw\Model\utilisateur
     */
    public $_utilisateurAdmin;

    /**
     *
     * @var \Slrfw\Model\gabaritManager
     */
    public  $_gabaritManager;

    /**
     * Fonction éxécutée avant l'execution de la fonction relative à la page en cours
     *
     * @return void
     * @hook front/ shutdown Avant l'inclusion de la vue
     */
    public function start() {
        parent::start();

        $this->_seo->setTitle($this->_mainConfig->get("project", "name"));

        $this->_view->currentUrl = $this->getCurrentUrl();

        $this->_view->google_analytics = Registry::get('analytics');

        $this->_view->fil_ariane = null;

        $this->_gabaritManager = new \Slrfw\Model\gabaritManager();

        /**
         * Mode prévisualisation,
         * On teste si utilisateur de l'admin est connecté et donc si il a la
         * possibilité de voir le site sans tenir compte de la visibilité
         */
        $this->_utilisateurAdmin = new \Slrfw\Session('back', 'back');
        $this->_view->utilisateurAdmin = $this->_utilisateurAdmin;

        $this->_view->modePrevisuPage = false;

        if ($this->_utilisateurAdmin->isConnected()
            && $this->_ajax == FALSE
        ) {
            if (!isset($_POST['id_gabarit'])) {
                if (isset($_GET["mode_previsualisation"])) {
                    $_SESSION["mode_previsualisation"] = (bool) $_GET["mode_previsualisation"];
                }

                if (!isset($_SESSION["mode_previsualisation"])) {
                    $_SESSION["mode_previsualisation"] = 0;
                }

                $this->_gabaritManager->setModePrevisualisation($_SESSION["mode_previsualisation"]);

                $this->_view->site = Registry::get('project-name');
                $this->_view->modePrevisualisation = $_SESSION["mode_previsualisation"];
            } else {
                $this->_view->modePrevisuPage = true;
            }
        }

        /**
         * Recupération des gabarits main
         */
        $this->_view->mainPage = $this->_gabaritManager->getMain(ID_VERSION, ID_API);

        /**
         * On recupere la page elements communs qui sera disponible sur toutes
         * les pages
         */
        $this->_view->mainPage["element_commun"] = $this->_gabaritManager->getPage(
            ID_VERSION, ID_API,
            $this->_view->mainPage["element_commun"][0]->getMeta("id"), 0,
            false, true);

        $this->_view->breadCrumbs = array();
        $this->_view->breadCrumbs[] = array(
            "label" => $this->_('Accueil'),
            "url" => "./",
        );

        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('front');

        $hook->controller = $this;

        $hook->exec('start');

    }

    /**
     * Fonction éxécutée après l'execution de la fonction relative à la page en cours
     *
     * @return void
     * @hook front/ shutdown Avant l'inclusion de la vue
     */
    public function shutdown()
    {
        parent::shutdown();

        /**
         * Chargement des executions automatiques
         */
        $this->loadExec('shutdown');

        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('front');

        $hook->controller = $this;

        $hook->exec('shutdown');
    }

    /**
     *
     * @return type
     */
    public function getCurrentUrl()
    {
        $currenturl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $currenturl;
    }
}

