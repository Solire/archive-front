<?php

namespace App\Front\Controller;

use Slrfw\Registry;


class Middleoffice extends \Slrfw\Controller
{

    private $_cache = null;

    /**
     *
     * @var \Slrfw\Model\gabaritPage
     */
    private $_page = null;
    
    /**
     *
     * @var \Slrfw\Model\gabaritManagerOptimized
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
        $this->_cache = Registry::get('cache');
        $this->_utilisateurAdmin = new \Slrfw\Session('back', 'back');
        $this->_gabaritManager = new \Slrfw\Model\gabaritManagerOptimized();
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
        if (isset($_POST["id_gab_page"]) && intval($_POST["id_gab_page"]) > 0) {
            $this->_page = $this->_gabaritManager->getPage(ID_VERSION, ID_API, intval($_POST["id_gab_page"]));
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
