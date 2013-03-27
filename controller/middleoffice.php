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
    protected $_gabaritManager;

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
        $this->_utilisateurAdmin = new \Slrfw\Session('back');
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
        if ($this->_utilisateurAdmin->isConnected() && $this->_ajax == FALSE) {
            $this->_view->site = Registry::get('project-name');
            $this->_view->modePrevisualisation = $_SESSION["mode_previsualisation"];

        }
        $this->_view->main(false);
        $this->_javascript->addLibrary('back/js/bootstrap/bootstrap.min.js');
        $this->_css->addLibrary('back/css/bootstrap/bootstrap.min.css', 'screen', false);
        $this->_page = $this->_gabaritManager->getPage(ID_VERSION, ID_API, $_GET["id_gab_page"]);
        $this->_view->page = $this->_page;
    }
    
    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function imageconfiguratorAction()
    {
        $this->_view->main(false);
    }

}
