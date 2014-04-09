<?php

namespace App\Front\Controller;

use Slrfw\Registry;


class Middleoffice extends \Slrfw\Controller
{
    /**
     *
     * @var \Slrfw\Model\gabaritPage
     */
    private $_page = null;

    /**
     *
     * @var \Slrfw\Session
     */
    protected $_utilisateurAdmin;

    /**
     *
     * @var \Slrfw\Model\gabaritManager
     */
    public $_gabaritManager;

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
        $this->_utilisateurAdmin = new \Slrfw\Session('back', 'back');
        $this->_gabaritManager = new \Slrfw\Model\gabaritManager();
    }

    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function toolbarbackAction()
    {
        $this->_view->utilisateurAdmin = $this->_utilisateurAdmin;
        if ($this->_utilisateurAdmin->isConnected()) {
            $this->_view->site = Registry::get('project-name');
            $this->_view->modePrevisualisation = $_SESSION["mode_previsualisation"];
        }
        $this->_view->main(false);
        $this->_javascript->addLibrary('back/js/bootstrap/bootstrap.min.js');
        $this->_javascript->addLibrary('back/js/main.js');
        $this->_css->addLibrary('back/css/bootstrap/bootstrap.min.css', 'screen', false);
        if (isset($_POST["id_gab_page"])
                && intval($_POST["id_gab_page"]) > 0
                && isset($_POST["id_api"])
                && intval($_POST["id_api"])) {
            $this->_page = $this->_gabaritManager->getPage(ID_VERSION, intval($_POST["id_api"]), intval($_POST["id_gab_page"]));

            if ($this->_utilisateurAdmin->isConnected()) {
                $this->_page->makeVisible = true;
                $this->_page->makeHidden  = true;

                $hook = new \Slrfw\Hook();
                $hook->setSubdirName('back');

                $hook->permission     = null;
                $hook->utilisateur    = $this->_utilisateurAdmin;
                $hook->visible        = $this->_page->getMeta('visible') == 0 ? 1 : 0;
                $hook->ids            = $data['id'];
                $hook->id_version     = ID_VERSION;

                $hook->exec('pagevisible');

                /**
                 * On récupère la permission du hook,
                 * on interdit uniquement si la variable a été modifié à false.
                 */
                if ($hook->permission === false) {
                    if ($hook->visible == 1) {
                        $this->_page->makeVisible = false;
                    } else {
                        $this->_page->makeHidden  = false;
                    }
                }
            }

            $this->_view->page = $this->_page;
        }

        $this->_view->currentUrl = $_POST["currentUrl"];
    }

    /**
     * Dialog pour la configuration des images
     *
     * @return void
     */
    public function imageconfiguratorAction()
    {
        $this->_view->main(false);
    }

    /**
     * Dialog pour la modification des zones HTML
     *
     * @return void
     */
    public function htmleditorAction()
    {
        $this->_view->main(false);
    }

}
