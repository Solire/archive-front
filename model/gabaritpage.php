<?php

namespace Slrfw\Model;

/**
 * Description of page
 *
 * @author thomas
 */
class gabaritPage extends gabaritBloc {
    /**
     *
     * @var array
     */
    private $_meta = array();

    /**
     *
     * @var array
     */
    private $_version = array();

    /**
     *
     * @var array
     */
    private $_blocs = array();

    /**
     *
     * @var array
     */
    private $_parents = array();

    /**
     *
     * @param array $meta
     */
    public function __construct() {
        $this->_values = array();
    }

    /**
     *
     * @param array $meta
     */
    public function setMeta($meta) {
        $this->_meta = $meta;
        $this->_id = $meta['id'];
    }

    public function setVersion($data) {
        $this->_version = $data;
    }

    /**
     *
     * @param array $values
     */
    public function setValues($values) {
        $this->_values = $values;
    }

    /**
     *
     * @param array $values
     */
    public function setValue($key, $value) {
        $this->_values[$key] = $value;
    }

    /**
     *
     * @param array $blocs tableau de page
     */
    public function setBlocs($blocs) {
        $this->_blocs = $blocs;
    }

    /**
     *
     * @param array $parents
     */
    public function setParents($parents) {
        $this->_parents = $parents;
    }

    /**
     *
     * @param gabaritPage $child
     */
    public function setChildren($children) {
        $this->_children= $children;
    }

    /**
     *
     * @param gabaritPage $child
     */
    public function getChildren() {
        return $this->_children;
    }

    /**
     *
     * @param gabaritPage $firstChild
     */
    public function setFirstChild($firstChild) {
        $this->_firstChild = $firstChild;
    }

    // GETTERS

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getMeta($key = NULL) {
        if ($key != NULL) {
            if (is_array($this->_meta) && array_key_exists($key, $this->_meta))
                return $this->_meta[$key];

            return NULL;
        }

        return $this->_meta;
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getVersion($key = NULL) {
        if ($key != NULL) {
            if (is_array($this->_version) && array_key_exists($key, $this->_version))
                return $this->_version[$key];

            return NULL;
        }

        return $this->_version;
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getValues($key = NULL) {
        if ($key == NULL)
            return $this->_values;

        if (is_array($this->_values) && array_key_exists($key, $this->_values))
            return $this->_values[$key];

        return '';
    }

    /**
     *
     * @return type
     */
    public function getBlocs($name = NULL) {
        if ($name == NULL || !isset ($this->_blocs[$name]))
            return $this->_blocs;

        return $this->_blocs[$name];
    }

    /**
     *
     * @param int $id_gabarit
     * @return gabaritPage
     */
    public function getParent($i) {
        if (array_key_exists($i, $this->_parents))
            return $this->_parents[$i];

        return FALSE;
    }

    /**
     *
     * @param int $id_gabarit
     * @return gabaritPage
     */
    public function getParents() {
        return $this->_parents;
    }

    public function getFirstChild(){
        return $this->_firstChild;
    }

    /**
     * Retourne le formulaire de création/d'édition de la page
     * 
     * @param string $action        adresse de l'action du formulaire
     * @param string $retour        adresse de retour
     * @param string $upload_path   emplacement des images
     * @param bool   $mobile        si vrai affiche la version mobile
     * @param bool   $meta          affiche les metas
     * @param bool   $_301_editable affiche les redirections 301
     * @param bool   $meta_titre    affiche le titre principal
     * @param string $extension     extension de la page '/', '.html'...
     * @param int    $versionId     identifiant de la version
     * @param array  $redirections  tableau des redirections
     * 
     * @return string formulaire au format HTML
     */
    public function getForm($action, $retour, $upload_path, $redirections = array())
    {
        $versionId          = $this->_version['id'];
        
        $metaId             = isset($this->_meta['id'])
                            ? $this->_meta['id']
                            : 0;
        $metaLang           = isset($this->_meta['id_version'])
                            ? $this->_meta['id_version']
                            : 1;
        $noMeta             = !$this->_gabarit->getMeta() || !$metaId
                            ? ' style="display: none;" '
                            : '';
        $noMetaTitre        = !$this->_gabarit->getMeta_titre()
                            ? ' style="display: none;" '
                            : '';
        $noRedirections301  = !$this->_gabarit->get301_editable()
                            ? ';display: none'
                            : '';
        $parentSelect       = '';

        $api = $this->_gabarit->getApi();

        $redirections   = count($redirections) == 0
                        ? array("")
                        : $redirections;

        if ($metaId && $this->_meta['id_parent'] > 0) {
            $parentSelect = '<div class="line">'
                          . '<label for="id_parent">' . $this->_gabarit->getGabaritParent("label") . '</label>'
                          . '<select disabled="disabled"><option>' . $this->getParent(0)->getMeta("titre") . '</option></select>'
                          . '<input type="hidden" disabled="disabled" name="id_parent" value="' . $this->getParent(0)->getMeta("id") . '" />'
                          . '</div>';
        }
        elseif (!$metaId && $this->_gabarit->getIdParent() > 0) {
            $parentSelect = '<div class="line">'
                          . '<label for="id_parent">' . $this->_gabarit->getGabaritParent("label") . '</label>'
                          . $this->_gabarit->getParentsSelect()
                          . '</div>';
        }

        $allchamps = $this->_gabarit->getChamps();

        ob_start();
        include __DIR__ . "/gabarit/form/default/default.phtml";
        $form = ob_get_clean();

		return $form;
	}

    /**
     *
     * @return type
     */
	public function buildForm($upload_path) {
        $form   = '<input type="hidden" name="id_' . $this->_gabarit->getTable()
                . '" value="' . (isset($this->_values['id']) ? $this->_values['id'] : '')
                . '" />';

        $allchamps = $this->_gabarit->getChamps();

        $id_gab_page = isset($this->_meta['id']) ? $this->_meta['id'] : 0;

        foreach ($allchamps as $name_group => $champs) {
            $form .= '<fieldset><legend>' . $name_group . '</legend><div ' . ($id_gab_page ? 'style="display:none;"' : '') . '>';
            foreach ($champs as $champ) {
                $value = isset($this->_values[$champ['name']]) ? $this->_values[$champ['name']] : '';
                $id = isset($this->_meta['id_version']) ? $this->_meta['id_version'] : '';
                $form .= $this->_buildChamp($champ, $value, $id, $upload_path, $id_gab_page);
            }
            $form .= '</div></fieldset>';
        }

        foreach ($this->_blocs as $blocName => $bloc) {
            $form .=  $bloc->buildForm($upload_path, $id_gab_page,
                $this->_version['id']);
        }
        
		return $form;
	}

}
