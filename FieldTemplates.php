<?php

namespace Yibby;

class FieldTemplates
{

    public function __construct()
    {
        $prefix = "yibby_cmb2_field_display_";
        $this->add_label_wrapped_field_filter($prefix . 'text', 'render_text');
        add_filter($prefix . "group", [$this, 'render_group'], 10, 2);
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
        return "  <ion-item>
    <ion-label position='floating' for='{$field['id']}'>{$field['name']}</ion-label>$content</ion-item>";
    }

    public function render_text($content, $field) {
        $attributes = [
            'id' => $field['id'],
            'formControlName' => $field['id'],
            'type' => 'text'
        ];
        $requiredMsg = "";
        if (!empty($field['attributes'])) {
            $attributes = array_merge($attributes, $field['attributes']);
            if (!empty($field['attributes']['required']))
                $requiredMsg = "<span slot='error'>{$field['name']} is required</span>";
        }
        $attributes_str = self::html_attributes($attributes);

        return "
    <ion-input $attributes_str></ion-input>
    $requiredMsg";
    }

    public function render_group($content, $field) {
        $display = "";
        foreach($field['fields'] as $subfield) {
            $display .= Yibby::get_field_display($subfield);
        }

        return $display;
    }
}