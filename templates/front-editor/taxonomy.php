<div class="select-wrap <?= $field['type'] ?>">
    <label for="<?= $field['type'] ?>"><?php echo esc_attr($field['label']); ?></label>
    <select id="<?= $field['type'] ?>" class="taxonomy-select <?= $tax_name ?>" name="<?= $field['type'] ?>" <?php echo isset($field['multiple']) ? 'multiple' : ''; ?> data-placeholder="<?php echo esc_attr($field['label']); ?>" data-add-new="<?= $field['add_new'] ?? 'false' ?>">
        <option data-placeholder="true"></option>
        <?php
        $post_cat_id = 0;
        $has_terms = wp_get_post_terms($post_id, $tax_name);
        $terms = get_terms($tax_name, [
            'hide_empty'   => $field['show_empty'] ? 0 : 1,
        ]);
        foreach ($terms as $term) {
            $term_id = (int) $term->term_id;
            echo sprintf(
                '<option %s value="%s">%s</option>',
                in_array($term_id, $has_terms) ? 'selected' : '',
                $term_id,
                $term->name
            );
        }
        ?>
    </select>
</div>