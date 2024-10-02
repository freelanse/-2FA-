<?php
// Простая реализация 2FA через код подтверждения
function two_factor_auth_login($user_login, $user) {
    if (get_user_meta($user->ID, 'two_factor_auth_enabled', true)) {
        // Генерация и отправка кода на email
        $code = rand(100000, 999999);
        update_user_meta($user->ID, 'two_factor_code', $code);
        wp_mail($user->user_email, 'Ваш код для входа', 'Ваш код: ' . $code);

        // Перенаправляем на страницу ввода кода
        wp_redirect(home_url('/enter-2fa-code'));
        exit;
    }
}
add_action('wp_login', 'two_factor_auth_login', 10, 2);

// Проверка кода 2FA
function verify_two_factor_code() {
    if (isset($_POST['two_factor_code'])) {
        $user_id = get_current_user_id();
        $correct_code = get_user_meta($user_id, 'two_factor_code', true);

        if ($_POST['two_factor_code'] == $correct_code) {
            // Успешная аутентификация
            delete_user_meta($user_id, 'two_factor_code');
        } else {
            // Неверный код
            wp_logout();
            wp_die('Неверный код. Попробуйте снова.');
        }
    }
}
add_action('init', 'verify_two_factor_code');
?>
