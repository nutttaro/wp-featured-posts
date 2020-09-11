<?php

/**
 * Class WPFP_WPML
 */
class WPFP_WPML
{

    public static function is_active()
    {
        if ( function_exists('icl_object_id') ) {
            return true;
        }

        return false;
    }

    /**
     * Get posts ids from WPML relation posts
     *
     * @param $post_id
     * @param $post_type
     * @return array
     */
    public static function wpml_get_post_ids($post_id, $post_type = '')
    {
        $ids = [];

        if (empty($post_type)) {
            $post_type = get_post_type($post_id);
        }

        $languages = apply_filters('wpml_active_languages', NULL);
        if ($languages) {
            foreach ($languages as $language) {
                $id = apply_filters('wpml_object_id', $post_id, $post_type, false, $language['code']);
                if ($id) {
                    $ids[$language['code']] = $id;
                }
            }
        }

        return $ids;
    }

}