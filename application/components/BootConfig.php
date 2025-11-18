<?php

namespace IndependentNiche\application\components;

use function IndependentNiche\prn;
use function IndependentNiche\prnx;

defined('\ABSPATH') || exit;

/**
 * BootConfig class file
 *
 * @author Independent Developer
 * @link https://github.com/independent-niche-generator
 * @copyright Copyright &copy; 2025 Independent Niche Generator
 */
abstract class BootConfig extends Config
{

    public function render_input($args)
    {
        if (!empty($args['type']))
            $type = $args['type'];
        else
            $type = 'text';
        if (!empty($args['class']))
            $class = $args['class'];
        else
            $class = '';

        if ($args['is_invalid'])
            $class .= ' is-invalid';

        echo '<div class="row">';
        echo '<div class="col-md-9 col-lg-7">';

        echo '<div class="form-floating mb-3">';
        echo '<input name="' . esc_attr($args['option_name']) . '[' . esc_attr($args['name']) . ']" id="' . esc_attr($args['label_for']) . '" type="' . esc_attr($type) . '" placeholder="' . esc_attr($args['title']) . '" value="' . esc_attr($args['value']) . '" class="form-control ' . esc_attr($class) . '"';
        if (!empty($args['autofocus']) && $args['autofocus'])
            echo ' autofocus';
        if (!empty($args['required']) && $args['required'])
            echo ' required';
        if (!empty($args['maxlength']) && $args['maxlength'])
            echo ' maxlength="' . esc_attr($args['maxlength']) . '"';
        if (!empty($args['height']) && $args['height'])
            echo ' height="' . esc_attr($args['height']) . '"';
        echo '>';
        echo '<label for="' . esc_attr($args['label_for']) . '">' . esc_attr($args['title']) . '</label>';

        if ($args['description'])
            echo '<div class="form-text">' . wp_kses_post($args['description']) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    public function render_textarea($args)
    {
        if (!empty($args['type']))
            $type = $args['type'];
        else
            $type = 'text';
        if (!empty($args['class']))
            $class = $args['class'];
        else
            $class = '';

        if (!empty($args['height']))
            $height = $args['height'];
        else
            $height = '92px';

        if ($args['is_invalid'])
            $class .= ' is-invalid';

        echo '<div class="row">';
        echo '<div class="col-md-9 col-lg-7">';

        echo '<div class="form-floating mb-3">';
        echo '<textarea style="height: ' . esc_attr($height) . '" name="' . esc_attr($args['option_name']) . '[' . esc_attr($args['name']) . ']" id="' . esc_attr($args['label_for']) . '" type="' . esc_attr($type) . '" placeholder="' . esc_attr($args['title']) . '" class="form-control ' . esc_attr($class) . '"';
        if (!empty($args['autofocus']) && $args['autofocus'])
            echo ' autofocus';
        if (!empty($args['required']) && $args['required'])
            echo ' required';
        if (!empty($args['maxlength']) && $args['maxlength'])
            echo ' maxlength="' . esc_attr($args['maxlength']) . '"';

        echo '>' . esc_attr($args['value']) . '</textarea>';
        echo '<label for="' . esc_html($args['label_for']) . '">' . esc_attr($args['title']) . '</label>';
        if ($args['description'])
        {
            echo '<div class="form-text">';
            echo wp_kses_post($args['description']);
            $this->render_help_icon($args);
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    public function render_dropdown($args)
    {
        if (empty($args['dropdown_options']))
        {
            echo ' - ';
            return;
        }

        $class = 'form-select';
        if ($args['is_invalid'])
            $class .= ' is-invalid';

        $id = $args['name'] . '_section';

        echo '<div class="row mb-3" id="' . esc_attr($id) . '">';
        echo '<div class="col-md-9 col-lg-7">';

        echo '<div class="form-floating">';
        echo '<select style="max-width: 100%;" class="' . esc_attr($class) . '" name="' . esc_attr($args['option_name']) . '['
            . esc_attr($args['name']) . ']" id="'
            . esc_attr($args['label_for']) . '" value="'
            . esc_attr($args['value']), '"';

        if (!empty($args['required']) && $args['required'])
            echo ' required';
        echo '>';
        foreach ($args['dropdown_options'] as $option_value => $option_name)
        {
            if ($option_value === $args['value'])
                $selected = ' selected="selected" ';
            else
                $selected = '';
            echo '<option value="' . esc_attr($option_value) . '"' . $selected . '>' . esc_html($option_name) . '</option>';
        }
        echo '</select>';
        echo '<label class="form-label" for="' . esc_attr($args['label_for']) . '">' . esc_attr($args['title']) . '</label>';

        echo '</div>';
        if ($args['description'])
        {
            echo '<div class="form-text">';
            echo wp_kses_post($args['description']);
            $this->render_help_icon($args);
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function render_radio($args)
    {
        if (empty($args['dropdown_options']))
        {
            echo ' - ';
            return;
        }

        $class = 'form-check-input';
        if ($args['is_invalid'])
            $class .= ' is-invalid';

        $id = $args['name'] . '_section';

        echo '<div class="mb-3" id="' . esc_attr($id) . '">';
        echo '<label class="form-label">' . esc_html($args['title']) . '</label>';
        foreach ($args['dropdown_options'] as $option_value => $option_name)
        {
            $label_for = $args['label_for'] . '-' . $option_value;

            if ($option_value === $args['value'])
                $checked = ' checked="checked" ';
            else
                $checked = '';
            echo '<div class="form-check">';
            echo '<input class="' . esc_attr($class) . '" type="radio" name="' . esc_attr($args['option_name']) . '['
                . esc_attr($args['name']) . ']" id="'
                . esc_attr($label_for) . '" value="' . esc_attr($option_value) . '"' . $checked . '>';
            echo '<label class="form-check-label" for="'
                . esc_attr($label_for) . '">';
            echo wp_kses_post($option_name);
            echo '</label>';
            echo '</div>';
        }
        echo '</div>';
    }

    public function render_checkbox($args)
    {
        if ((bool) $args['value'])
            $checked = ' checked="checked" ';
        else
            $checked = '';

        $class = 'form-check-input';
        if ($args['is_invalid'])
            $class .= ' is-invalid';

        echo '<div class="form-check mb-3">';
        echo '<input class="' . esc_attr($class) . '" type="checkbox" name="' . esc_attr($args['option_name']) . '['
            . esc_attr($args['name']) . ']" id="'
            . esc_attr($args['label_for']), '"'
            . $checked . ' value="1"';

        if (!empty($args['required']) && $args['required'])
            echo ' required';

        echo '>';

        echo '<label class="form-check-label" for="' . esc_attr($args['label_for']) . '">';
        echo wp_kses_post($args['title']);
        echo '</label>';

        if ($args['description'])
            echo '<div class="form-text">' . wp_kses_post($args['description']) . '</div>';

        echo '</div>';
    }

    public function render_text($args)
    {
        echo wp_kses_post($args['description']);
    }

    public static function doSettingsFields($page, $section)
    {
        global $wp_settings_fields;

        if (!isset($wp_settings_fields[$page][$section]))
            return;

        foreach ((array) $wp_settings_fields[$page][$section] as $field)
        {
            call_user_func($field['callback'], $field['args']);
        }
    }

    public static function settingsErrors($errors_only = false)
    {
        $settings_errors = \get_settings_errors();

        if (empty($settings_errors))
            return;

        $output = '';
        foreach ($settings_errors as $details)
        {
            if (!$details['message'])
                continue;

            if ($details['type'] == 'success' && $errors_only)
                continue;

            if ($details['type'] == 'updated')
                $details['type'] = 'success';

            if ($details['type'] == 'error')
                $details['type'] = 'danger';

            if (in_array($details['type'], array('danger', 'success', 'warning', 'info'), true))
                $class = 'tmn-callout-' . $details['type'];
            else
                $class = '';

            $output .= '<div class="tmn-callout ' . esc_attr($class) . '">';
            $output .= $details['message'];
            $output .= '</div>';
        }

        if ($output)
            $output .= '<div class="mb-3"> </div>';

        echo \wp_kses_post($output);
    }

    public function regexMatch($value, $regex)
    {
        if (preg_match($regex, $value))
            return false;

        return true;
    }

    private function render_help_icon($args)
    {
        if (!empty($args['help_url']))
        {
            echo '<a class="ms-1" href="' . esc_url($args['help_url']) . '" target="_blank">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286m1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94"/></svg>';
            echo '</a>';
        }
    }
}
