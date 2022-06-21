<?php

namespace Yibby;

class FieldTemplates
{

    public function __construct()
    {
        $prefix = "yibby_cmb2_field_display_";
        $this->add_label_wrapped_field_filter($prefix . 'text', 'render_text');
        $this->add_label_wrapped_field_filter($prefix . 'text_medium', 'render_text');
        $this->add_label_wrapped_field_filter($prefix . 'text_small', 'render_text');
        $this->add_label_wrapped_field_filter($prefix . 'text_date', 'render_text');
        $this->add_label_wrapped_field_filter($prefix . 'select', 'render_select');
        $this->add_label_wrapped_field_filter($prefix . 'pw_multiselect', 'render_select');
        $this->add_label_wrapped_field_filter($prefix . 'pw_select', 'render_select');
        $this->add_label_wrapped_field_filter($prefix . 'title', 'render_title');
        $this->add_label_wrapped_field_filter($prefix . 'checkbox', 'render_checkbox');
        add_filter($prefix . "group", [$this, 'render_group'], 10, 2);
        add_filter($prefix . "hidden", [$this, 'render_hidden'], 10, 2);
        add_filter($prefix . "html", [$this, 'render_html'], 10, 2);
    }

    public static function html_attributes($attributes)
    {
        if(!$attributes) return '';

        $compiled = join('="%s" ', array_keys($attributes)).'="%s"';

        return vsprintf($compiled, array_map('htmlspecialchars', array_values($attributes)));
    }

    private function add_label_wrapped_field_filter($filter, $callback) {
        add_filter($filter, [$this, $callback], 10, 2);
        add_filter($filter, [$this, 'render_label_wrap'], 15, 2);
    }

    public function render_label_wrap($content, $field) {
        return "
    <ion-item>
    <ion-label position='floating' for='{$field['id']}'>{$field['name']}</ion-label>
    $content
    </ion-item>";
    }

    private function render_field($field, $props, $tag, $content="") {

        $attributes = [
            'id' => $field['id'],
            'formControlName' => $field['id'],
            'type' => 'text'
        ];

        $requiredMsg = "";
        if (!empty($field['attributes'])) {
            $attributes = array_merge($attributes, $props, $field['attributes']);
            if (!empty($field['attributes']['required']))
                $requiredMsg = "<span slot='error'>{$field['name']} is required</span>";
        }

        $startEndTag = ">";
        $endTag = "</$tag>";
        if (empty($content) && $tag == "input") {
            $endTag = "";
            $startEndTag = " />";
        }

        $attributes_str = self::html_attributes($attributes);
        return "<$tag $attributes_str $startEndTag $content $endTag
    $requiredMsg";
    }

    public function render_text($content, $field) {

        return $this->render_field($field, [], "ion-input");
    }

    public function render_group($content, $field) {
        $display = "";
        foreach($field['fields'] as $subfield) {
            $display .= Yibby::get_field_display($subfield);
        }

        return $display;
    }

    public function render_select($content, $field) {

        $options = "";
        $props = [
            'interface' => 'action-sheet',
            '[value]' => $field['name'],
            'okText' => 'Select',
            'cancelText' => 'Close',
        ];

        if ($field['type'] == 'pw_multiselect') {
            $props['multiple'] = 'true';
            $props['interface'] = 'alert';
        }

        foreach($field['options'] as $key => $value) {
            $options .= "
        <ion-select-option value='$key'>$value</ion-select-option>";
        }

        return $this->render_field($field, $props, 'ion-select', $options);
    }

    public function render_hidden($content, $field) {
        return $this->render_field($field, ['type' => 'hidden'], 'input', '');
    }

    public function render_checkbox($content, $field) {
        return $this->render_field($field, [], 'ion-checkbox', '');
    }

    public function render_html($content, $field) {
        if (is_string($field['content']))
            return $field['content'];
        return " ";
    }

    public function render_title($content, $field) {
        return "";
    }
}