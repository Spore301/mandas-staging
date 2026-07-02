<?php
/**
 * Plugin support: Elementor Core Extensions.
 *
 * @package ThemeREX Addons
 * @since v2.30.0
 */

namespace TrxAddons\ElementorTemplates;

use TrxAddons\ElementorTemplates\Globals\Controller;
use TrxAddons\ElementorTemplates\Globals\ColorsEditor;
use TrxAddons\ElementorTemplates\Globals\TypographyEditor;
use TrxAddons\ElementorTemplates\Atomic\ColorVariableType;
use TrxAddons\ElementorTemplates\Atomic\SystemColorVariables;
use TrxAddons\ElementorTemplates\Atomic\FontVariableType;
use TrxAddons\ElementorTemplates\Atomic\SystemFontVariables;

use TrxAddons\ElementorTemplates\Templates\Library;

/**
 * Intializes Elementor Core Extensions on Elementor editing page.
 */
class ElementorTemplates extends Base {

	private $templates_library;
	private $colors_editor;
	private $typography_editor;
	private $color_variable_type;
	private $system_color_variables;
	private $font_variable_type;
	private $system_font_variables;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'elementor/controls/register', array( $this, 'register_controls' ) );

		// Add Theme Colors to Global Colors
		$this->colors_editor = new ColorsEditor();

		// Add Theme Fonts to Global Fonts
		$this->typography_editor = new TypographyEditor();

		// Add Theme Colors to Atomic Editor Vars
		$this->color_variable_type = new ColorVariableType();
		$this->system_color_variables = new SystemColorVariables();

		// Add Theme Fonts to Atomic Editor Vars
		$this->font_variable_type = new FontVariableType();
		$this->system_font_variables = new SystemFontVariables();

		// Templates Library Popup
		if ( trx_addons_is_theme_activated() ) {
			$this->templates_library = new Library();
		}

		$this->register_data_controllers();
	}

	/**
	 * Register custom Elementor control.
	 */
	public function register_controls() {
		\Elementor\Plugin::instance()->controls_manager->register( new Action() );
	}

	/**
	 * Register custom Elementor REST data controllers.
	 *
	 * @return void
	 */
	public function register_data_controllers() {
		\Elementor\Plugin::instance()->data_manager_v2->register_controller( new Controller() );
	}
}
