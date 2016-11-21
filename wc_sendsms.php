<?php
/*
Plugin Name: SendSMS - WooCommerce
Plugin URI: https://ameya.ro/
Description: Acest modul permite trimiterea de sms-uri la schimbarea de status al comenzilor WooCommerce
Version: 1.0.3
Author: Ameya Solutions
Author URI: https://ameya.ro
*/

$pluginDir = plugin_dir_path(__FILE__);
$pluginDirUrl = plugin_dir_url(__FILE__);

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}


# admin page
add_action('admin_menu', 'wc_sendsms_add_menu');

function wc_sendsms_add_menu()
{
    add_menu_page(
        __('SendSMS', 'wc_sendsms'),
        __('SendSMS', 'wc_sendsms'),
        'manage_options',
        'wc_sendsms_main',
        'wc_sendsms_main',
        plugin_dir_url(__FILE__).'images/sendsms.png'
    );

    add_submenu_page(
        'wc_sendsms_main',
        __('Setări', 'wc_sendsms'),
        __('Setări', 'wc_sendsms'),
        'manage_options',
        'wc_sendsms_login',
        'wc_sendsms_login'
    );
}

function wc_sendsms_main()
{
    ?>
    <div class="wrap">
        <h2><?=__('SendSMS pentru WooCommerce', 'wc_sendsms')?></h2>
        <br />
        <p>Pentru a folosi modulul, te rugăm să introduci datele de autentificare în pagina de setări.</p><br />
        <p>În pagina de setări, sub datele de autentificare, vei găsi câte un câmp text pentru fiecare status disponibil în WooCommerce. Va trebui să introduci un mesaj pentru câmpurile la care vrei să se trimită sms de înștiințare. Dacă un câmp va fi gol, atunci sms-ul nu se va trimite.</p>
        <p>Exemplu: Dacă dorești să trimiți un mesaj când se schimbă statusul comenzii în finalizată (Completed) atunci va trebui să completezi un mesaj în câmpul text <strong>"Mesaj: Completed"</strong>.</p><br />
        <p>Poți introduce variabile care se vor completa în funcție de datele de comandă.</p>
        <p>Exemplu mesaj: <strong>Salut {billing_first_name}. Comanda ta cu numarul {order_number} a fost finalizata.</strong></p>
        <br /><br /><p style="text-align: center"><a href="https://ameya.ro" target="_blank"><img src="<?=plugin_dir_url(__FILE__).'images/ameya.png'?>" style="width: 200px; height: auto;" /></a></p>
    </div>
    <?php
}

# options
add_action('admin_init', 'wc_sendsms_admin_init');
function wc_sendsms_admin_init()
{
    # for login
    register_setting(
        'wc_sendsms_plugin_options',
        'wc_sendsms_plugin_options',
        'wc_sendsms_plugin_options_validate'
    );
    add_settings_section(
        'wc_sendsms_plugin_login',
        '',
        'wc_sendsms_plugin_login_section_text',
        'wc_sendsms_plugin'
    );
    add_settings_field(
        'wc_sendsms_plugin_options_username',
        __('Nume utilizator', 'wc_sendsms'),
        'wc_sendsms_settings_display_username',
        'wc_sendsms_plugin',
        'wc_sendsms_plugin_login'
    );
    add_settings_field(
        'wc_sendsms_plugin_options_password',
        __('Parola', 'wc_sendsms'),
        'wc_sendsms_settings_display_password',
        'wc_sendsms_plugin',
        'wc_sendsms_plugin_login'
    );
    add_settings_field(
        'wc_sendsms_plugin_options_from',
        __('Label expeditor', 'wc_sendsms'),
        'wc_sendsms_settings_display_from',
        'wc_sendsms_plugin',
        'wc_sendsms_plugin_login'
    );
    add_settings_field(
        'wc_sendsms_plugin_options_content',
        __('Statusuri', 'wc_sendsms'),
        'wc_sendsms_settings_display_content',
        'wc_sendsms_plugin',
        'wc_sendsms_plugin_login'
    );
}

function wc_sendsms_login()
{
    ?>
    <div class="wrap">
        <h2><?=__('SendSMS - Date autentificare', 'wc_sendsms')?></h2>
        <?php settings_errors(); ?>
        <form action="options.php" method="post">
            <?php settings_fields('wc_sendsms_plugin_options'); ?>
            <?php do_settings_sections('wc_sendsms_plugin'); ?>

            <input name="Submit" type="submit" class="button button-primary button-large" value="<?=__('Salvează', 'wc_sendsms')?>" />
        </form>
    </div>
    <?php
}

function wc_sendsms_plugin_login_section_text()
{
    //
}

function wc_sendsms_settings_display_username()
{
    $options = get_option('wc_sendsms_plugin_options');
    if (!empty($options) && is_array($options) && isset($options['username'])) {
        $username = $options['username'];
    } else {
        $username = '';
    }
    echo '
    <input id="wc_sendsms_settings_username" name="wc_sendsms_plugin_options[username]" type="text" value="'.$username.'" style="width: 400px;" />';
}

