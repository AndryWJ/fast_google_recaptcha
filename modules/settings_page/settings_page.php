<?php
include_once FGR_DIR . '/includes/CMB2-develop/init.php';

class fastGoogleRecaptchaOptions extends fastGoogleRecaptcha {

    static function init(){
        add_action( 'cmb2_admin_init', ['fastGoogleRecaptchaOptions','create_theme_options']);
        add_action('admin_menu', ['fastGoogleRecaptchaOptions','register_submenu_page']);
        add_action('admin_menu', function(){
            add_menu_page( 'Налаштування Recaptcha', 'Recaptcha', 'manage_options', 'recaptcha_settings', ['fastGoogleRecaptchaOptions','add_menu'], '', 4 );
        });
    }

    static function add_menu(){
        ?>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?></h2>
            <h3>1. <?=" Як отримати \"Site key\" та \"Private key\" описано <a target=\"__blank\" href=\"https://developers.google.com/recaptcha/docs/v3\">тут</a>"?></h3>
            <h3>2. Для роботи перевірки вкладаємо в форму "<?= htmlentities('<input type="hidden" name="frg_token"></input>'); ?>"</h3>
            <h3>3. В обробнику форми отримуємо "робот чи не робот" так: fastGoogleRecaptcha::self::is_robot();</h3>
    
            <form action="options.php" method="POST">
                <?php
                    settings_fields("opt_group"); 
                    do_settings_sections("opt_page");
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    static function create_theme_options(){
        $fields = array(
            [
                'name' => 'Site key',
                'id' => 'fgr_site_key',
                'type' => 'text'
            ],
            [
                'name' => 'Private key',
                'id' => 'fgr_private_key',
                'type' => 'text'
            ],
            [
                'name' => 'Перевіряти всі форми form-7 на стороні сервера автоматично ?',
                'id' => 'fgr_form7check',
                'type' => 'checkbox'
            ]
        );

        new_cmb2_box( array(
            'id'           => FGR_CLASS_NAME,
            'title'        => esc_html__( 'Налаштування', FGR_CLASS_NAME ),
            'object_types' => array( 'options-page' ),
            'option_key'      => 'recaptcha_settings',
            'save_button'     => esc_html__( 'Зберегти зміни', FGR_CLASS_NAME ),
            'fields' => $fields
        ));
    }

}

fastGoogleRecaptchaOptions::init();

