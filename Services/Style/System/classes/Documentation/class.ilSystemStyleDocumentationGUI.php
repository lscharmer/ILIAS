<?php
require_once("Services/Style/System/classes/Documentation/class.ilKSDocumentationExplorerGUI.php");
require_once("Services/Style/System/classes/Documentation/class.ilKSDocumentationEntryGUI.php");
require_once("libs/composer/vendor/geshi/geshi/src/geshi.php");


/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleDocumentationGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilCtrl $ctrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var bool
     */
    protected $is_read_only = false;

    const ROOT_FACTORY_PATH = "./Services/Style/System/data/abstractDataFactory.php";
    const DATA_DIRECTORY = "./Services/Style/System/data";
    const DATA_FILE = "data.php";
    const SHOW_TREE = "system_styles_show_tree";
    public static $DATA_PATH;

    /**
     * ilSystemStyleDocumentationGUI constructor.
     * @param bool|false $read_only
     */
    public function __construct($read_only = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->global_screen = $DIC->globalScreen();

        $this->setIsReadOnly($read_only);

        self::$DATA_PATH= self::DATA_DIRECTORY . "/" . self::DATA_FILE;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        if ($this->is_read_only) {
            $this->resetForReadOnly();
        }
        $this->addGotoLink();
        $this->setGlobalScreenContext();
        $this->show();
    }

    protected function setGlobalScreenContext()
    {
        $context = $this->global_screen->tool()->context()->current();
        $context->addAdditionalData(self::SHOW_TREE, true);
    }

    public function show()
    {
        $content = "";


        $entry_gui = new ilKSDocumentationEntryGUI(
            $this
        );

        $content.= $entry_gui->renderEntry();
        $this->tpl->setContent($content);
    }

    protected function resetForReadOnly()
    {
        /**
         * @var ILIAS\DI\Container $DIC
         */
        global $DIC;

        $DIC->tabs()->clearTargets();

        /**
         * Since clearTargets also clears the help screen ids
         */
        $DIC->help()->setScreenIdComponent("sty");
        $DIC->help()->setScreenId("system_styles");

        $skin_id = $_GET["skin_id"];
        $style_id = $_GET["style_id"];

        $skin = ilSystemStyleSkinContainer::generateFromId($skin_id)->getSkin();
        $style = $skin->getStyle($style_id);

        $DIC["tpl"]->setTitle($DIC->language()->txt("documentation"));

        if ($style->isSubstyle()) {
            $DIC["tpl"]->setDescription(
                $this->lng->txt("ks_documentation_of_substyle")
                    . " '"
                    . $style->getName() . "' " .
                    $this->lng->txt("of_parent") . " '" . $skin->getStyle($style->getSubstyleOf())->getName() . "' " .
                    $this->lng->txt("from_skin") . " " . $skin->getName()
            );
        } else {
            $DIC["tpl"]->setDescription(
                $this->lng->txt("ks_documentation_of_style") . " '" . $style->getName() . "' " .
                    $this->lng->txt("from_skin") . " '" . $skin->getName() . "'"
            );
        }

        $DIC["ilLocator"]->clearItems();
        $DIC["tpl"]->setLocator();
    }

    protected function addGotoLink()
    {
        $this->tpl->setPermanentLink("stys", $_GET["ref_id"], "_" . $_GET["node_id"] . "_"
                . $_GET["skin_id"] . "_" . $_GET["style_id"]);
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->is_read_only;
    }

    /**
     * @param bool $is_read_only
     */
    public function setIsReadOnly($is_read_only)
    {
        $this->is_read_only = $is_read_only;
    }
}