function wc_sendsms_settings_display_password()
{
    $options = get_option('wc_sendsms_plugin_options');
    if (!empty($options) && is_array($options) && isset($options['password'])) {
        $password = $options['password'];
    } else {
        $password = '';
    }
    echo '
    <input id="wc_sendsms_settings_password" name="wc_sendsms_plugin_options[password]" type="password" value="'.$password.'" style="width: 400px;" />';
}

function wc_sendsms_settings_display_from()
{
    $options = get_option('wc_sendsms_plugin_options');
    if (!empty($options) && is_array($options) && isset($options['from'])) {
        $from = $options['from'];
    } else {
        $from = '';
    }
    echo '
    <input id="wc_sendsms_settings_from" name="wc_sendsms_plugin_options[from]" type="text" value="'.$from.'" style="width: 400px;" /> <span>maxim 11 caractere alfa numerice</span>';
}

function wc_sendsms_settings_display_content()
{
    echo '<p>Variabile disponibile: {billing_first_name}, {billing_last_name}, {shipping_first_name}, {shipping_last_name}, {order_number}, {order_date}</p><br />';
    $options = get_option('wc_sendsms_plugin_options');
    if (!empty($options) && is_array($options) && isset($options['content'])) {
        $content = $options['content'];
    } else {
        $content = array();
    }
    $statuses = wc_get_order_statuses();
    foreach ($statuses as $key => $value) {
        echo '<label>Mesaj: '.$value.'</label><br />
    <textarea id="wc_sendsms_settings_content_'.$key.'" name="wc_sendsms_plugin_options[content]['.$key.']" style="width: 400px; height: 100px;">'.(isset($content[$key])?$content[$key]:'').'</textarea><br />';
    }
}

function wc_sendsms_plugin_options_validate($input)
{
    return $input;
}

# magic
add_action("woocommerce_order_status_changed", "wc_sendsms_order_status_changed");

function wc_sendsms_order_status_changed($order_id, $checkout = null)
{
    global $woocommerce;
    $order = new WC_Order($order_id);
    $status = $order->status;

    $options = get_option('wc_sendsms_plugin_options');
    if (!empty($options) && is_array($options) && isset($options['content'])) {
        $content = $options['content'];
    } else {
        $content = array();
    }
    if (!empty($options) && is_array($options) && isset($options['username'])) {
        $username = $options['username'];
    } else {
        $username = '';
    }
    if (!empty($options) && is_array($options) && isset($options['password'])) {
        $password = $options['password'];
    } else {
        $password = '';
    }
    if (!empty($options) && is_array($options) && isset($options['from'])) {
        $from = $options['from'];
    } else {
        $from = '';
    }

    if (!empty($username) && !empty($password)) {
        if (isset($content['wc-' . $status]) && !empty($content['wc-' . $status])) {
            # replace variables
            $message = $content['wc-' . $status];
            $replace = array(
                '{billing_first_name}' => wc_sendsms_clean_diacritice($order->billing_first_name),
                '{billing_last_name}' => wc_sendsms_clean_diacritice($order->billing_last_name),
                '{shipping_first_name}' => wc_sendsms_clean_diacritice($order->shipping_first_name),
                '{shipping_last_name}' => wc_sendsms_clean_diacritice($order->shipping_last_name),
                '{order_number}' => $order_id,
                '{order_date}' => date('d.m.Y', strtotime($order->order_date))
            );
            foreach ($replace as $key => $value) {
                $message = str_replace($key, $value, $message);
            }

            # generate valid phone number
            $phone = wc_sendsms_validate_phone($order->billing_phone);

            if (!empty($phone)) {
                # send sms
                wc_sendsms_send($username, $password, $phone, $message, $from);
            }
        }
    }
}

function wc_sendsms_send($username, $password, $phone, $message, $from)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_URL, 'http://api.sendsms.ro/json?action=message_send&username='.urlencode($username).'&password='.urlencode($password).'&from='.urlencode($from).'&to='.urlencode($phone).'&text='.urlencode($message));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Connection: keep-alive"));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $status = curl_exec($curl);
}

function wc_sendsms_validate_phone($phone)
{
    $phone = preg_replace('/\D/', '', $phone);
    if (substr($phone, 0, 1) == '0' && strlen($phone) == 10) {
        $phone = '4'.$phone;
    } elseif (substr($phone, 0, 1) != '0' && strlen($phone) == 9) {
        $phone = '40'.$phone;
    } elseif (strlen($phone) == 13 && substr($phone, 0, 2) == '00') {
        $phone = substr($phone, 2);
    }
    return $phone;
}

function wc_sendsms_clean_diacritice($string)
{
    $balarii = array(
        "\xC4\x82",
        "\xC4\x83",
        "\xC3\x82",
        "\xC3\xA2",
        "\xC3\x8E",
        "\xC3\xAE",
        "\xC8\x98",
        "\xC8\x99",
        "\xC8\x9A",
        "\xC8\x9B",
        "\xC5\x9E",
        "\xC5\x9F",
        "\xC5\xA2",
        "\xC5\xA3",
        "\xC3\xA3",
        "\xC2\xAD",
        "\xe2\x80\x93");
    $cleanLetters = array("A", "a", "A", "a", "I", "i", "S", "s", "T", "t", "S", "s", "T", "t", "a", " ", "-");
    return str_replace($balarii, $cleanLetters, $string);
}
