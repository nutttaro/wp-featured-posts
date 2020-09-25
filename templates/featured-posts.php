<div class="wrap wrap-featured-sorting">
    <h1 class="wp-heading-inline"><?php echo $title; ?></h1>
    <hr class="wp-header-end">

    <?php if ($show_select_post): ?>
    <div class="tablenav top">
        <form action="post" id="form-featured-sorting" class="form-featured-sorting">
            <div class="alignleft actions bulkactions">
                <label for="select-featured-sorting" class="screen-reader-text">Select <?php echo $post_type_title; ?></label>
                <select name="post_id" id="select-featured-sorting">
                    <option>Select <?php echo $post_type_title; ?></option>
                    <?php
                    if ($posts):
                        foreach ($posts as $post):
                            ?>
                            <option value="<?php echo $post->ID; ?>" data-image="<?php echo get_the_post_thumbnail_url($post->ID, 'medium'); ?>"><?php echo $post->post_title; ?></option>
                        <?php
                        endforeach;
                    endif;
                    ?>
                </select>
                <input type="submit" id="add-featured-sorting" class="button action button-primary" value="Add <?php echo $post_type_title; ?>">
            </div>
            <input type="hidden" name="count_featured_sorting" value="<?php echo count($featured_posts); ?>">
            <input type="hidden" name="post_type_title" value="<?php echo $post_type_title; ?>">
            <input type="hidden" name="post_type" value="<?php echo $post_type; ?>">
            <input type="hidden" name="featured_key" value="<?php echo $featured_key; ?>">
            <?php if (defined('ICL_LANGUAGE_CODE')): ?>
            <input type="hidden" name="lang" value="<?php echo ICL_LANGUAGE_CODE; ?>">
            <?php endif; ?>
            <input type="hidden" name="action" value="save_featured_sorting">
            <?php wp_nonce_field('save-featured-sorting', '_wpnonce'); ?>
        </form>
        <br class="clear">
    </div>
    <?php endif; ?>

    <?php
    if ($featured_posts):
        ?>
        <form action="post" id="order-featured-sorting" class="order-featured-sorting">
            <table id="table-featured-sorting" class="wp-list-table widefat fixed striped posts post-type-<?php echo $post_type; ?>">
                <thead>
                <tr>
                    <td id="order" class="manage-column" width="10%">
                        <span>Order</span>
                    </td>
                    <th scope="col" id="title" class="manage-column column-title column-primary" width="70%">
                        <span>Title</span>
                    </th>
                    <?php if ($allow_delete): ?>
                    <th scope="col" id="action" class="manage-column column-action column-primary" width="20%">
                        <span>Action</span>
                    </th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody id="the-list">

                <?php foreach ($featured_posts as $key => $post): ?>

                    <tr id="post-<?php echo $post->ID; ?>" class="">
                        <td class="order column-order has-row-actions column-primary page-order" data-colname="Order" width="10%">
                            <span><?php echo ++$key; ?></span>
                        </td>
                        <td class="title column-title has-row-actions column-primary page-title" data-colname="Title" width="70%">
                            <strong>
                                <?php echo $post->post_title; ?>
                            </strong> <input type="hidden" name="post_id[]" value="<?php echo $post->ID; ?>">
                        </td>
                        <?php if ($allow_delete): ?>
                        <td class="action column-action has-row-actions column-primary page-action" data-colname="Action" width="20%">
                            <a href="#" data-id="<?php echo $post->ID; ?>" data-lang="<?php echo defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : ''; ?>" data-nonce="<?php echo wp_create_nonce('delete-featured-sorting'); ?>">Delete</a>
                        </td>
                        <?php endif; ?>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>
            <input type="hidden" name="post_type_title" value="<?php echo $post_type_title; ?>">
            <input type="hidden" name="post_type" value="<?php echo $post_type; ?>">
            <input type="hidden" name="featured_key" value="<?php echo $featured_key; ?>">
            <input type="hidden" name="action" value="order_featured_sorting">

            <?php if (defined('ICL_LANGUAGE_CODE')): ?>
            <input type="hidden" name="lang" value="<?php echo ICL_LANGUAGE_CODE; ?>">
            <?php endif; ?>

            <?php wp_nonce_field('order-featured-sorting', '_wpnonce'); ?>
        </form>
    <?php endif; ?>

</div>
