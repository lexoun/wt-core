<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

//$shop['folder'] = 'spahouse';
$shop['folder'] = 'spamall';
$shop['id'] = 3;

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";
//
//
//$categoriesQuery = $mysqli->query("SELECT * FROM shops_categories WHERE shop_id = '".$shop['id']."' AND parent_id = 0");
//
//while($category = mysqli_fetch_array($categoriesQuery)){
//
//    $insertedID = wp_insert_term( $category['name'], 'product_cat', array(
////        'description' => 'Description for category',
//        'parent' => 0,
//        'slug' => $category['name']
//    ) );
//
//    $finalID = $insertedID['term_id'];
//
//    $mysqli->query("UPDATE shops_categories SET id = '".$finalID."' WHERE id = '".$category['id']."' AND shop_id = '".$shop['id']."' ");
//
//
//    $subCategoriesQuery = $mysqli->query("SELECT * FROM shops_categories WHERE shop_id = '".$shop['id']."' AND parent_id = '".$category['id']."'");
//
//    while($subCategory = mysqli_fetch_array($subCategoriesQuery)){
//
//        $subInsertedID = wp_insert_term( $subCategory['name'], 'product_cat', array(
////        'description' => 'Description for category',
//            'parent' => $finalID,
//            'slug' => $subCategory['name']
//        ) );
//
//        $subFinalID = $subInsertedID['term_id'];
//
//
//        $mysqli->query("UPDATE shops_categories SET parent_id = '".$finalID."', id = '".$subFinalID."' WHERE id = '".$subCategory['id']."' AND shop_id = '".$shop['id']."' ");
//
//
//        $subSubCategoriesQuery = $mysqli->query("SELECT * FROM shops_categories WHERE shop_id = '".$shop['id']."' AND parent_id = '".$subCategory['id']."'");
//
//        while($subSubCategory = mysqli_fetch_array($subSubCategoriesQuery)){
//
//            $subSubInsertedID = wp_insert_term( $subSubCategory['name'], 'product_cat', array(
////        'description' => 'Description for category',
//                'parent' => $subFinalID,
//                'slug' => $subSubCategory['name']
//            ) );
//
//            $subSubFinalID = $subSubInsertedID['term_id'];
//
//            $mysqli->query("UPDATE shops_categories SET parent_id = '".$subFinalID."', id = '".$subSubFinalID."' WHERE id = '".$subSubCategory['id']."' AND shop_id = '".$sho
//p['id']."' ");
//
//        }
//    }
//
//}
//
//
//
//exit;

$taxonomy     = 'product_cat';
$orderby      = 'name';
$show_count   = 0;      // 1 for yes, 0 for no
$pad_counts   = 0;      // 1 for yes, 0 for no
$hierarchical = 1;      // 1 for yes, 0 for no
$title        = '';
$empty        = 0;

$args = array(
    'taxonomy'     => $taxonomy,
    'orderby'      => $orderby,
    'show_count'   => $show_count,
    'pad_counts'   => $pad_counts,
    'hierarchical' => $hierarchical,
    'title_li'     => $title,
    'hide_empty'   => $empty
);
$all_categories = get_categories( $args );

//print_r($all_categories);

foreach ($all_categories as $cat) {
    if($cat->category_parent == 0) {
        $category_id = $cat->term_id;
        echo '<br /> '. $cat->term_id .' <a href="'. get_term_link($cat->slug, 'product_cat') .'">'. $cat->name .'</a><br>';

          $mysqli->query("INSERT INTO shops_categories (id, shop_id, name, slug, parent_id) VALUES ('".$cat->term_id."', '".$shop['id']."', '".$cat->name."', '".$cat->slug."', '0')");

        $args2 = array(
            'taxonomy'     => $taxonomy,
            'child_of'     => 0,
            'parent'       => $category_id,
            'orderby'      => $orderby,
            'show_count'   => $show_count,
            'pad_counts'   => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li'     => $title,
            'hide_empty'   => $empty
        );

        $sub_cats = get_categories( $args2 );
        if($sub_cats) {
            foreach($sub_cats as $sub_category) {

                $sub_category_id = $sub_category->term_id;
                echo '---- '. $sub_category->term_id .' <a href="'. get_term_link($sub_category->slug, 'product_cat') .'">'. $sub_category->name .'</a><br>';

                $mysqli->query("INSERT INTO shops_categories (id, shop_id, name, slug, parent_id, main_id) VALUES ('".$sub_category_id."', '".$shop['id']."', '".$sub_category->name."', '".$sub_category->slug."', '".$category_id."', '".$category_id."')");

                $args3 = array(
                    'taxonomy'     => $taxonomy,
                    'child_of'     => 0,
                    'parent'       => $sub_category_id,
                    'orderby'      => $orderby,
                    'show_count'   => $show_count,
                    'pad_counts'   => $pad_counts,
                    'hierarchical' => $hierarchical,
                    'title_li'     => $title,
                    'hide_empty'   => $empty
                );

                $subSub_cats = get_categories( $args3 );
                if($subSub_cats) {
                    foreach ($subSub_cats as $subino_category) {

                        echo '-------- '. $subino_category->term_id .' <a href="' . get_term_link($subino_category->slug, 'product_cat') . '">' . $subino_category->name . '</a><br>';

                       $mysqli->query("INSERT INTO shops_categories (id, shop_id, name, slug, parent_id, main_id) VALUES ('".$subino_category->term_id."', '".$shop['id']."', '".$subino_category->name."', '".$subino_category->slug."', '".$sub_category_id."', '".$category_id."')");

                    }
                }
            }
        }
    }
}


?>